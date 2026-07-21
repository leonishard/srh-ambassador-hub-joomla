<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Groups;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;
use SRH\Component\Ambassadorhub\Site\Model\GroupProposalsModel;
use SRH\Component\Ambassadorhub\Site\Model\GroupsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Groups directory view: browse all Groups, propose a new one.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  array
     * @since  0.1.0
     */
    protected $items = [];

    /**
     * @var  \Joomla\CMS\Pagination\Pagination
     * @since  0.1.0
     */
    protected $pagination;

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $categories = [];

    /**
     * @var  array
     * @since  0.1.0
     */
    protected $joinedGroupIds = [];

    /**
     * @var  object[]
     * @since  0.1.0
     */
    protected $myProposals = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var GroupsModel $model */
        $model = $this->getModel();

        $this->items      = $model->getItems();
        $this->pagination  = $model->getPagination();
        $this->categories  = $model->getCategories();

        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest) {
            $studentId            = AmbassadorhubHelper::getStudentId((int) $user->id);
            $this->joinedGroupIds = $model->getJoinedGroupIds($studentId);

            if ($studentId) {
                $proposalsModel    = new GroupProposalsModel();
                $this->myProposals = $proposalsModel->getForStudent($studentId);
            }
        }

        Factory::getApplication()->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_PAGE_GROUPS'));

        parent::display($tpl);
    }
}
