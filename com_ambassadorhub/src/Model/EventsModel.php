<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Events model: the frontend Events listing, filtered to approved Events only.
 *
 * @since  0.1.0
 */
class EventsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   0.1.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'a.id', 'title', 'a.title', 'event_date', 'a.event_date'];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   Field to order by.
     * @param   string  $direction  Direction to order by.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    protected function populateState($ordering = 'a.event_date', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $this->setState('filter.group_id', $app->getInput()->getInt('filter_group_id', 0));
        $this->setState('filter.category', $app->getInput()->getString('filter_category', ''));
        $this->setState('filter.host', $app->getInput()->getInt('filter_host', 0));
        $this->setState('filter.upcoming_only', $app->getInput()->getBool('filter_upcoming_only', true));

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to build a database query for the list of approved Events.
     *
     * @return  QueryInterface
     *
     * @since   0.1.0
     */
    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.description'),
                    $db->quoteName('a.event_date'),
                    $db->quoteName('a.location'),
                    $db->quoteName('a.category'),
                    $db->quoteName('a.poster'),
                    $db->quoteName('a.group_id'),
                    $db->quoteName('a.submitted_by'),
                ]
            )
            ->select($db->quoteName('g.name', 'group_name'))
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_event_attendees') . ' ea WHERE ea.event_id = a.id) AS attendee_count')
            ->from($db->quoteName('#__ambassadorhub_events', 'a'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('a.group_id'))
            ->where($db->quoteName('g.state') . ' = 1');

        // Visibility: everyone sees approved Events; a signed-in Student also sees pending/
        // rejected Events they submitted or that belong to a Group they manage; Coordinators
        // see everything regardless of status.
        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest && $user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            // Coordinator: no status restriction at all.
        } else {
            $conditions = [$db->quoteName('a.status') . ' = ' . $db->quote('approved')];

            if (!$user->guest) {
                $studentId = AmbassadorhubHelper::getStudentId((int) $user->id);

                if ($studentId) {
                    $conditions[] = $db->quoteName('a.submitted_by') . ' = ' . (int) $studentId;
                    $conditions[] = $db->quoteName('g.manager_student_id') . ' = ' . (int) $studentId;
                }
            }

            $query->extendWhere('AND', $conditions, 'OR');
        }

        $groupId = (int) $this->getState('filter.group_id');

        if ($groupId > 0) {
            $query->where($db->quoteName('a.group_id') . ' = :groupId')
                ->bind(':groupId', $groupId, ParameterType::INTEGER);
        }

        $category = $this->getState('filter.category');

        if (!empty($category)) {
            $query->where($db->quoteName('a.category') . ' = :category')
                ->bind(':category', $category);
        }

        $hostId = (int) $this->getState('filter.host');

        if ($hostId > 0) {
            $query->where($db->quoteName('a.submitted_by') . ' = :hostId')
                ->bind(':hostId', $hostId, ParameterType::INTEGER);
        }

        if ($this->getState('filter.upcoming_only')) {
            $now = Factory::getDate()->toSql();
            $query->where($db->quoteName('a.event_date') . ' >= :now')
                ->bind(':now', $now);
        }

        $orderCol  = $this->state->get('list.ordering', 'a.event_date');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->quoteName($orderCol) . ' ' . $orderDirn);

        return $query;
    }

    /**
     * Returns the distinct list of categories currently in use among approved Events.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    public function getCategories(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('category'))
            ->from($db->quoteName('#__ambassadorhub_events'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('approved'))
            ->where($db->quoteName('category') . ' IS NOT NULL')
            ->order($db->quoteName('category') . ' ASC');

        $db->setQuery($query);

        return $db->loadColumn() ?: [];
    }

    /**
     * Returns the set of Event ids the given Student is attending.
     *
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    public function getAttendingEventIds(?int $studentId): array
    {
        if (!$studentId) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('event_id'))
            ->from($db->quoteName('#__ambassadorhub_event_attendees'))
            ->where($db->quoteName('student_id') . ' = :studentId')
            ->bind(':studentId', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return array_map('intval', $db->loadColumn() ?: []);
    }

    /**
     * Returns the display names of Students attending an Event, for the public attendee list.
     *
     * @param   integer  $eventId  The Event id.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    public function getAttendeeNames(int $eventId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('u.name'))
            ->from($db->quoteName('#__ambassadorhub_event_attendees', 'ea'))
            ->join('INNER', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('ea.student_id'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('ea.event_id') . ' = :eventId')
            ->bind(':eventId', $eventId, ParameterType::INTEGER)
            ->order($db->quoteName('u.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadColumn() ?: [];
    }
}
