<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\View\Roles;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use SRH\Component\Ambassadorhub\Administrator\Model\RolesModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roles & Rights overview: a read-only, plain-language summary of the three
 * Ambassador Hub roles, what each can do, and who currently holds them.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $coordinators = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $groupManagers = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $plainStudents = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var RolesModel $model */
        $model = $this->getModel();

        $this->coordinators  = $model->getCoordinators();
        $this->groupManagers = $model->getGroupManagers();
        $this->plainStudents = $model->getPlainStudents();

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
        ToolbarHelper::title('COM_AMBASSADORHUB_MANAGER_ROLES', 'users');
    }
}
