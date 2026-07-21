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
 * Profile controller: a Student may edit their own bio, profile picture, and the
 * three privacy toggles that control what non-owners see on their Profile page.
 *
 * @since  0.1.0
 */
class ProfileController extends BaseController
{
    /**
     * Saves the current user's own Student profile.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function save(): void
    {
        $this->checkToken();

        $app  = $this->app;
        $user = $app->getIdentity();

        if ($user->guest) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=home', false));

            return;
        }

        $studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
        $input     = $app->getInput();

        $bio             = $input->get('bio', '', 'RAW');
        $profilePicture  = $input->getString('profile_picture', '');
        $showGroups      = $input->getInt('show_groups_on_profile', 0) ? 1 : 0;
        $showFriends     = $input->getInt('show_friends_on_profile', 0) ? 1 : 0;
        $showContrib     = $input->getInt('show_contributions_on_profile', 0) ? 1 : 0;
        $now             = Factory::getDate()->toSql();

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ambassadorhub_students'))
            ->set(
                [
                    $db->quoteName('bio') . ' = :bio',
                    $db->quoteName('profile_picture') . ' = :profilePicture',
                    $db->quoteName('show_groups_on_profile') . ' = :showGroups',
                    $db->quoteName('show_friends_on_profile') . ' = :showFriends',
                    $db->quoteName('show_contributions_on_profile') . ' = :showContrib',
                    $db->quoteName('modified') . ' = :modified',
                ]
            )
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':bio', $bio)
            ->bind(':profilePicture', $profilePicture)
            ->bind(':showGroups', $showGroups, ParameterType::INTEGER)
            ->bind(':showFriends', $showFriends, ParameterType::INTEGER)
            ->bind(':showContrib', $showContrib, ParameterType::INTEGER)
            ->bind(':modified', $now)
            ->bind(':id', $studentId, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_PROFILE_SAVED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=profile', false));
    }
}
