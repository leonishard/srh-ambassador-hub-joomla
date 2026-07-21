<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Profile;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\ProfileModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Student Profile view: a Student's own profile (editable) or another Student's
 * (read-only, respecting their privacy toggles).
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  ?object
     * @since  0.1.0
     */
    protected $profile;

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $isOwner = false;

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $groups = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $friends = [];

    /**
     * @var  array{posts: int, comments: int, eventsHosted: int}
     * @since  0.1.0
     */
    protected $contributions = ['posts' => 0, 'comments' => 0, 'eventsHosted' => 0];

    /**
     * @var  string  One of "none", "pending_outgoing", "pending_incoming", "accepted", "self".
     * @since  0.1.0
     */
    protected $friendStatus = 'none';

    /**
     * @var  ?integer
     * @since  0.1.0
     */
    protected $friendshipId;

    /**
     * @var  boolean  Whether Groups/Friends/Contributions are visible to the current
     *                viewer -- false when a non-owner has that privacy toggle off, in
     *                which case the section is hidden entirely rather than shown empty.
     * @since  0.1.0
     */
    protected $groupsVisible = true;

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $friendsVisible = true;

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $contributionsVisible = true;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        /** @var ProfileModel $model */
        $model = $this->getModel();

        $requestedId = $app->getInput()->getInt('id', 0);

        if (!$requestedId) {
            if ($user->guest) {
                $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_PROFILE'));
                parent::display($tpl);

                return;
            }

            $targetId      = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);
            $this->isOwner = true;
        } else {
            $targetId        = $requestedId;
            $viewerStudentId = $user->guest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);
            $this->isOwner   = $viewerStudentId !== null && $viewerStudentId === $targetId;
        }

        $this->profile = $model->getProfile($targetId);

        if (!$this->profile) {
            $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_PROFILE'));
            parent::display($tpl);

            return;
        }

        $this->groupsVisible        = $this->isOwner || (int) $this->profile->show_groups_on_profile === 1;
        $this->friendsVisible       = $this->isOwner || (int) $this->profile->show_friends_on_profile === 1;
        $this->contributionsVisible = $this->isOwner || (int) $this->profile->show_contributions_on_profile === 1;

        $this->groups        = $this->groupsVisible ? $model->getGroups($targetId) : [];
        $this->friends        = $this->friendsVisible ? $model->getFriends($targetId) : [];
        $this->contributions = $this->contributionsVisible ? $model->getContributions($targetId) : $this->contributions;

        if ($this->isOwner) {
            $this->friendStatus = 'self';
        } elseif (!$user->guest) {
            $viewerStudentId = AmbassadorhubHelper::getStudentId((int) $user->id);

            if ($viewerStudentId) {
                $status              = $model->getFriendshipStatus($viewerStudentId, $targetId);
                $this->friendStatus  = $status['status'];
                $this->friendshipId  = $status['friendshipId'];
            }
        }

        $app->getDocument()->setTitle($this->profile->name);

        parent::display($tpl);
    }
}
