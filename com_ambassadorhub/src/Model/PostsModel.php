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
 * Posts + Comments model: backs a Group's post feed on its detail page.
 *
 * @since  0.1.0
 */
class PostsModel extends BaseDatabaseModel
{
    /**
     * Fetches a Group's Posts, newest first, with author display names.
     *
     * @param   integer  $groupId  The Group id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getForGroup(int $groupId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('p.id'),
                    $db->quoteName('p.content'),
                    $db->quoteName('p.media'),
                    $db->quoteName('p.author_id'),
                    $db->quoteName('p.created'),
                ]
            )
            ->select($db->quoteName('u.name', 'author_name'))
            ->from($db->quoteName('#__ambassadorhub_posts', 'p'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('p.author_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('p.group_id') . ' = :groupId')
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->order($db->quoteName('p.created') . ' DESC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Fetches a Post's Comments, oldest first, with author display names.
     *
     * @param   integer  $postId  The Post id.
     *
     * @return  object[]
     *
     * @since   0.1.0
     */
    public function getComments(int $postId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('c.id'),
                    $db->quoteName('c.content'),
                    $db->quoteName('c.author_id'),
                    $db->quoteName('c.created'),
                ]
            )
            ->select($db->quoteName('u.name', 'author_name'))
            ->from($db->quoteName('#__ambassadorhub_comments', 'c'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('c.author_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('c.post_id') . ' = :postId')
            ->bind(':postId', $postId, ParameterType::INTEGER)
            ->order($db->quoteName('c.created') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
