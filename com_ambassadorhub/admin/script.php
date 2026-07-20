<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer script for com_ambassadorhub.
 *
 * Creates the "Group Manager" and "Coordinator" custom user groups (children of
 * Registered) on install, and grants each group its default component permission
 * so the roles work out of the box. Both steps are idempotent so re-running the
 * install (upgrade) never creates duplicates.
 *
 * This component is installed via Joomla's "Discover" flow against a symlinked dev
 * checkout rather than a zip package, and Discover's install route never copies
 * `<media>`/`<languages>` files (only `<install><sql>` and the script's own hooks run) --
 * so media and language files are copied into Joomla's real global locations by hand here.
 *
 * @since  0.1.0
 */
class Com_AmbassadorhubInstallerScript
{
    /**
     * Runs after install or update.
     *
     * @param   string  $type    install|update|discover_install
     * @param   mixed   $parent  The parent adapter
     *
     * @return  boolean
     */
    public function postflight($type, $parent)
    {
        $this->copyMediaAndLanguageFiles();

        $registeredId = $this->getGroupIdByTitle('Registered');

        if (!$registeredId) {
            return true;
        }

        $groupManagerId = $this->ensureUserGroup('Group Manager', $registeredId);
        $coordinatorId  = $this->ensureUserGroup('Coordinator', $registeredId);

        $this->grantDefaultPermissions($groupManagerId, ['ambassadorhub.manage.own' => true]);
        $this->grantDefaultPermissions($coordinatorId, [
            'ambassadorhub.review' => true,
            'core.manage'          => true,
            'core.delete'          => true,
            'core.edit.state'      => true,
        ]);

        return true;
    }

    /**
     * Copies media assets and language files from this repo's checkout into Joomla's
     * real global /media and /language trees, since Discover-install does not do this
     * for us. Safe to re-run: folders are force-copied and files are overwritten.
     *
     * @return  void
     */
    private function copyMediaAndLanguageFiles(): void
    {
        $siteRoot  = \dirname(__DIR__);
        $adminRoot = __DIR__;

        // Media: repo/media -> JPATH_ROOT/media/com_ambassadorhub
        $mediaSource = $siteRoot . '/media';
        $mediaDest   = JPATH_ROOT . '/media/com_ambassadorhub';

        if (is_dir($mediaSource)) {
            try {
                Folder::copy($mediaSource, $mediaDest, '', true);
            } catch (\Exception $e) {
                Log::add('Ambassador Hub: could not copy media files: ' . $e->getMessage(), Log::WARNING, 'jerror');
            }
        }

        // Language files: repo/language/<tag>/*.ini -> JPATH_SITE/language/<tag>/*.ini
        foreach (['en-GB', 'de-DE'] as $tag) {
            $this->copyLanguageFile($siteRoot . '/language/' . $tag . '/com_ambassadorhub.ini', JPATH_SITE . '/language/' . $tag . '/com_ambassadorhub.ini');

            // Admin language files (including .sys.ini) -> JPATH_ADMINISTRATOR/language/<tag>/*.ini
            $this->copyLanguageFile($adminRoot . '/language/' . $tag . '/com_ambassadorhub.ini', JPATH_ADMINISTRATOR . '/language/' . $tag . '/com_ambassadorhub.ini');
            $this->copyLanguageFile($adminRoot . '/language/' . $tag . '/com_ambassadorhub.sys.ini', JPATH_ADMINISTRATOR . '/language/' . $tag . '/com_ambassadorhub.sys.ini');
        }
    }

    /**
     * Copies a single language file, creating the destination language folder if needed.
     *
     * @param   string  $source  Absolute source file path.
     * @param   string  $dest    Absolute destination file path.
     *
     * @return  void
     */
    private function copyLanguageFile(string $source, string $dest): void
    {
        if (!is_file($source)) {
            return;
        }

        $destFolder = \dirname($dest);

        if (!is_dir($destFolder)) {
            Folder::create($destFolder);
        }

        if (!File::copy($source, $dest)) {
            Log::add('Ambassador Hub: could not copy language file to ' . $dest, Log::WARNING, 'jerror');
        }
    }

    /**
     * Finds a user group id by its exact title.
     *
     * @param   string  $title  The group title.
     *
     * @return  integer|null
     */
    private function getGroupIdByTitle(string $title): ?int
    {
        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . ' = :title')
            ->bind(':title', $title);

        $db->setQuery($query);
        $id = $db->loadResult();

        return $id ? (int) $id : null;
    }

    /**
     * Creates a user group if it does not already exist, and returns its id either way.
     *
     * @param   string   $title     The group title.
     * @param   integer  $parentId  The parent group id.
     *
     * @return  integer
     */
    private function ensureUserGroup(string $title, int $parentId): int
    {
        $existingId = $this->getGroupIdByTitle($title);

        if ($existingId) {
            return $existingId;
        }

        /** @var \Joomla\CMS\Table\Usergroup $table */
        $table = Table::getInstance('Usergroup');
        $table->parent_id = $parentId;
        $table->title     = $title;

        if (!$table->store()) {
            Log::add('Ambassador Hub: could not create user group "' . $title . '": ' . $table->getError(), Log::WARNING, 'jerror');

            return 0;
        }

        return (int) $table->id;
    }

    /**
     * Grants a set of component actions to a user group on the com_ambassadorhub asset,
     * without touching any rules an administrator may have already customised.
     *
     * @param   integer  $groupId  The user group id, or 0 to skip.
     * @param   array    $actions  Map of action name => allowed (bool).
     *
     * @return  void
     */
    private function grantDefaultPermissions(int $groupId, array $actions): void
    {
        if (!$groupId) {
            return;
        }

        /** @var \Joomla\CMS\Table\Asset $asset */
        $asset = Table::getInstance('Asset');

        if (!$asset->loadByName('com_ambassadorhub')) {
            return;
        }

        $rules = json_decode($asset->rules ?: '{}', true) ?: [];

        foreach ($actions as $action => $allowed) {
            if (!isset($rules[$action])) {
                $rules[$action] = [];
            }

            // Don't overwrite an explicit rule an admin may have already set for this group.
            if (!array_key_exists((string) $groupId, $rules[$action])) {
                $rules[$action][(string) $groupId] = $allowed ? 1 : 0;
            }
        }

        $asset->rules = json_encode($rules);
        $asset->store();
    }
}
