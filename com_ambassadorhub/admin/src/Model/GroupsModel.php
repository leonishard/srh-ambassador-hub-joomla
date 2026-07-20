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
 * Groups list model.
 *
 * @since  0.1.0
 */
class GroupsModel extends ListModel
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
                'name', 'a.name',
                'category', 'a.category',
                'state', 'a.state',
                'manager_student_id', 'a.manager_student_id',
            ];
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
    protected function populateState($ordering = 'a.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
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
                    $db->quoteName('a.name'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.category'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.manager_student_id'),
                    $db->quoteName('a.created'),
                ]
            )
            ->select(
                [
                    $db->quoteName('u.name', 'manager_name'),
                ]
            )
            ->from($db->quoteName('#__ambassadorhub_groups', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__ambassadorhub_students', 's'),
                $db->quoteName('s.id') . ' = ' . $db->quoteName('a.manager_student_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u'),
                $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id')
            );

        // Filter by search phrase or ID.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $searchId = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :searchId')
                    ->bind(':searchId', $searchId, ParameterType::INTEGER);
            } else {
                $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $query->where($db->quoteName('a.name') . ' LIKE :search')
                    ->bind(':search', $searchLike);
            }
        }

        // Filter by published state.
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        // Filter by category.
        $category = $this->getState('filter.category');

        if (!empty($category)) {
            $query->where($db->quoteName('a.category') . ' = :category')
                ->bind(':category', $category);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->quoteName($orderCol) . ' ' . $orderDirn);

        return $query;
    }
}
