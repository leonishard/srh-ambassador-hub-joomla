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
 * Event create/edit form (Group Manager or Coordinator only, for a specific Group).
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
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $id  = (int) $app->getInput()->getInt('id');

        /** @var EventModel $model */
        $model = $this->getModel();
        $item  = $id > 0 ? $model->getItem($id) : null;

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

        parent::display($tpl);
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
