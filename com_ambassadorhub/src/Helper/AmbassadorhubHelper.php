<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Shared helper for bridging Joomla users to Ambassador Hub Students, and for the
 * row-level "own group" ownership checks the ACL layer can't express on its own.
 *
 * @since  0.1.0
 */
class AmbassadorhubHelper
{
    /**
     * Finds the Student row id for a Joomla user, creating one on first use.
     *
     * Every Ambassador Hub action (join a Group, RSVP, comment, ...) is keyed off a
     * Student id rather than the Joomla user id directly, but nothing currently
     * provisions that row when a user registers -- so it is lazily created here the
     * first time a registered user actually does something in the component.
     *
     * @param   integer  $userId  The Joomla user id. 0/guest returns null.
     *
     * @return  integer|null
     *
     * @since   0.1.0
     */
    public static function getOrCreateStudentId(int $userId): ?int
    {
        if ($userId <= 0) {
            return null;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__ambassadorhub_students'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);
        $studentId = $db->loadResult();

        if ($studentId) {
            return (int) $studentId;
        }

        $now = Factory::getDate()->toSql();

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_students'))
            ->columns(
                [
                    $db->quoteName('user_id'),
                    $db->quoteName('show_groups_on_profile'),
                    $db->quoteName('show_friends_on_profile'),
                    $db->quoteName('show_contributions_on_profile'),
                    $db->quoteName('created'),
                ]
            )
            ->values(':userId, 1, 1, 1, :created')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':created', $now);

        $db->setQuery($query);
        $db->execute();

        return (int) $db->insertid();
    }

    /**
     * Finds the Student row id for a Joomla user without creating one.
     *
     * @param   integer  $userId  The Joomla user id.
     *
     * @return  integer|null
     *
     * @since   0.1.0
     */
    public static function getStudentId(int $userId): ?int
    {
        if ($userId <= 0) {
            return null;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__ambassadorhub_students'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);
        $id = $db->loadResult();

        return $id ? (int) $id : null;
    }

    /**
     * Whether the given Student manages the given Group.
     *
     * This is the row-level "own group" check the spec calls for: Joomla's ACL only
     * knows the Student holds `ambassadorhub.manage.own` in general, not which specific
     * Groups they manage, so that relation is checked here against the actual row.
     *
     * @param   integer|null  $groupId    The Group id.
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    public static function isGroupManager(?int $groupId, ?int $studentId): bool
    {
        if (!$groupId || !$studentId) {
            return false;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__ambassadorhub_groups'))
            ->where($db->quoteName('id') . ' = :groupId')
            ->where($db->quoteName('manager_student_id') . ' = :studentId')
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->bind(':studentId', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    /**
     * Whether the current user may manage the given Group: either they are its
     * manager, or they hold the Coordinator-only `ambassadorhub.review` action.
     *
     * @param   integer|null  $groupId  The Group id.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    public static function canManageGroup(?int $groupId): bool
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->guest) {
            return false;
        }

        if ($user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            return true;
        }

        $studentId = self::getStudentId((int) $user->id);

        return self::isGroupManager($groupId, $studentId);
    }
}
