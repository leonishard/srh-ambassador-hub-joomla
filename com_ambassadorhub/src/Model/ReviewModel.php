<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Review queue model (Coordinator-only): pending Group Proposals, pending Events, and
 * every Group with its current manager, for reassignment.
 *
 * @since  0.1.0
 */
class ReviewModel extends BaseDatabaseModel
{
    /**
     * Fetches every pending Group Proposal, oldest first.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getPendingProposals(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('gp.id'),
                    $db->quoteName('gp.name'),
                    $db->quoteName('gp.description'),
                    $db->quoteName('gp.category'),
                    $db->quoteName('gp.created'),
                ]
            )
            ->select($db->quoteName('u.name', 'submitted_by_name'))
            ->from($db->quoteName('#__ambassadorhub_group_proposals', 'gp'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('gp.submitted_by'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('gp.status') . ' = ' . $db->quote('pending'))
            ->order($db->quoteName('gp.created') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches every pending Event, oldest first.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getPendingEvents(): array
    {
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
                    $db->quoteName('e.created'),
                ]
            )
            ->select($db->quoteName('g.name', 'group_name'))
            ->select($db->quoteName('u.name', 'submitted_by_name'))
            ->from($db->quoteName('#__ambassadorhub_events', 'e'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('e.group_id'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('e.submitted_by'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('e.status') . ' = ' . $db->quote('pending'))
            ->order($db->quoteName('e.created') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches every Group with its current manager's display name, for reassignment.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getGroups(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('g.id'), $db->quoteName('g.name'), $db->quoteName('g.manager_student_id')])
            ->select($db->quoteName('u.name', 'manager_name'))
            ->from($db->quoteName('#__ambassadorhub_groups', 'g'))
            ->join('LEFT', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('g.manager_student_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->order($db->quoteName('g.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches every Student (id + display name), for the manager-reassignment picker.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getStudents(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'), $db->quoteName('u.name')])
            ->from($db->quoteName('#__ambassadorhub_students', 's'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
