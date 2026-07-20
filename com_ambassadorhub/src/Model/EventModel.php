<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Single Event model: backs the frontend create/edit form a Group Manager or
 * Coordinator uses to submit or amend one of a Group's Events.
 *
 * @since  0.1.0
 */
class EventModel extends BaseDatabaseModel
{
    /**
     * Fetches an Event by id.
     *
     * @param   integer  $id  The Event id.
     *
     * @return  object|null
     *
     * @since   0.1.0
     */
    public function getItem(int $id)
    {
        if ($id <= 0) {
            return null;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('title'),
                    $db->quoteName('alias'),
                    $db->quoteName('description'),
                    $db->quoteName('event_date'),
                    $db->quoteName('location'),
                    $db->quoteName('category'),
                    $db->quoteName('poster'),
                    $db->quoteName('status'),
                    $db->quoteName('group_id'),
                    $db->quoteName('submitted_by'),
                ]
            )
            ->from($db->quoteName('#__ambassadorhub_events'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }
}
