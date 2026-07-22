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
 * Post controller: create/delete a Post on a Group's feed. Only that Group's manager
 * or a Coordinator may post -- membership alone isn't enough (matches the reference app:
 * Post creation requires `ambassadorhub.manage.own` on the Group, same as Events).
 *
 * @since  0.1.0
 */
class PostController extends BaseController
{
    /**
     * Creates a Post in a Group's feed.
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
        $groupId = (int) $input->getInt('group_id');
        $content = trim($input->get('content', '', 'RAW'));

        if (!AmbassadorhubHelper::canManageGroup($groupId) || $content === '') {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));

            return;
        }

        $user      = $app->getIdentity();
        $studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
        $media     = AmbassadorhubHelper::processImageUpload('media', '') ?? '';
        $now       = Factory::getDate()->toSql();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_posts'))
            ->columns(
                [
                    $db->quoteName('content'),
                    $db->quoteName('group_id'),
                    $db->quoteName('author_id'),
                    $db->quoteName('media'),
                    $db->quoteName('created'),
                ]
            )
            ->values(':content, :groupId, :authorId, :media, :created')
            ->bind(':content', $content)
            ->bind(':groupId', $groupId, ParameterType::INTEGER)
            ->bind(':authorId', $studentId, ParameterType::INTEGER)
            ->bind(':media', $media)
            ->bind(':created', $now);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_POST_CREATED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));
    }

    /**
     * Deletes a Post. Allowed for the Post's own author, the hosting Group's manager,
     * or a Coordinator.
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
            ->select([$db->quoteName('group_id'), $db->quoteName('author_id')])
            ->from($db->quoteName('#__ambassadorhub_posts'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $post = $db->loadObject();

        if (!$post) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));

            return;
        }

        $user      = $app->getIdentity();
        $studentId = $user->guest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);
        $isAuthor  = $studentId && (int) $post->author_id === $studentId;

        if (!$isAuthor && !AmbassadorhubHelper::canManageGroup((int) $post->group_id)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $post->group_id, false));

            return;
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_comments'))
            ->where($db->quoteName('post_id') . ' = :postId')
            ->bind(':postId', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_posts'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_POST_DELETED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $post->group_id, false));
    }
}
