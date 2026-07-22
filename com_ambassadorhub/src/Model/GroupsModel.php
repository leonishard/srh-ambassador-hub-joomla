<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Groups directory model: the published Groups list shown on the frontend.
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
            $config['filter_fields'] = ['id', 'a.id', 'name', 'a.name', 'category', 'a.category'];
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
        $category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'string');
        $this->setState('filter.category', $category);

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to build a database query for the list of published Groups.
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
                    $db->quoteName('a.description'),
                    $db->quoteName('a.category'),
                    $db->quoteName('a.cover_image'),
                    $db->quoteName('a.profile_picture'),
                    $db->quoteName('a.manager_student_id'),
                    $db->quoteName('a.created'),
                ]
            )
            ->select('(SELECT COUNT(*) FROM ' . $db->quoteName('#__ambassadorhub_group_members') . ' gm WHERE gm.group_id = a.id) AS member_count')
            ->select($db->quoteName('u.name', 'manager_name'))
            ->from($db->quoteName('#__ambassadorhub_groups', 'a'))
            ->join('LEFT', $db->quoteName('#__ambassadorhub_students', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('a.manager_student_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
            ->where($db->quoteName('a.state') . ' = 1');

        $category = $this->getState('filter.category');

        if (!empty($category)) {
            $query->where($db->quoteName('a.category') . ' = :category')
                ->bind(':category', $category);
        }

        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :search')
                ->bind(':search', $searchLike);
        }

        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->quoteName($orderCol) . ' ' . $orderDirn);

        return $query;
    }

    /**
     * Returns the distinct list of categories currently in use, for filter UI.
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
            ->from($db->quoteName('#__ambassadorhub_groups'))
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('category') . ' IS NOT NULL')
            ->order($db->quoteName('category') . ' ASC');

        $db->setQuery($query);

        return $db->loadColumn() ?: [];
    }

    /**
     * Returns the set of Group ids the given Student currently belongs to.
     *
     * @param   integer|null  $studentId  The Student id.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    public function getJoinedGroupIds(?int $studentId): array
    {
        if (!$studentId) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('group_id'))
            ->from($db->quoteName('#__ambassadorhub_group_members'))
            ->where($db->quoteName('student_id') . ' = :studentId')
            ->bind(':studentId', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);

        return array_map('intval', $db->loadColumn() ?: []);
    }
}
