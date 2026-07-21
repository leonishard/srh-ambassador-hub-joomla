<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Single Group model: the frontend Group detail page.
 *
 * @since  0.1.0
 */
class GroupModel extends BaseDatabaseModel
{
    /**
     * Fetches a published Group by id, with its manager's display name and member count.
     *
     * @param   integer  $id  The Group id.
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
                    $db->quoteName('a.id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.description'),
                    $db->quoteName('a.category'),
                    $db->quoteName('a.cover_image'),
                    $db->quoteName('a.profile_picture'),
                    $db->quoteName('a.manager_student_id'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.created'),
                ]
            )
            ->select(
                [
                    $db->quoteName('u.name', 'manager_name'),
                ]
            )
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_group_members') . ' gm WHERE gm.group_id = a.id) AS member_count')
            ->from($db->quoteName('#__ambassadorhub_groups', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__ambassadorhub_students', 's'),
                $db->quoteName('s.id') . ' = ' . $db->quoteName('a.manager_student_id')
            )
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('a.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Whether the given Student is a member of the given Group.
     *
     * @param   integer       $groupId    The Group id.
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    public function isMember(int $groupId, ?int $studentId): bool
    {
        if (!$studentId) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__ambassadorhub_group_members'))
            ->where($db->quoteName('group_id') . ' = :groupId')
            ->where($db->quoteName('student_id') . ' = :studentId')
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->bind(':studentId', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    /**
     * Fetches a Group's Events, split into upcoming/past (approved only) and pending
     * (only populated when $includeAll is true, i.e. for that Group's manager or a
     * Coordinator, who need to track their own submissions awaiting a decision).
     *
     * @param   integer  $groupId     The Group id.
     * @param   boolean  $includeAll  Whether to also fetch pending/rejected Events.
     *
     * @return  array{upcoming: object[], past: object[], pending: object[]}
     *
     * @since   0.1.0
     */
    public function getEvents(int $groupId, bool $includeAll = false): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('title'),
                    $db->quoteName('description'),
                    $db->quoteName('event_date'),
                    $db->quoteName('location'),
                    $db->quoteName('category'),
                    $db->quoteName('poster'),
                    $db->quoteName('status'),
                ]
            )
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_event_attendees') . ' ea WHERE ea.event_id = ' . $db->quoteName('a.id') . ') AS attendee_count')
            ->from($db->quoteName('#__ambassadorhub_events', 'a'))
            ->where($db->quoteName('group_id') . ' = :groupId')
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->order($db->quoteName('event_date') . ' ASC');

        $db->setQuery($query);
        $events = $db->loadObjectList() ?: [];

        $now      = Factory::getDate()->toSql();
        $upcoming = [];
        $past     = [];
        $pending  = [];

        foreach ($events as $event) {
            if ($event->status === 'pending') {
                if ($includeAll) {
                    $pending[] = $event;
                }

                continue;
            }

            if ($event->status !== 'approved') {
                continue;
            }

            if ($event->event_date >= $now) {
                $upcoming[] = $event;
            } else {
                $past[] = $event;
            }
        }

        return ['upcoming' => $upcoming, 'past' => $past, 'pending' => $pending];
    }

    /**
     * Fetches a Group's members' display names, for the sidebar member list.
     *
     * @param   integer  $groupId  The Group id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getMembers(int $groupId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_group_members', 'gm'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('gm.student_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('gm.group_id') . ' = :groupId')
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
