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
use SRH\Component\Ambassadorhub\Administrator\Table\GroupTable;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Group controller: handles the frontend Join/Leave actions.
 *
 * @since  0.1.0
 */
class GroupController extends BaseController
{
    /**
     * Joins the current user (as a Student) to a Group.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function join(): void
    {
        $this->checkToken();

        $app = $this->app;
        $id  = (int) $app->getInput()->getInt('id');
        $user = $app->getIdentity();

        if ($user->guest) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));

            return;
        }

        $studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__ambassadorhub_group_members'))
            ->where($db->quoteName('group_id') . ' = :groupId')
            ->where($db->quoteName('student_id') . ' = :studentId')
            ->bind(':groupId', $id, ParameterType::INTEGER)
            ->bind(':studentId', $studentId, ParameterType::INTEGER);
        $db->setQuery($query);

        if (!$db->loadResult()) {
            $now   = Factory::getDate()->toSql();
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__ambassadorhub_group_members'))
                ->columns([$db->quoteName('group_id'), $db->quoteName('student_id'), $db->quoteName('date_joined')])
                ->values(':groupId, :studentId, :dateJoined')
                ->bind(':groupId', $id, ParameterType::INTEGER)
                ->bind(':studentId', $studentId, ParameterType::INTEGER)
                ->bind(':dateJoined', $now);
            $db->setQuery($query);
            $db->execute();

            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_JOINED'), 'message');
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));
    }

    /**
     * Removes the current user (as a Student) from a Group.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function leave(): void
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
                    ->delete($db->quoteName('#__ambassadorhub_group_members'))
                    ->where($db->quoteName('group_id') . ' = :groupId')
                    ->where($db->quoteName('student_id') . ' = :studentId')
                    ->bind(':groupId', $id, ParameterType::INTEGER)
                    ->bind(':studentId', $studentId, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();

                $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_LEFT'), 'message');
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));
    }

    /**
     * Saves edits to a Group made from its frontend edit form. Only the Group's own
     * manager or a Coordinator may do this -- re-checked here server-side, since the
     * form being reachable at all is not the same as being allowed to submit it.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function save(): void
    {
        $this->checkToken();

        $app   = $this->app;
        $input = $app->getInput();
        $id    = (int) $input->getInt('id');

        if (!AmbassadorhubHelper::canManageGroup($id)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));

            return;
        }

        $table = new GroupTable(Factory::getContainer()->get(DatabaseInterface::class));

        if (!$table->load($id)) {
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));

            return;
        }

        $table->name            = $input->getString('name', $table->name);
        $table->category        = $input->getString('category', $table->category);
        $table->description     = $input->get('description', $table->description, 'RAW');
        $table->cover_image     = AmbassadorhubHelper::processImageUpload('cover_image', $table->cover_image);
        $table->profile_picture = AmbassadorhubHelper::processImageUpload('profile_picture', $table->profile_picture);

        if (!$table->check() || !$table->store()) {
            $app->enqueueMessage($table->getError(), 'error');
        } else {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_SAVED'), 'message');
        }

        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));
    }

    /**
     * Deletes a Group outright. Coordinator-only, per the `ambassadorhub.review` action --
     * a Group Manager may edit their own Group but not delete it.
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

        if ($user->guest || !$user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=group&id=' . $id, false));

            return;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_group_members'))
            ->where($db->quoteName('group_id') . ' = :groupId')
            ->bind(':groupId', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ambassadorhub_groups'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_DELETED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));
    }
}
