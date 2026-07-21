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
 * Friendship controller: send/accept/remove a connection between two Students.
 * Reachable from both the Friends page and a Student's Profile page, so every
 * action honours an optional "return" to land back wherever it was triggered.
 *
 * @since  0.1.0
 */
class FriendshipController extends BaseController
{
    /**
     * Sends a friend request to another Student.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function send(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $user = $app->getIdentity();

        if ($user->guest) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect($this->returnUrl());

            return;
        }

        $studentId   = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
        $addresseeId = $app->getInput()->getInt('addressee_id', 0);

        if (!$addresseeId || $addresseeId === $studentId) {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_SELF_ERROR'), 'warning');
            $this->setRedirect($this->returnUrl());

            return;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__ambassadorhub_friendships'))
            ->where(
                '(' . $db->quoteName('requester_id') . ' = :r1 AND ' . $db->quoteName('addressee_id') . ' = :a1)'
                . ' OR (' . $db->quoteName('requester_id') . ' = :r2 AND ' . $db->quoteName('addressee_id') . ' = :a2)'
            )
            ->bind(':r1', $studentId, ParameterType::INTEGER)
            ->bind(':a1', $addresseeId, ParameterType::INTEGER)
            ->bind(':r2', $addresseeId, ParameterType::INTEGER)
            ->bind(':a2', $studentId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ($db->loadResult()) {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_DUPLICATE_ERROR'), 'warning');
            $this->setRedirect($this->returnUrl());

            return;
        }

        $now    = Factory::getDate()->toSql();
        $status = 'pending';
        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_friendships'))
            ->columns([$db->quoteName('requester_id'), $db->quoteName('addressee_id'), $db->quoteName('status'), $db->quoteName('created')])
            ->values(':requesterId, :addresseeId, :status, :created')
            ->bind(':requesterId', $studentId, ParameterType::INTEGER)
            ->bind(':addresseeId', $addresseeId, ParameterType::INTEGER)
            ->bind(':status', $status)
            ->bind(':created', $now);
        $db->setQuery($insert);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_REQUEST_SENT'), 'message');
        $this->setRedirect($this->returnUrl());
    }

    /**
     * Accepts a pending friend request -- only the addressee may do this.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function accept(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $user = $app->getIdentity();
        $id   = $app->getInput()->getInt('id', 0);

        if ($user->guest || !$id) {
            $this->setRedirect($this->returnUrl());

            return;
        }

        $studentId = AmbassadorhubHelper::getStudentId((int) $user->id);

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('addressee_id'))
            ->from($db->quoteName('#__ambassadorhub_friendships'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $addresseeId = $db->loadResult();

        if (!$studentId || (int) $addresseeId !== $studentId) {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_NOT_ADDRESSEE_ERROR'), 'warning');
            $this->setRedirect($this->returnUrl());

            return;
        }

        $status = 'accepted';
        $now    = Factory::getDate()->toSql();
        $update = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_friendships'))
            ->set([$db->quoteName('status') . ' = :status', $db->quoteName('modified') . ' = :modified'])
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':status', $status)
            ->bind(':modified', $now)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($update);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_REQUEST_ACCEPTED'), 'message');
        $this->setRedirect($this->returnUrl());
    }

    /**
     * Removes a Friendship row -- used for declining an incoming request, cancelling an
     * outgoing one, or ending an accepted friendship. Either party may do this.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function remove(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $user = $app->getIdentity();
        $id   = $app->getInput()->getInt('id', 0);

        if ($user->guest || !$id) {
            $this->setRedirect($this->returnUrl());

            return;
        }

        $studentId = AmbassadorhubHelper::getStudentId((int) $user->id);

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('requester_id'), $db->quoteName('addressee_id')])
            ->from($db->quoteName('#__ambassadorhub_friendships'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $row = $db->loadObject();

        $isParty = $row && $studentId && ((int) $row->requester_id === $studentId || (int) $row->addressee_id === $studentId);

        if (!$isParty) {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_NOT_PARTY_ERROR'), 'warning');
            $this->setRedirect($this->returnUrl());

            return;
        }

        $delete = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_friendships'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($delete);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_FRIENDS_REMOVED'), 'message');
        $this->setRedirect($this->returnUrl());
    }

    /**
     * Where to send the user back to: whatever "return" was posted (e.g. a Profile
     * page), or the Friends page by default.
     *
     * @return  string
     *
     * @since   0.1.0
     */
    private function returnUrl(): string
    {
        $return = $this->app->getInput()->getString('return', '');

        // Only ever a route we generated ourselves (e.g. back to a Profile page), never
        // an arbitrary external target -- posted form fields aren't trustworthy input.
        if ($return !== '' && str_starts_with($return, 'index.php?option=com_ambassadorhub')) {
            return Route::_($return, false);
        }

        return Route::_('index.php?option=com_ambassadorhub&view=friends', false);
    }
}
