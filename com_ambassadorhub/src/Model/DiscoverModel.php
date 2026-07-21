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
 * Discover model: every published Group, unpaginated, for the Recommended/Popular/
 * New sections -- those are computed in the view, mirroring the reference app.
 *
 * @since  0.1.0
 */
class DiscoverModel extends BaseDatabaseModel
{
    /**
     * Fetches every published Group.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getGroups(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.category'),
                    $db->quoteName('a.description'),
                    $db->quoteName('a.cover_image'),
                    $db->quoteName('a.profile_picture'),
                    $db->quoteName('a.created'),
                ]
            )
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_group_members') . ' gm WHERE gm.group_id = a.id) AS member_count')
            ->from($db->quoteName('#__ambassadorhub_groups', 'a'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->order($db->quoteName('a.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
