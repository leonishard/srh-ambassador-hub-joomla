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
 * Group Proposals model: a Student's own submitted proposals (Groups list page), and
 * the full review queue (Review page, Coordinator-only).
 *
 * @since  0.1.0
 */
class GroupProposalsModel extends BaseDatabaseModel
{
    /**
     * Fetches a Student's own Group Proposals, any status, newest first. A proposal is
     * only ever visible to the student who submitted it and to Coordinators -- never to
     * Public or other Registered students.
     *
     * @param   integer  $studentId  The Student id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getForStudent(int $studentId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id', 'name', 'category', 'status', 'created'])
            ->from($db->quoteName('#__ambassadorhub_group_proposals'))
            ->where($db->quoteName('submitted_by') . ' = :studentId')
            ->bind(':studentId', $studentId, ParameterType::INTEGER)
            ->order($db->quoteName('created') . ' DESC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches all pending Group Proposals, for the Coordinator Review queue.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getPending(): array
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
}
