<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\View\Login;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Login view: a styled front door onto Joomla's own com_users authentication --
 * the form here posts straight to com_users' real login handler (same security,
 * just our own markup) so this page matches the rest of the app instead of the
 * default template's login module.
 *
 * @since  0.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  string
     * @since  0.1.0
     */
    protected $returnUrl;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The template file to include.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        if (!$app->getIdentity()->guest) {
            $app->redirect(Route::_('index.php?option=com_ambassadorhub&view=home', false));

            return;
        }

        // Must be an absolute URL: Joomla's own Uri::isInternal() (checked by the real
        // com_users login handler this form posts to) rejects root-relative paths with
        // a leading slash, which is exactly what Route::_(..., false) returns once SEF
        // is on -- so both the default and any incoming "return" have to be absolute.
        $home   = Route::_('index.php?option=com_ambassadorhub&view=home', false, Route::TLS_IGNORE, true);
        $return = $app->getInput()->getString('return', '');

        $this->returnUrl = ($return !== '' && Uri::isInternal($return)) ? $return : $home;

        $app->getDocument()->setTitle(Text::_('COM_AMBASSADORHUB_LOGIN_TITLE'));

        parent::display($tpl);
    }
}
