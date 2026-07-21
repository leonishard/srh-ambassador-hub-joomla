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
 * Group Proposal controller: any Registered student may submit one (creates a pending
 * row, not a live Group -- a Coordinator turns it into a real Group via the Review queue).
 *
 * @since  0.1.0
 */
class GroupProposalController extends BaseController
{
    /**
     * Submits a new Group Proposal.
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
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));

            return;
        }

        $input = $app->getInput();
        $name  = trim($input->getString('name', ''));

        if ($name === '') {
            $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_PROPOSAL_NAME_REQUIRED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));

            return;
        }

        $studentId   = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
        $now         = Factory::getDate()->toSql();
        $status      = 'pending';
        $description = $input->get('description', '', 'RAW');
        $category    = $input->getString('category', '');
        $coverImage  = $input->getString('cover_image', '');
        $profilePic  = $input->getString('profile_picture', '');

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ambassadorhub_group_proposals'))
            ->columns(
                [
                    $db->quoteName('name'),
                    $db->quoteName('description'),
                    $db->quoteName('category'),
                    $db->quoteName('cover_image'),
                    $db->quoteName('profile_picture'),
                    $db->quoteName('status'),
                    $db->quoteName('submitted_by'),
                    $db->quoteName('created'),
                ]
            )
            ->values(':name, :description, :category, :coverImage, :profilePicture, :status, :submittedBy, :created')
            ->bind(':name', $name)
            ->bind(':description', $description)
            ->bind(':category', $category)
            ->bind(':coverImage', $coverImage)
            ->bind(':profilePicture', $profilePic)
            ->bind(':status', $status)
            ->bind(':submittedBy', $studentId, ParameterType::INTEGER)
            ->bind(':created', $now);

        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage(Text::_('COM_AMBASSADORHUB_GROUP_PROPOSAL_SUBMITTED'), 'message');
        $this->setRedirect(Route::_('index.php?option=com_ambassadorhub&view=groups', false));
    }
}
