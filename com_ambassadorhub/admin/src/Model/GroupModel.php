<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Group form model.
 *
 * @since  0.1.0
 */
class GroupModel extends AdminModel
{
    /**
     * Prefix used with controller messages.
     *
     * @var  string
     * @since  0.1.0
     */
    protected $text_prefix = 'COM_AMBASSADORHUB_GROUP';

    /**
     * Returns a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name
     * @param   array   $config  Configuration array for the table
     *
     * @return  Table
     *
     * @since   0.1.0
     */
    public function getTable($name = 'Group', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure.
     *
     * @since   0.1.0
     */
    public function getForm($data = [], $loadData = true)
    {
        Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_ambassadorhub/src/Field');

        $form = $this->loadForm('com_ambassadorhub.group', 'group', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     *
     * @since   0.1.0
     */
    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_ambassadorhub.edit.group.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}
