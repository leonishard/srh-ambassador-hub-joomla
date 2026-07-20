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
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event form model.
 *
 * @since  0.1.0
 */
class EventModel extends AdminModel
{
    /**
     * Prefix used with controller messages.
     *
     * @var  string
     * @since  0.1.0
     */
    protected $text_prefix = 'COM_AMBASSADORHUB_EVENT';

    /**
     * Returns a Table object.
     *
     * @param   string  $name     The table type to instantiate
     * @param   string  $prefix   A prefix for the table class name
     * @param   array   $options  Configuration array for the table
     *
     * @return  Table
     *
     * @since   0.1.0
     */
    public function getTable($name = 'Event', $prefix = 'Administrator', $options = []): Table
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

        $form = $this->loadForm('com_ambassadorhub.event', 'event', ['control' => 'jform', 'load_data' => $loadData]);

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
        $data = $app->getUserState('com_ambassadorhub.edit.event.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to save the form data. `submitted_by` is `NOT NULL` in the schema (Events always
     * have a submitter, unlike `approved_by` which is genuinely optional) -- but the admin form
     * lets a Super User leave it blank when creating an Event directly from the backend, so if
     * it's empty here, we fall back to the current admin user's own Student record (creating
     * one for them if they don't have one yet, same as the frontend's lazy-provisioning).
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   0.1.0
     */
    public function save($data)
    {
        if (empty($data['submitted_by'])) {
            $data['submitted_by'] = $this->getOrCreateStudentIdForCurrentUser();
        }

        return parent::save($data);
    }

    /**
     * Finds the Student row id for the current admin user, creating one if it doesn't exist yet.
     *
     * @return  integer
     *
     * @since   0.1.0
     */
    private function getOrCreateStudentIdForCurrentUser(): int
    {
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__ambassadorhub_students'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);
        $db->setQuery($query);
        $studentId = $db->loadResult();

        if ($studentId) {
            return (int) $studentId;
        }

        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_students'))
            ->columns(
                [
                    $db->quoteName('user_id'),
                    $db->quoteName('show_groups_on_profile'),
                    $db->quoteName('show_friends_on_profile'),
                    $db->quoteName('show_contributions_on_profile'),
                    $db->quoteName('created'),
                ]
            )
            ->values(':userId, 1, 1, 1, :created')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':created', $now);
        $db->setQuery($query);
        $db->execute();

        return (int) $db->insertid();
    }
}
