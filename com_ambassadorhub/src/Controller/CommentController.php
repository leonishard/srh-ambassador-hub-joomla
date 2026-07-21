<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Comment controller: create/delete a Comment on a Post. Creating requires being a
 * member of the Post's Group (not just any Registered student); deleting is allowed
 * for the Comment's own author, the hosting Group's manager, or a Coordinator.
 *
 * @since  0.1.0
 */
class CommentController extends BaseController
{
    /**
     * Creates a Comment on a Post.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function save(): void
    {
        $this->checkToken();

        $app     = $this->app;
        $input   = $app->getInput();
        $postId  = (int) $input->getInt('post_id');
        $content = trim($input->getString('content', ''));
        $user    = $app->getIdentity();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('group_id'))
            ->from($db->quoteName('#__ambassadorhub_posts'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $postId, ParameterType::INTEGER);
        $db->setQuery($query);
        $groupId = (int) $db->loadResult();

        $studentId = $user->guest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);

        if (!$groupId || $content === '' || !AmbassadorhubHelper::isGroupMember($groupId, $studentId)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));

            return;
        }

        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_comments'))
            ->columns([$db->quoteName('content'), $db->quoteName('post_id'), $db->quoteName('author_id'), $db->quoteName('created')])
            ->values(':content, :postId, :authorId, :created')
            ->bind(':content', $content)
            ->bind(':postId', $postId, ParameterType::INTEGER)
            ->bind(':authorId', $studentId, ParameterType::INTEGER)
            ->bind(':created', $now);
        $db->setQuery($query);
        $db->execute();

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));
    }

    /**
     * Deletes a Comment.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function delete(): void
    {
        $this->checkToken();

        $app = $this->app;
        $id  = (int) $app->getInput()->getInt('id');

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('c.author_id'),
                    $db->quoteName('p.group_id'),
                ]
            )
            ->from($db->quoteName('#__ambassadorhub_comments', 'c'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_posts', 'p'), $db->quoteName('p.id') . ' = ' . $db->quoteName('c.post_id'))
            ->where($db->quoteName('c.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $comment = $db->loadObject();

        if (!$comment) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));

            return;
        }

        $user      = $app->getIdentity();
        $studentId = $user->guest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);
        $isAuthor  = $studentId && (int) $comment->author_id === $studentId;

        if (!$isAuthor && !AmbassadorhubHelper::canManageGroup((int) $comment->group_id)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $comment->group_id, false));

            return;
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_comments'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $comment->group_id, false));
    }
}
