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
 * Friendships model: every Friendship row involving a Student (either direction),
 * plus the pool of Students still available to send a request to.
 *
 * @since  0.1.0
 */
class FriendsModel extends BaseDatabaseModel
{
    /**
     * Fetches every Friendship row where the given Student is either party, each
     * annotated with the *other* Student's id/name so the view doesn't need to
     * work out "who is the other person" itself.
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getFriendshipsForStudent(int $studentId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('f.id'),
                    $db->quoteName('f.requester_id'),
                    $db->quoteName('f.addressee_id'),
                    $db->quoteName('f.status'),
                    $db->quoteName('f.created'),
                    $db->quoteName('f.modified'),
                ]
            )
            ->from($db->quoteName('#__ambassadorhub_friendships', 'f'))
            ->where('(' . $db->quoteName('f.requester_id') . ' = :sid1 OR ' . $db->quoteName('f.addressee_id') . ' = :sid2)')
            ->bind(':sid1', $studentId, ParameterType::INTEGER)
            ->bind(':sid2', $studentId, ParameterType::INTEGER)
            ->order($db->quoteName('f.created') . ' DESC');

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        if (!$rows) {
            return [];
        }

        $otherIds = [];

        foreach ($rows as $row) {
            $otherIds[] = ((int) $row->requester_id === $studentId) ? (int) $row->addressee_id : (int) $row->requester_id;
        }

        $names = $this->getStudentNames(array_values(array_unique($otherIds)));

        foreach ($rows as $row) {
            $otherId               = ((int) $row->requester_id === $studentId) ? (int) $row->addressee_id : (int) $row->requester_id;
            $row->other_student_id = $otherId;
            $row->other_name       = $names[$otherId] ?? '';
        }

        return $rows;
    }

    /**
     * Fetches every Student not yet connected (in any status, either direction) to the
     * given Student and not the Student themselves -- the pool selectable in "Add a Friend".
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getAddableStudents(int $studentId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_students', 's'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('s.id') . ' != :selfId')
            ->where(
                'NOT EXISTS (SELECT 1 FROM ' . $db->quoteName('#__ambassadorhub_friendships') . ' f2'
                . ' WHERE (f2.requester_id = ' . $db->quoteName('s.id') . ' AND f2.addressee_id = :selfId2)'
                . ' OR (f2.addressee_id = ' . $db->quoteName('s.id') . ' AND f2.requester_id = :selfId3))'
            )
            ->bind(':selfId', $studentId, ParameterType::INTEGER)
            ->bind(':selfId2', $studentId, ParameterType::INTEGER)
            ->bind(':selfId3', $studentId, ParameterType::INTEGER)
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Maps Student ids to their Joomla display name.
     *
     * @param   integer[]  $studentIds  The Student ids.
     *
     * @return  array  Student id => display name.
     *
     * @since   0.1.0
     */
    private function getStudentNames(array $studentIds): array
    {
        if (!$studentIds) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_students', 's'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->whereIn($db->quoteName('s.id'), $studentIds);

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row->id] = $row->name;
        }

        return $map;
    }
}
