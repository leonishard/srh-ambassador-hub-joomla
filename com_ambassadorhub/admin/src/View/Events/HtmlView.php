<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\View\Events;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use SRH\Component\Ambassadorhub\Administrator\Model\EventsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Events list view.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  array
     * @since  0.1.0
     */
    public $items = [];

    /**
     * @var  \Joomla\CMS\Pagination\Pagination
     * @since  0.1.0
     */
    public $pagination;

    /**
     * @var  \Joomla\Registry\Registry
     * @since  0.1.0
     */
    public $state;

    /**
     * @var  \Joomla\CMS\Form\Form
     * @since  0.1.0
     */
    public $filterForm;

    /**
     * @var  array
     * @since  0.1.0
     */
    public $activeFilters = [];

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
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Configure the admin page toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $user    = Factory::getApplication()->getIdentity();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_AMBASSADORHUB_MANAGER_EVENTS'), 'calendar');

        if ($user->authorise('core.create', 'com_ambassadorhub')) {
            $toolbar->addNew('event.add');
        }

        if ($user->authorise('core.delete', 'com_ambassadorhub')) {
            $toolbar->delete('events.delete')
                ->text('JTOOLBAR_DELETE')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }
    }
}
