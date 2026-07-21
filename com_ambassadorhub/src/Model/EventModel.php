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

    /**
     * Fetches an Event by id for the public detail page: the raw fields plus the
     * Group's name and the submitter's display name.
     *
     * @param   integer  $id  The Event id.
     *
     * @return  object|null
     *
     * @since   0.1.0
     */
    public function getItemForDetail(int $id)
    {
        if ($id <= 0) {
            return null;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('e.id'),
                    $db->quoteName('e.title'),
                    $db->quoteName('e.description'),
                    $db->quoteName('e.event_date'),
                    $db->quoteName('e.location'),
                    $db->quoteName('e.category'),
                    $db->quoteName('e.poster'),
                    $db->quoteName('e.status'),
                    $db->quoteName('e.group_id'),
                    $db->quoteName('e.submitted_by'),
                ]
            )
            ->select([$db->quoteName('g.name', 'group_name'), $db->quoteName('hu.name', 'host_name')])
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_event_attendees') . ' ea WHERE ea.event_id = e.id) AS attendee_count')
            ->from($db->quoteName('#__ambassadorhub_events', 'e'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('e.group_id'))
            ->join('LEFT', $db->quoteName('#__ambassadorhub_students', 'hs'), $db->quoteName('hs.id') . ' = ' . $db->quoteName('e.submitted_by'))
            ->join('LEFT', $db->quoteName('#__users', 'hu'), $db->quoteName('hu.id') . ' = ' . $db->quoteName('hs.user_id'))
            ->where($db->quoteName('e.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Fetches an Event's attendees (Student id + display name), for the public
     * "Interested" list -- each links through to that Student's Profile.
     *
     * @param   integer  $eventId  The Event id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getAttendees(int $eventId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_event_attendees', 'ea'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('ea.student_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('ea.event_id') . ' = :eventId')
            ->bind(':eventId', $eventId, ParameterType::INTEGER)
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Whether the given Student is RSVP'd to the given Event.
     *
     * @param   integer       $eventId    The Event id.
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    public function isAttending(int $eventId, ?int $studentId): bool
    {
        if (!$studentId) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__ambassadorhub_event_attendees'))
            ->where($db->quoteName('event_id') . ' = :eventId')
            ->where($db->quoteName('student_id') . ' = :studentId')
            ->bind(':eventId', $eventId, ParameterType::INTEGER)
            ->bind(':studentId', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }
}
