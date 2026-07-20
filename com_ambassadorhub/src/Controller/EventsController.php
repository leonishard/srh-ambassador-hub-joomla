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
 * Events controller: handles the frontend RSVP/un-RSVP actions.
 *
 * @since  0.1.0
 */
class EventsController extends BaseController
{
    /**
     * RSVPs the current user (as a Student) to an Event.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function rsvp(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $id   = (int) $app->getInput()->getInt('id');
        $user = $app->getIdentity();

        if (!$user->guest) {
            $studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);

            /** @var DatabaseInterface $db */
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__ambassadorhub_event_attendees'))
                ->where($db->quoteName('event_id') . ' = :eventId')
                ->where($db->quoteName('student_id') . ' = :studentId')
                ->bind(':eventId', $id, ParameterType::INTEGER)
                ->bind(':studentId', $studentId, ParameterType::INTEGER);
            $db->setQuery($query);

            if (!$db->loadResult()) {
                $now   = Factory::getDate()->toSql();
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__ambassadorhub_event_attendees'))
                    ->columns([$db->quoteName('event_id'), $db->quoteName('student_id'), $db->quoteName('date_rsvped')])
                    ->values(':eventId, :studentId, :dateRsvped')
                    ->bind(':eventId', $id, ParameterType::INTEGER)
                    ->bind(':studentId', $studentId, ParameterType::INTEGER)
                    ->bind(':dateRsvped', $now);
                $db->setQuery($query);
                $db->execute();

                $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_EVENT_RSVPD'), 'message');
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=events', false));
    }

    /**
     * Removes the current user's (as a Student) RSVP from an Event.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function unrsvp(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $id   = (int) $app->getInput()->getInt('id');
        $user = $app->getIdentity();

        if (!$user->guest) {
            $studentId = AmbassadorhubHelper::getStudentId((int) $user->id);

            if ($studentId) {
                /** @var DatabaseInterface $db */
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__ambassadorhub_event_attendees'))
                    ->where($db->quoteName('event_id') . ' = :eventId')
                    ->where($db->quoteName('student_id') . ' = :studentId')
                    ->bind(':eventId', $id, ParameterType::INTEGER)
                    ->bind(':studentId', $studentId, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();

                $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_EVENT_UNRSVPD'), 'message');
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=events', false));
    }
}
