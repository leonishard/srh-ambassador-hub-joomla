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
}
