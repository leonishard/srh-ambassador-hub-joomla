<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Event;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\EventModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event view: the public read-only detail page (default layout) that event cards
 * link to everywhere in the app, plus the create/edit form (layout=edit) used by
 * a Group Manager or Coordinator -- same split as the Group view.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  object
     * @since  0.1.0
     */
    protected $item;

    /**
     * @var  integer
     * @since  0.1.0
     */
    protected $groupId = 0;

    /**
     * @var  string
     * @since  0.1.0
     */
    protected $groupName = '';

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $isAttending = false;

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $attendees = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     *
     * @throws  GenericDataException
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $id  = (int) $app->getInput()->getInt('id');

        /** @var EventModel $model */
        $model = $this->getModel();

        if ($this->getLayout() === 'edit') {
            $this->displayEdit($app, $model, $id);
        } else {
            $this->displayDetail($app, $model, $id);
        }

        parent::display($tpl);
    }

    /**
     * The public read-only detail page.
     *
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app    The application.
     * @param   EventModel                                       $model  The model.
     * @param   integer                                          $id     The Event id.
     *
     * @return  void
     *
     * @throws  GenericDataException
     */
    private function displayDetail($app, EventModel $model, int $id): void
    {
        $item = $model->getItemForDetail($id);

        if (!$item) {
            throw new GenericDataException(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        $user      = $app->getIdentity();
        $studentId = $user->guest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);
        $canManage = AmbassadorhubHelper::canManageGroup((int) $item->group_id);
        $isOwner   = $studentId && (int) $item->submitted_by === $studentId;

        // Same visibility rule as the Events list: approved is public, anything else
        // is only visible to the person who submitted it, that Group's manager, or a Coordinator.
        if ($item->status !== 'approved' && !$canManage && !$isOwner) {
            throw new GenericDataException(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        $this->item        = $item;
        $this->groupId     = (int) $item->group_id;
        $this->groupName   = (string) $item->group_name;
        $this->attendees   = $model->getAttendees($id);
        $this->isAttending = $model->isAttending($id, $studentId);

        $app->getDocument()->setTitle($item->title);
    }

    /**
     * The Group Manager/Coordinator-only create/edit form.
     *
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app    The application.
     * @param   EventModel                                       $model  The model.
     * @param   integer                                          $id     The Event id (0 when creating).
     *
     * @return  void
     *
     * @throws  GenericDataException
     */
    private function displayEdit($app, EventModel $model, int $id): void
    {
        $item = $id > 0 ? $model->getItem($id) : null;

        if ($id > 0 && !$item) {
            throw new GenericDataException(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        $groupId = $item ? (int) $item->group_id : (int) $app->getInput()->getInt('group_id');

        if (!AmbassadorhubHelper::canManageGroup($groupId)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $this->item      = $item ?: (object) [
            'id' => 0, 'title' => '', 'description' => '', 'event_date' => '', 'location' => '',
            'category' => '', 'poster' => '', 'group_id' => $groupId, 'status' => 'pending',
        ];
        $this->groupId   = $groupId;
        $this->groupName = $this->getGroupName($groupId);

        $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_EVENT_FORM'));
    }

    /**
     * Looks up a Group's name for display in the form header.
     *
     * @param   integer  $groupId  The Group id.
     *
     * @return  string
     *
     * @since   0.1.0
     */
    private function getGroupName(int $groupId): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__ambassadorhub_groups'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $groupId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (string) $db->loadResult();
    }
}
