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
 * Student Profile model: the Student's own row plus the Groups/Friends/contribution
 * counts shown on their Profile page (subject to that Student's own privacy toggles
 * when viewed by someone else -- enforced by the view, not here).
 *
 * @since  0.1.0
 */
class ProfileModel extends BaseDatabaseModel
{
    /**
     * Fetches a Student's core profile row (joined to their Joomla display name).
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object|null
     *
     * @since   0.1.0
     */
    public function getProfile(int $studentId): ?object
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('s.id'),
                    $db->quoteName('s.user_id'),
                    $db->quoteName('s.bio'),
                    $db->quoteName('s.program'),
                    $db->quoteName('s.profile_picture'),
                    $db->quoteName('s.show_groups_on_profile'),
                    $db->quoteName('s.show_friends_on_profile'),
                    $db->quoteName('s.show_contributions_on_profile'),
                ]
            )
            ->select($db->quoteName('u.name'))
            ->from($db->quoteName('#__ambassadorhub_students', 's'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('s.id') . ' = :id')
            ->bind(':id', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Fetches the Groups a Student has joined and/or manages, merged into one list with
     * an `is_manager` flag (a managed Group they haven't separately joined still appears once).
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getGroups(int $studentId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('g.id'), $db->quoteName('g.name')])
            ->select('0 AS is_manager')
            ->from($db->quoteName('#__ambassadorhub_group_members', 'gm'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('gm.group_id'))
            ->where($db->quoteName('gm.student_id') . ' = :studentId1')
            ->bind(':studentId1', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);
        $joined = $db->loadObjectList() ?: [];

        $query2 = $db->getQuery(true)
            ->select([$db->quoteName('g.id'), $db->quoteName('g.name')])
            ->from($db->quoteName('#__ambassadorhub_groups', 'g'))
            ->where($db->quoteName('g.manager_student_id') . ' = :studentId2')
            ->bind(':studentId2', $studentId, ParameterType::INTEGER);

        $db->setQuery($query2);
        $managed = $db->loadObjectList() ?: [];

        $byId = [];

        foreach ($joined as $group) {
            $byId[(int) $group->id] = $group;
        }

        foreach ($managed as $group) {
            $group->is_manager        = 1;
            $byId[(int) $group->id] = $group;
        }

        return array_values($byId);
    }

    /**
     * Fetches a Student's accepted friends (id + display name).
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getFriends(int $studentId): array
    {
        $friendsModel = new FriendsModel();
        $friendships  = $friendsModel->getFriendshipsForStudent($studentId);

        $friends = [];

        foreach ($friendships as $friendship) {
            if ($friendship->status !== 'accepted') {
                continue;
            }

            $friends[] = (object) [
                'id'   => $friendship->other_student_id,
                'name' => $friendship->other_name,
            ];
        }

        return $friends;
    }

    /**
     * Counts Posts authored, Comments authored, and Events submitted by a Student.
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  array{posts: int, comments: int, eventsHosted: int}
     *
     * @since   0.1.0
     */
    public function getContributions(int $studentId): array
    {
        $db = $this->getDatabase();

        $counts = [];

        foreach (
            [
                'posts'        => '#__ambassadorhub_posts',
                'comments'     => '#__ambassadorhub_comments',
            ] as $key => $table
        ) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName($table))
                ->where($db->quoteName('author_id') . ' = :studentId')
                ->bind(':studentId', $studentId, ParameterType::INTEGER);
            $db->setQuery($query);
            $counts[$key] = (int) $db->loadResult();
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ambassadorhub_events'))
            ->where($db->quoteName('submitted_by') . ' = :studentId')
            ->bind(':studentId', $studentId, ParameterType::INTEGER);
        $db->setQuery($query);
        $counts['eventsHosted'] = (int) $db->loadResult();

        return $counts;
    }

    /**
     * Works out the Friendship state between two Students, from the viewer's perspective.
     *
     * @param   integer  $viewerStudentId  The signed-in viewer's Student id.
     * @param   integer  $targetStudentId  The profile being viewed.
     *
     * @return  array{status: string, friendshipId: ?int}  status is one of
     *          "none", "pending_outgoing", "pending_incoming", "accepted".
     *
     * @since   0.1.0
     */
    public function getFriendshipStatus(int $viewerStudentId, int $targetStudentId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('requester_id'), $db->quoteName('status')])
            ->from($db->quoteName('#__ambassadorhub_friendships'))
            ->where(
                '(' . $db->quoteName('requester_id') . ' = :r1 AND ' . $db->quoteName('addressee_id') . ' = :a1)'
                . ' OR (' . $db->quoteName('requester_id') . ' = :r2 AND ' . $db->quoteName('addressee_id') . ' = :a2)'
            )
            ->bind(':r1', $viewerStudentId, ParameterType::INTEGER)
            ->bind(':a1', $targetStudentId, ParameterType::INTEGER)
            ->bind(':r2', $targetStudentId, ParameterType::INTEGER)
            ->bind(':a2', $viewerStudentId, ParameterType::INTEGER);

        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            return ['status' => 'none', 'friendshipId' => null];
        }

        if ($row->status === 'accepted') {
            return ['status' => 'accepted', 'friendshipId' => (int) $row->id];
        }

        $status = (int) $row->requester_id === $viewerStudentId ? 'pending_outgoing' : 'pending_incoming';

        return ['status' => $status, 'friendshipId' => (int) $row->id];
    }
}
