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
 * Review controller (Coordinator-only): approve/reject Group Proposals and Events,
 * reassign or remove a Group's manager. Every task re-checks `ambassadorhub.review`
 * itself -- reaching this controller at all doesn't imply authorisation.
 *
 * @since  0.1.0
 */
class ReviewController extends BaseController
{
    /**
     * Approves a Group Proposal: creates the real Group and promotes the proposer to
     * its manager.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function approveProposal(): void
    {
        $this->checkToken();

        if (!$this->guardCoordinator()) {
            return;
        }

        $app = $this->app;
        $id  = (int) $app->getInput()->getInt('id');

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__ambassadorhub_group_proposals'))
            ->where($db->quoteName('id') . ' = :id')
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $proposal = $db->loadObject();

        if (!$proposal) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));

            return;
        }

        $coordinatorId = AmbassadorhubHelper::getOrCreateStudentId((int) $app->getIdentity()->id);
        $now           = Factory::getDate()->toSql();

        $alias = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $proposal->name), '-'));
        $alias = $alias !== '' ? $alias : 'group-' . time();

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_groups'))
            ->columns(
                [
                    $db->quoteName('name'),
                    $db->quoteName('alias'),
                    $db->quoteName('description'),
                    $db->quoteName('category'),
                    $db->quoteName('cover_image'),
                    $db->quoteName('profile_picture'),
                    $db->quoteName('manager_student_id'),
                    $db->quoteName('state'),
                    $db->quoteName('created'),
                ]
            )
            ->values(':name, :alias, :description, :category, :coverImage, :profilePicture, :managerId, 1, :created')
            ->bind(':name', $proposal->name)
            ->bind(':alias', $alias)
            ->bind(':description', $proposal->description)
            ->bind(':category', $proposal->category)
            ->bind(':coverImage', $proposal->cover_image)
            ->bind(':profilePicture', $proposal->profile_picture)
            ->bind(':managerId', $proposal->submitted_by, ParameterType::INTEGER)
            ->bind(':created', $now);
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_group_proposals'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('approved'))
            ->set($db->quoteName('approved_by') . ' = :approvedBy')
            ->set($db->quoteName('modified') . ' = :modified')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':approvedBy', $coordinatorId, ParameterType::INTEGER)
            ->bind(':modified', $now)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_REVIEW_PROPOSAL_APPROVED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));
    }

    /**
     * Rejects a Group Proposal.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function rejectProposal(): void
    {
        $this->checkToken();

        if (!$this->guardCoordinator()) {
            return;
        }

        $this->setProposalStatus('rejected');

        $this->app->enqueueMessage(Text::_('COM_AMBASSADORHUB_REVIEW_PROPOSAL_REJECTED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));
    }

    /**
     * Approves a pending Event, making it publicly visible.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function approveEvent(): void
    {
        $this->checkToken();

        if (!$this->guardCoordinator()) {
            return;
        }

        $this->setEventStatus('approved');

        $this->app->enqueueMessage(Text::_('COM_AMBASSADORHUB_REVIEW_EVENT_APPROVED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));
    }

    /**
     * Rejects a pending Event, hiding it from Public.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function rejectEvent(): void
    {
        $this->checkToken();

        if (!$this->guardCoordinator()) {
            return;
        }

        $this->setEventStatus('rejected');

        $this->app->enqueueMessage(Text::_('COM_AMBASSADORHUB_REVIEW_EVENT_REJECTED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));
    }

    /**
     * Reassigns (or, with a blank selection, removes) a Group's manager.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function reassignManager(): void
    {
        $this->checkToken();

        if (!$this->guardCoordinator()) {
            return;
        }

        $app       = $this->app;
        $input     = $app->getInput();
        $groupId   = (int) $input->getInt('group_id');
        $managerId = $input->getInt('manager_student_id', 0);

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_groups'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $groupId, ParameterType::INTEGER);

        if ($managerId > 0) {
            $query->set($db->quoteName('manager_student_id') . ' = :managerId')
                ->bind(':managerId', $managerId, ParameterType::INTEGER);
        } else {
            $query->set($db->quoteName('manager_student_id') . ' = NULL');
        }

        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_REVIEW_MANAGER_UPDATED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=review', false));
    }

    /**
     * Confirms the current user holds the Coordinator-only `ambassadorhub.review` action,
     * enqueuing a warning and redirecting away if not.
     *
     * @return  boolean
     *
     * @since   0.1.0
     */
    private function guardCoordinator(): bool
    {
        $user = $this->app->getIdentity();

        if ($user->guest || !$user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=home', false));

            return false;
        }

        return true;
    }

    /**
     * Sets a Group Proposal's status and stamps the reviewing Coordinator.
     *
     * @param   string  $status  'approved' or 'rejected'.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    private function setProposalStatus(string $status): void
    {
        $app           = $this->app;
        $id            = (int) $app->getInput()->getInt('id');
        $coordinatorId = AmbassadorhubHelper::getOrCreateStudentId((int) $app->getIdentity()->id);
        $now           = Factory::getDate()->toSql();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_group_proposals'))
            ->set($db->quoteName('status') . ' = :status')
            ->set($db->quoteName('approved_by') . ' = :approvedBy')
            ->set($db->quoteName('modified') . ' = :modified')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':status', $status)
            ->bind(':approvedBy', $coordinatorId, ParameterType::INTEGER)
            ->bind(':modified', $now)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Sets an Event's status and stamps the reviewing Coordinator.
     *
     * @param   string  $status  'approved' or 'rejected'.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    private function setEventStatus(string $status): void
    {
        $app           = $this->app;
        $id            = (int) $app->getInput()->getInt('id');
        $coordinatorId = AmbassadorhubHelper::getOrCreateStudentId((int) $app->getIdentity()->id);
        $now           = Factory::getDate()->toSql();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_events'))
            ->set($db->quoteName('status') . ' = :status')
            ->set($db->quoteName('approved_by') . ' = :approvedBy')
            ->set($db->quoteName('modified') . ' = :modified')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':status', $status)
            ->bind(':approvedBy', $coordinatorId, ParameterType::INTEGER)
            ->bind(':modified', $now)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }
}
