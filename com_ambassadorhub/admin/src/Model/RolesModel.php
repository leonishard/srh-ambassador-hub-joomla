<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roles & Rights overview model: a read-only summary of who currently holds each
 * Ambassador Hub role, for the admin-only "Roles & Rights" screen.
 *
 * @since  0.1.0
 */
class RolesModel extends BaseDatabaseModel
{
    /**
     * Fetches every user in the "Coordinator" Joomla User Group.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getCoordinators(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('u.id'), $db->quoteName('u.name'), $db->quoteName('u.username')])
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__usergroups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('m.group_id'))
            ->where($db->quoteName('g.title') . ' = ' . $db->quote('Coordinator'))
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches every user in the "Group Manager" Joomla User Group, each annotated with
     * the Group(s) they actually manage (the `manager_student_id` column that drives
     * the real per-Group permission check -- this label is otherwise just cosmetic).
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getGroupManagers(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('u.id'), $db->quoteName('u.name'), $db->quoteName('u.username')])
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__usergroups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('m.group_id'))
            ->where($db->quoteName('g.title') . ' = ' . $db->quote('Group Manager'))
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);
        $managers = $db->loadObjectList() ?: [];

        if (!$managers) {
            return [];
        }

        $groupQuery = $db->getQuery(true)
            ->select([$db->quoteName('g.name'), $db->quoteName('s.user_id')])
            ->from($db->quoteName('#__ambassadorhub_groups', 'g'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('g.manager_student_id'))
            ->order($db->quoteName('g.name') . ' ASC');
        $db->setQuery($groupQuery);
        $groupRows = $db->loadObjectList() ?: [];

        $groupsByUserId = [];

        foreach ($groupRows as $row) {
            $groupsByUserId[(int) $row->user_id][] = $row->name;
        }

        foreach ($managers as $manager) {
            $manager->managed_groups = $groupsByUserId[(int) $manager->id] ?? [];
        }

        return $managers;
    }

    /**
     * Fetches every Registered user who is neither a Coordinator nor a Group Manager --
     * i.e. an ordinary student, with the Groups they've joined as a plain member.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getPlainStudents(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('u.id'), $db->quoteName('u.name'), $db->quoteName('u.username')])
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__usergroups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('m.group_id'))
            ->where($db->quoteName('g.title') . ' = ' . $db->quote('Registered'))
            ->where(
                'NOT EXISTS (SELECT 1 FROM ' . $db->quoteName('#__user_usergroup_map') . ' m2'
                . ' INNER JOIN ' . $db->quoteName('#__usergroups') . ' g2 ON g2.id = m2.group_id'
                . ' WHERE m2.user_id = u.id AND g2.title IN (' . $db->quote('Group Manager') . ', ' . $db->quote('Coordinator') . '))'
            )
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);
        $students = $db->loadObjectList() ?: [];

        if (!$students) {
            return [];
        }

        $memberQuery = $db->getQuery(true)
            ->select([$db->quoteName('g.name'), $db->quoteName('s.user_id')])
            ->from($db->quoteName('#__ambassadorhub_group_members', 'gm'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('gm.group_id'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('gm.student_id'))
            ->order($db->quoteName('g.name') . ' ASC');
        $db->setQuery($memberQuery);
        $memberRows = $db->loadObjectList() ?: [];

        $groupsByUserId = [];

        foreach ($memberRows as $row) {
            $groupsByUserId[(int) $row->user_id][] = $row->name;
        }

        foreach ($students as $student) {
            $student->member_of_groups = $groupsByUserId[(int) $student->id] ?? [];
        }

        return $students;
    }
}
