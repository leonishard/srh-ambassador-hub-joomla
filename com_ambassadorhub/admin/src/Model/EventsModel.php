<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Events list model.
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
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'category', 'a.category',
                'status', 'a.status',
                'group_id', 'a.group_id',
                'event_date', 'a.event_date',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to build a database query for the list.
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
                    $db->quoteName('a.category'),
                    $db->quoteName('a.status'),
                    $db->quoteName('a.event_date'),
                    $db->quoteName('a.group_id'),
                    $db->quoteName('a.created'),
                ]
            )
            ->select($db->quoteName('g.name', 'group_name'))
            ->from($db->quoteName('#__ambassadorhub_events', 'a'))
            ->join('LEFT', $db->quoteName('#__ambassadorhub_groups', 'g'), $db->quoteName('g.id') . ' = ' . $db->quoteName('a.group_id'));

        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $searchId = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :searchId')
                    ->bind(':searchId', $searchId, ParameterType::INTEGER);
            } else {
                $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $query->where($db->quoteName('a.title') . ' LIKE :search')
                    ->bind(':search', $searchLike);
            }
        }

        $status = $this->getState('filter.status');

        if (!empty($status)) {
            $query->where($db->quoteName('a.status') . ' = :status')
                ->bind(':status', $status);
        }

        $groupId = (int) $this->getState('filter.group_id');

        if ($groupId > 0) {
            $query->where($db->quoteName('a.group_id') . ' = :groupId')
                ->bind(':groupId', $groupId, ParameterType::INTEGER);
        }

        $orderCol  = $this->state->get('list.ordering', 'a.event_date');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->quoteName($orderCol) . ' ' . $orderDirn);

        return $query;
    }
}
