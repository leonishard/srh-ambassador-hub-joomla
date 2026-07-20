<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\View\Event;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use SRH\Component\Ambassadorhub\Administrator\Model\EventModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event form view.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  \Joomla\CMS\Object\CMSObject
     * @since  0.1.0
     */
    protected $item;

    /**
     * @var  \Joomla\CMS\Form\Form
     * @since  0.1.0
     */
    protected $form;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var EventModel $model */
        $model = $this->getModel();

        $this->item = $model->getItem();
        $this->form = $model->getForm();

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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = empty($this->item->id);
        $user    = Factory::getApplication()->getIdentity();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_($isNew ? 'COM_AMBASSADORHUB_MANAGER_EVENT_NEW' : 'COM_AMBASSADORHUB_MANAGER_EVENT_EDIT'), 'calendar');

        if ($user->authorise('core.edit', 'com_ambassadorhub') || $user->authorise('core.create', 'com_ambassadorhub')) {
            $toolbar->apply('event.apply');

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($isNew) {
                    $childBar->save('event.save');

                    if ($isNew) {
                        $childBar->save2new('event.save2new');
                    }
                }
            );
        }

        $toolbar->cancel('event.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
