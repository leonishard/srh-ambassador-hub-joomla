<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\View\Dashboard;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dashboard view for com_ambassadorhub administration.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
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
        ToolbarHelper::title('COM_AMBASSADORHUB_MANAGER_DASHBOARD', 'groups');
    }
}
