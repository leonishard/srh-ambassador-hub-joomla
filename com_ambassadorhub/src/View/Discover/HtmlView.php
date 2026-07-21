<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Discover;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\DiscoverModel;
use SRH\Component\Ambassadorhub\Site\Model\GroupsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Discover view: Recommended (same category as a Group you've already joined) /
 * Popular (most members) / New (most recently created) Groups, each excluding
 * Groups already joined or already shown in an earlier section, one-click join.
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
     * @var  integer[]
     * @since  0.1.0
     */
    protected $joinedGroupIds = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $recommended = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $popular = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $newest = [];

    /**
     * @var  boolean
     * @since  0.1.0
     */
    protected $hasGroups = false;

    /**
     * @var  boolean  True once every Group is already joined or shown, and nothing
     *                is left to recommend/popular/new -- "you're already part of everything".
     * @since  0.1.0
     */
    protected $nothingToDiscover = false;

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

        /** @var DiscoverModel $model */
        $model  = $this->getModel();
        $groups = $model->getGroups();

        $this->hasGroups = !empty($groups);

        $studentId = $this->isGuest ? null : AmbassadorhubHelper::getStudentId((int) $user->id);

        $groupsModel          = new GroupsModel();
        $this->joinedGroupIds = $studentId ? $groupsModel->getJoinedGroupIds($studentId) : [];

        $shown = array_flip($this->joinedGroupIds);

        $myCategories = [];

        if (!$this->isGuest && $this->joinedGroupIds) {
            foreach ($groups as $group) {
                if (\in_array((int) $group->id, $this->joinedGroupIds, true) && !empty($group->category)) {
                    $myCategories[$group->category] = true;
                }
            }
        }

        if (!$this->isGuest && $myCategories) {
            foreach ($groups as $group) {
                if (!isset($shown[$group->id]) && !empty($group->category) && isset($myCategories[$group->category])) {
                    $this->recommended[] = $group;
                    $shown[$group->id]   = true;
                }
            }
        }

        $remaining = array_values(array_filter($groups, static fn ($g) => !isset($shown[$g->id])));

        usort($remaining, static fn ($a, $b) => (int) $b->member_count <=> (int) $a->member_count);
        $this->popular = \array_slice($remaining, 0, 4);

        foreach ($this->popular as $group) {
            $shown[$group->id] = true;
        }

        $remaining = array_values(array_filter($groups, static fn ($g) => !isset($shown[$g->id])));

        usort($remaining, static fn ($a, $b) => strcmp($b->created, $a->created));
        $this->newest = \array_slice($remaining, 0, 4);

        foreach ($this->newest as $group) {
            $shown[$group->id] = true;
        }

        $this->nothingToDiscover = $this->hasGroups
            && \count($shown) === \count($groups)
            && !$this->recommended
            && !$this->popular
            && !$this->newest;

        $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_DISCOVER'));

        parent::display($tpl);
    }
}
