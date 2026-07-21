<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Review;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Model\ReviewModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Review view (Coordinator only): approve/reject Group Proposals and Events,
 * reassign or delete any Group.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $pendingProposals = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $pendingEvents = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $groups = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $students = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('ambassadorhub.review', 'com_ambassadorhub') && !$user->authorise('core.admin', 'com_ambassadorhub')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var ReviewModel $model */
        $model = $this->getModel();

        $this->pendingProposals = $model->getPendingProposals();
        $this->pendingEvents    = $model->getPendingEvents();
        $this->groups           = $model->getGroups();
        $this->students         = $model->getStudents();

        Factory::getApplication()->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_REVIEW'));

        parent::display($tpl);
    }
}
