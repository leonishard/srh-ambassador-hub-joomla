<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Events;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\EventsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Events view: filter by group/host/category, upcoming toggle, RSVP.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  array
     * @since  0.1.0
     */
    protected $items = [];

    /**
     * @var  \Joomla\CMS\Pagination\Pagination
     * @since  0.1.0
     */
    protected $pagination;

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $categories = [];

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $groupOptions = [];

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $hostOptions = [];

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $attendingEventIds = [];

    /**
     * @var  string  "all" or "upcoming".
     * @since  0.1.0
     */
    protected $timeScope = 'all';

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var EventsModel $model */
        $model = $this->getModel();

        $this->items        = $model->getItems();
        $this->pagination   = $model->getPagination();
        $this->categories   = $model->getCategories();
        $this->groupOptions = $model->getGroupOptions();
        $this->hostOptions  = $model->getHostOptions();
        $this->timeScope    = (string) $model->getState('filter.time_scope');

        foreach ($this->items as $item) {
            $item->attendee_names = $model->getAttendeeNames((int) $item->id);
        }

        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest) {
            $studentId               = AmbassadorhubHelper::getStudentId((int) $user->id);
            $this->attendingEventIds = $model->getAttendingEventIds($studentId);
        }

        Factory::getApplication()->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_EVENTS'));

        parent::display($tpl);
    }
}
