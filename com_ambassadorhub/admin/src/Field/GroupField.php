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
 * A list field of all Groups.
 *
 * @since  0.1.0
 */
class GroupField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  0.1.0
     */
    protected $type = 'group';

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
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__ambassadorhub_groups'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        $groups = $db->loadObjectList();

        foreach ($groups as $group) {
            $options[] = HTMLHelper::_('select.option', $group->id, $group->name);
        }

        return $options;
    }
}
