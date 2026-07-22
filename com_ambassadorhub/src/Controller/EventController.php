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
use SRH\Component\Ambassadorhub\Administrator\Table\EventTable;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event controller: handles the frontend create/edit/delete actions for a single Event.
 *
 * @since  0.1.0
 */
class EventController extends BaseController
{
    /**
     * Saves a new or edited Event. Only the hosting Group's manager or a Coordinator
     * may submit for that Group -- re-checked here server-side. New Events are always
     * saved as "pending"; editing an existing Event does not change its status (that's
     * the Coordinator's Review call).
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
        $id      = (int) $input->getInt('id');
        $groupId = (int) $input->getInt('group_id');

        if (!AmbassadorhubHelper::canManageGroup($groupId)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));

            return;
        }

        $table  = new EventTable(Factory::getContainer()->get(DatabaseInterface::class));
        $isNew  = $id <= 0;

        if (!$isNew && !$table->load($id)) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));

            return;
        }

        $table->title       = $input->getString('title', $table->title ?? '');
        $table->group_id    = $groupId;
        $table->description = $input->get('description', $table->description ?? '', 'RAW');
        $table->event_date  = $input->getString('event_date', $table->event_date ?? '');
        $table->location    = $input->getString('location', $table->location ?? '');
        $table->category    = $input->getString('category', $table->category ?? '');
        $table->poster      = AmbassadorhubHelper::processImageUpload('poster', $table->poster ?? '');

        if ($isNew) {
            $user                 = $app->getIdentity();
            $table->submitted_by  = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
            $table->status        = 'pending';
        }

        if (!$table->check() || !$table->store()) {
            $app->enqueueMessage($table->getError(), 'error');
        } else {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_EVENT_SAVED'), 'message');
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));
    }

    /**
     * Deletes an Event outright. Coordinator-only -- a Group Manager may create/edit
     * their Group's Events but not delete them.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function delete(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $id   = (int) $app->getInput()->getInt('id');
        $user = $app->getIdentity();

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('group_id'))
            ->from($db->quoteName('#__ambassadorhub_events'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $groupId = (int) $db->loadResult();

        if ($user->guest || !$user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));

            return;
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_event_attendees'))
            ->where($db->quoteName('event_id') . ' = :eventId')
            ->bind(':eventId', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_events'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_EVENT_DELETED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $groupId, false));
    }
}
