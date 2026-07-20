<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Group;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\GroupModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Group detail view: banner + icon, post feed with comments, upcoming/past events,
 * edit/delete controls for the managing Group Manager or a Coordinator.
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
     * @var  boolean
     * @since  0.1.0
     */
    protected $isMember = false;

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $canManage = false;

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $isCoordinator = false;

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

        /** @var GroupModel $model */
        $model = $this->getModel();
        $item  = $model->getItem($id);

        if (!$item || (int) $item->state !== 1) {
            throw new GenericDataException(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        $this->item      = $item;
        $this->canManage = AmbassadorhubHelper::canManageGroup($id);
        $this->isCoordinator = $app->getIdentity()->authorise('ambassadorhub.review', 'com_ambassadorhub');

        if ($this->getLayout() === 'edit' && !$this->canManage) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $user = $app->getIdentity();

        if (!$user->guest) {
            $studentId      = AmbassadorhubHelper::getStudentId((int) $user->id);
            $this->isMember = $model->isMember($id, $studentId);
        }

        $app->getDocument()->setTitle($item->name);

        parent::display($tpl);
    }
}
