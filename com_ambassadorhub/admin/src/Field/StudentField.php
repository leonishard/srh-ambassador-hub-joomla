<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A list field of all Students, labelled by their Joomla username.
 *
 * @since  0.1.0
 */
class StudentField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  0.1.0
     */
    protected $type = 'student';

    /**
     * Method to get field options.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_students', 's'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);
        $students = $db->loadObjectList();

        foreach ($students as $student) {
            $options[] = HTMLHelper::_('select.option', $student->id, $student->name);
        }

        return $options;
    }
}
