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
 * My Circle model: the signed-in Student's personal dashboard -- quick stats,
 * recent Posts from their Groups, their upcoming/attending Events, and an
 * activity ledger synthesized from Posts/Events/Friendships involving them.
 *
 * @since  0.1.0
 */
class MyCircleModel extends BaseDatabaseModel
{
    /**
     * Fetches the four headline stats shown at the top of the dashboard.
     *
     * @param   integer    $studentId   The Student id.
     * @param   integer[]  $myGroupIds  Ids of Groups the Student has joined.
     *
     * @return  array{groupsFollowed: int, acceptedFriends: int, upcomingEvents: int, eventsAttending: int}
     *
     * @since   0.1.0
     */
    public function getStats(int $studentId, array $myGroupIds): array
    {
        $db  = $this->getDatabase();
        $now = Factory::getDate()->toSql();

        $acceptedFriends = 0;
        $query           = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ambassadorhub_friendships'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('accepted'))
            ->where('(' . $db->quoteName('requester_id') . ' = :sid1 OR ' . $db->quoteName('addressee_id') . ' = :sid2)')
            ->bind(':sid1', $studentId, ParameterType::INTEGER)
            ->bind(':sid2', $studentId, ParameterType::INTEGER);
        $db->setQuery($query);
        $acceptedFriends = (int) $db->loadResult();

        $upcomingEvents = 0;

        if ($myGroupIds) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__ambassadorhub_events'))
                ->where($db->quoteName('status') . ' = ' . $db->quote('approved'))
                ->where($db->quoteName('event_date') . ' > :now1')
                ->whereIn($db->quoteName('group_id'), $myGroupIds)
                ->bind(':now1', $now);
            $db->setQuery($query);
            $upcomingEvents = (int) $db->loadResult();
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ambassadorhub_event_attendees', 'ea'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_events', 'e'), $db->quoteName('e.id') . ' = ' . $db->quoteName('ea.event_id'))
            ->where($db->quoteName('ea.student_id') . ' = :studentId')
            ->where($db->quoteName('e.event_date') . ' > :now2')
            ->bind(':studentId', $studentId, ParameterType::INTEGER)
            ->bind(':now2', $now);
        $db->setQuery($query);
        $eventsAttending = (int) $db->loadResult();

        return [
            'groupsFollowed'  => \count($myGroupIds),
            'acceptedFriends' => $acceptedFriends,
            'upcomingEvents'  => $upcomingEvents,
            'eventsAttending' => $eventsAttending,
        ];
    }

    /**
     * Fetches the most recent Posts from the Student's Groups.
     *
     * @param   integer[]  $myGroupIds  Ids of Groups the Student has joined.
     * @param   integer    $limit       Maximum rows to return.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getRecentPosts(array $myGroupIds, int $limit = 6): array
    {
        if (!$myGroupIds) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('p.id'), $db->quoteName('p.content'), $db->quoteName('p.media'), $db->quoteName('p.created')])
            ->select([$db->quoteName('u.name', 'author_name'), $db->quoteName('g.id', 'group_id'), $db->quoteName('g.name', 'group_name')])
            ->from($db->quoteName('#__ambassadorhub_posts', 'p'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('p.group_id'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('p.author_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->whereIn($db->quoteName('p.group_id'), $myGroupIds)
            ->order($db->quoteName('p.created') . ' DESC')
            ->setLimit($limit);

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches upcoming approved Events from the Student's Groups.
     *
     * @param   integer[]  $myGroupIds  Ids of Groups the Student has joined.
     * @param   integer    $limit       Maximum rows to return.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getUpcomingEvents(array $myGroupIds, int $limit = 10): array
    {
        if (!$myGroupIds) {
            return [];
        }

        $db    = $this->getDatabase();
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('e.id'), $db->quoteName('e.title'), $db->quoteName('e.event_date'), $db->quoteName('e.location')])
            ->select([$db->quoteName('g.id', 'group_id'), $db->quoteName('g.name', 'group_name')])
            ->from($db->quoteName('#__ambassadorhub_events', 'e'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('e.group_id'))
            ->where($db->quoteName('e.status') . ' = ' . $db->quote('approved'))
            ->where($db->quoteName('e.event_date') . ' > :now')
            ->whereIn($db->quoteName('e.group_id'), $myGroupIds)
            ->bind(':now', $now)
            ->order($db->quoteName('e.event_date') . ' ASC')
            ->setLimit($limit);

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches upcoming Events the Student has RSVP'd to (any Group, any status).
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getAttendingEvents(int $studentId): array
    {
        $db    = $this->getDatabase();
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('e.id'), $db->quoteName('e.title'), $db->quoteName('e.event_date'), $db->quoteName('e.location')])
            ->select([$db->quoteName('g.id', 'group_id'), $db->quoteName('g.name', 'group_name')])
            ->from($db->quoteName('#__ambassadorhub_event_attendees', 'ea'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_events', 'e'), $db->quoteName('e.id') . ' = ' . $db->quoteName('ea.event_id'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('e.group_id'))
            ->where($db->quoteName('ea.student_id') . ' = :studentId')
            ->where($db->quoteName('e.event_date') . ' > :now')
            ->bind(':studentId', $studentId, ParameterType::INTEGER)
            ->bind(':now', $now)
            ->order($db->quoteName('e.event_date') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches recent Events submitted for the Student's Groups (any status), for the
     * activity ledger -- approved/rejected decisions and new pending submissions.
     *
     * @param   integer[]  $myGroupIds  Ids of Groups the Student has joined.
     * @param   integer    $limit       Maximum rows to return.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getRecentEventActivity(array $myGroupIds, int $limit = 20): array
    {
        if (!$myGroupIds) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('e.id'), $db->quoteName('e.title'), $db->quoteName('e.status'), $db->quoteName('e.created'), $db->quoteName('e.modified')])
            ->from($db->quoteName('#__ambassadorhub_events', 'e'))
            ->whereIn($db->quoteName('e.group_id'), $myGroupIds)
            ->order($db->quoteName('e.created') . ' DESC')
            ->setLimit($limit);

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
