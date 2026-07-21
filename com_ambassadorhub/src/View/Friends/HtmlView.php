<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Friends;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\FriendsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Friends view: send/accept/decline/remove friend connections.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  ?integer
     * @since  0.1.0
     */
    protected $studentId;

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $accepted = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $incoming = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $outgoing = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $addableStudents = [];

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

        if (!$user->guest) {
            $this->studentId = AmbassadorhubHelper::getOrCreateStudentId((int) $user->id);

            /** @var FriendsModel $model */
            $model        = $this->getModel();
            $friendships  = $model->getFriendshipsForStudent($this->studentId);
            $studentId    = $this->studentId;

            $this->accepted = array_values(array_filter($friendships, static fn ($f) => $f->status === 'accepted'));
            $this->incoming = array_values(array_filter(
                $friendships,
                static fn ($f) => $f->status === 'pending' && (int) $f->addressee_id === $studentId
            ));
            $this->outgoing = array_values(array_filter(
                $friendships,
                static fn ($f) => $f->status === 'pending' && (int) $f->requester_id === $studentId
            ));

            $this->addableStudents = $model->getAddableStudents($studentId);
        }

        $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_FRIENDS'));

        parent::display($tpl);
    }
}
