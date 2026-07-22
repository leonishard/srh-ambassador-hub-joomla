<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
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

    /**
     * The current signed-in user's role, as a language key -- for the topbar's
     * name-and-role display. One of Coordinator / Group Manager / Student, in that
     * priority order (a Coordinator who also manages a Group is shown as Coordinator).
     *
     * @return  string
     *
     * @since   0.1.0
     */
    public static function getCurrentUserRoleLabelKey(): string
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->guest) {
            return 'COM_AMBASSADORHUB_ROLE_STUDENT';
        }

        if ($user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            return 'COM_AMBASSADORHUB_ROLE_COORDINATOR';
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__usergroups', 'g'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.group_id') . ' = ' . $db->quoteName('g.id'))
            ->where($db->quoteName('m.user_id') . ' = :userId')
            ->where($db->quoteName('g.title') . ' = ' . $db->quote('Group Manager'))
            ->bind(':userId', $user->id, ParameterType::INTEGER);
        $db->setQuery($query);

        if ($db->loadResult()) {
            return 'COM_AMBASSADORHUB_ROLE_GROUP_MANAGER';
        }

        return 'COM_AMBASSADORHUB_ROLE_STUDENT';
    }

    /**
     * Whether the given Student belongs to the given Group (used to gate commenting --
     * only members may comment on a Group's Posts, per spec).
     *
     * @param   integer|null  $groupId    The Group id.
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    public static function isGroupMember(?int $groupId, ?int $studentId): bool
    {
        if (!$groupId || !$studentId) {
            return false;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
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
     * Adds a Student's Joomla user to the "Group Manager" user group, if it exists
     * and they aren't already a direct member -- so a newly-promoted manager shows
     * up correctly in Users > Manage, not just in the `manager_student_id` column
     * that actually drives the per-group permission check.
     *
     * @param   integer  $studentId  The Student id being promoted.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public static function promoteToGroupManagerRole(int $studentId): void
    {
        if ($studentId <= 0) {
            return;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__ambassadorhub_students'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $studentId, ParameterType::INTEGER);
        $db->setQuery($query);
        $userId = (int) $db->loadResult();

        if (!$userId) {
            return;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . ' = ' . $db->quote('Group Manager'));
        $db->setQuery($query);
        $groupManagerId = (int) $db->loadResult();

        if (!$groupManagerId) {
            return;
        }

        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        if (!$user || $user->id <= 0 || \in_array($groupManagerId, $user->groups, true)) {
            return;
        }

        $user->groups = array_values(array_unique([...$user->groups, $groupManagerId]));
        $user->save();
    }

    /**
     * Renders the shared topbar (brand, nav, auth state, theme toggle) used across every
     * Ambassador Hub page in place of Joomla's default template chrome, which is hidden
     * for this component via CSS.
     *
     * @param   string  $activeView  The current view name, to highlight in the nav.
     *
     * @return  string
     *
     * @since   0.1.0
     */
    public static function renderTopbar(string $activeView): string
    {
        ob_start();
        require JPATH_SITE . '/components/com_ambassadorhub/layouts/topbar.php';

        return ob_get_clean();
    }

    /**
     * The site language tags Ambassador Hub ships its own UI copy in.
     *
     * @since  0.1.0
     */
    public const SUPPORTED_LANGUAGE_TAGS = ['en-GB', 'de-DE'];

    /**
     * Name of the cookie storing the visitor's chosen Ambassador Hub UI language --
     * independent of the site's own default language, since this is a lightweight
     * per-component switch rather than full Joomla multilingual routing.
     *
     * @since  0.1.0
     */
    public const LANGUAGE_COOKIE = 'ambassadorhub_lang';

    /**
     * The visitor's current Ambassador Hub UI language, from their cookie if set and
     * valid, else the site's default language tag.
     *
     * @return  string
     *
     * @since   0.1.0
     */
    public static function getCurrentLanguageTag(): string
    {
        $cookieValue = Factory::getApplication()->getInput()->cookie->getString(self::LANGUAGE_COOKIE, '');

        if (\in_array($cookieValue, self::SUPPORTED_LANGUAGE_TAGS, true)) {
            return $cookieValue;
        }

        return Factory::getApplication()->getLanguage()->getTag();
    }

    /**
     * Reads an incoming `?ah_lang=` request (updating the stored cookie if present),
     * then force-(re)loads Ambassador Hub's own language strings in whichever tag is
     * now current. Shared by our own Dispatcher and by the com_users login template
     * override, which isn't dispatched through our component at all but still needs
     * the same toggle to work.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public static function applyLanguagePreference(): void
    {
        $app       = Factory::getApplication();
        $requested = $app->getInput()->getCmd('ah_lang', '');

        if (\in_array($requested, self::SUPPORTED_LANGUAGE_TAGS, true)) {
            $app->getInput()->cookie->set(
                self::LANGUAGE_COOKIE,
                $requested,
                ['expires' => time() + 60 * 60 * 24 * 365, 'path' => '/']
            );
        }

        $app->getLanguage()->load('com_ambassadorhub', JPATH_SITE, self::getCurrentLanguageTag(), true);
    }

    /**
     * Fetches a Student's profile picture URL by their Joomla user id, without lazily
     * creating a Student row -- used to render the topbar avatar for any signed-in
     * user, including ones who haven't taken an Ambassador Hub action yet.
     *
     * @param   integer  $userId  The Joomla user id.
     *
     * @return  string|null
     *
     * @since   0.1.0
     */
    public static function getProfilePictureForUser(int $userId): ?string
    {
        if ($userId <= 0) {
            return null;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('profile_picture'))
            ->from($db->quoteName('#__ambassadorhub_students'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);
        $picture = $db->loadResult();

        return $picture ?: null;
    }

    /**
     * Image mime types accepted from an upload field, mapped to the file extension
     * saved to disk.
     *
     * @since  0.1.0
     */
    private const UPLOAD_IMAGE_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * Handles an `<input type="file">` image upload for a Group/Event/Post form,
     * saving it under `/images/ambassadorhub` and returning its public URL.
     *
     * Every one of these forms doubles as an edit form (a Group being edited, an Event
     * being resubmitted, ...), so a blank/invalid file input isn't an error -- it just
     * means "leave the image as it was", hence returning `$existingValue` unchanged
     * whenever there's nothing valid to replace it with.
     *
     * @param   string   $fieldName      The file input's `name` attribute.
     * @param   ?string  $existingValue  The image URL already stored, if any (editing).
     *
     * @return  string|null  The new image's public URL, or `$existingValue` if no
     *                        valid image was uploaded.
     *
     * @since   0.1.0
     */
    public static function processImageUpload(string $fieldName, ?string $existingValue = null): ?string
    {
        $file = Factory::getApplication()->getInput()->files->get($fieldName, [], 'raw');

        if (empty($file['name']) || ($file['error'] ?? \UPLOAD_ERR_NO_FILE) !== \UPLOAD_ERR_OK) {
            return $existingValue;
        }

        $imageInfo = @getimagesize($file['tmp_name']);

        if ($imageInfo === false || !isset(self::UPLOAD_IMAGE_EXTENSIONS[$imageInfo['mime']])) {
            return $existingValue;
        }

        $extension      = self::UPLOAD_IMAGE_EXTENSIONS[$imageInfo['mime']];
        $filename       = 'ah-' . bin2hex(random_bytes(12)) . '.' . $extension;
        $destinationDir = JPATH_ROOT . '/images/ambassadorhub';

        if (!File::upload($file['tmp_name'], $destinationDir . '/' . $filename)) {
            return $existingValue;
        }

        return Uri::root() . 'images/ambassadorhub/' . $filename;
    }
}
