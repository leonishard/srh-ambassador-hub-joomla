<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Mycircle;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\FriendsModel;
use SRH\Component\Ambassadorhub\Site\Model\GroupsModel;
use SRH\Component\Ambassadorhub\Site\Model\MyCircleModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * My Circle view: the logged-in Student's personal dashboard -- quick stats,
 * recent Posts from their Groups, upcoming/attending Events, and an activity
 * ledger built from Posts/Events/Friendships involving them.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $isGuest = true;

    /**
     * @var  array{groupsFollowed: int, acceptedFriends: int, upcomingEvents: int, eventsAttending: int}
     * @since  0.1.0
     */
    protected $stats = ['groupsFollowed' => 0, 'acceptedFriends' => 0, 'upcomingEvents' => 0, 'eventsAttending' => 0];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $recentPosts = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $upcomingEvents = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $attendingEvents = [];

    /**
     * @var  array  Ledger entries: ['timestamp' => string, 'accent' => css-var-name, 'text' => string].
     * @since  0.1.0
     */
    protected $activity = [];

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

        $this->isGuest = $user->guest;

        if (!$this->isGuest) {
            $studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);

            $groupsModel = new GroupsModel();
            $myGroupIds  = $groupsModel->getJoinedGroupIds($studentId);

            /** @var MyCircleModel $model */
            $model = $this->getModel();

            $this->stats           = $model->getStats($studentId, $myGroupIds);
            $this->recentPosts     = $model->getRecentPosts($myGroupIds);
            $this->upcomingEvents  = $model->getUpcomingEvents($myGroupIds);
            $this->attendingEvents = $model->getAttendingEvents($studentId);

            $this->activity = $this->buildActivity(
                $studentId,
                $model->getRecentEventActivity($myGroupIds),
                $this->recentPosts,
                (new FriendsModel())->getFriendshipsForStudent($studentId)
            );
        }

        $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_MYCIRCLE'));

        parent::display($tpl);
    }

    /**
     * Merges Posts/Events/Friendships into one activity ledger, newest first.
     *
     * @param   integer   $studentId    The signed-in Student's id.
     * @param   object[]  $events       Recent Events for the Student's Groups (any status).
     * @param   object[]  $posts        Recent Posts for the Student's Groups.
     * @param   object[]  $friendships  Friendships involving the Student.
     *
     * @return  array
     *
     * @since   0.1.0
     */
    private function buildActivity(int $studentId, array $events, array $posts, array $friendships): array
    {
        $entries = [];
        $esc     = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        foreach ($posts as $post) {
            $entries[] = [
                'timestamp' => $post->created,
                'accent'    => '--signal',
                'text'      => Text::sprintf('COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_POSTED_IN', $esc($post->author_name), $esc($post->group_name)),
            ];
        }

        foreach ($events as $event) {
            if ($event->status === 'approved' || $event->status === 'rejected') {
                $entries[] = [
                    'timestamp' => $event->modified ?: $event->created,
                    'accent'    => $event->status === 'approved' ? '--status-approved' : '--status-rejected',
                    'text'      => Text::sprintf(
                        $event->status === 'approved'
                            ? 'COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_EVENT_APPROVED'
                            : 'COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_EVENT_REJECTED',
                        $esc($event->title)
                    ),
                ];
            } elseif ($event->status === 'pending') {
                $entries[] = [
                    'timestamp' => $event->created,
                    'accent'    => '--status-pending',
                    'text'      => Text::sprintf('COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_EVENT_SUBMITTED', $esc($event->title)),
                ];
            }
        }

        foreach ($friendships as $friendship) {
            $isRequester = (int) $friendship->requester_id === $studentId;

            if ($friendship->status === 'accepted') {
                $entries[] = [
                    'timestamp' => $friendship->modified ?: $friendship->created,
                    'accent'    => '--signal',
                    'text'      => Text::sprintf('COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_NOW_FRIENDS', $esc($friendship->other_name)),
                ];
            } elseif ($friendship->status === 'pending') {
                $entries[] = [
                    'timestamp' => $friendship->created,
                    'accent'    => '--ash',
                    'text'      => Text::sprintf(
                        $isRequester
                            ? 'COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_SENT_REQUEST'
                            : 'COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_RECEIVED_REQUEST',
                        $esc($friendship->other_name)
                    ),
                ];
            }
        }

        usort($entries, static fn ($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

        return \array_slice($entries, 0, 20);
    }
}
