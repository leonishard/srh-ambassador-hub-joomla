<?php

/**
 * @package     SRH.Site
 * @subpackage  templates.cassiopeia
 *
 * Template override for com_users' login view (the "credentials" sub-template).
 * A failed sign-in on the Ambassador Hub login page is hard-redirected by Joomla
 * core to index.php?option=com_users&view=login (see UserController::login()) --
 * there is no "return" hook for that failure path, so this override reskins the
 * real com_users form itself rather than duplicating it, keeping every Joomla
 * behaviour (validation, plugins, remember-me, tokens) intact.
 *
 * @var \Joomla\Component\Users\Site\View\Login\HtmlView $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// This view lives outside com_ambassadorhub, so its language file isn't auto-loaded
// the way it would be when our own component is dispatching -- and since our own
// Dispatcher (which normally applies the visitor's chosen ah_lang cookie, and handles
// an incoming language-toggle click) never runs for this com_users view either, that
// has to be done here too.
AmbassadorhubHelper::applyLanguagePreference();

// A failed login on our own page arrives here with no "return" carried over
// (core's failure redirect doesn't forward one), so fall back to Ambassador Hub
// Home -- as an absolute URL, since Uri::isInternal() rejects the root-relative
// paths Route::_(..., false) produces once SEF is on.
$return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem', '')));

if ($return === '') {
    $return = Route::_('index.php?option=com_ambassadorhub&view=home', false, Route::TLS_IGNORE, true);
}

$quickLoginGroups = [
    'COM_AMBASSADORHUB_LOGIN_QUICK_COORDINATOR' => [
        ['name' => 'Alice Nguyen', 'username' => 'alice.coordinator'],
    ],
    'COM_AMBASSADORHUB_LOGIN_QUICK_MANAGERS' => [
        ['name' => 'Ben Fischer', 'username' => 'ben.fischer'],
        ['name' => 'Carla Weber', 'username' => 'carla.weber'],
        ['name' => 'Diego Alvarez', 'username' => 'diego.alvarez'],
        ['name' => 'Elena Rossi', 'username' => 'elena.rossi'],
        ['name' => 'Finn Becker', 'username' => 'finn.becker'],
        ['name' => 'Grace Adeyemi', 'username' => 'grace.adeyemi'],
        ['name' => 'Hassan Ali', 'username' => 'hassan.ali'],
    ],
    'COM_AMBASSADORHUB_LOGIN_QUICK_STUDENTS' => [
        ['name' => 'Mia Chen', 'username' => 'mia.chen'],
        ['name' => 'Omar Farouk', 'username' => 'omar.farouk'],
        ['name' => 'Sophie Turner', 'username' => 'sophie.turner'],
    ],
];
?>
<link rel="stylesheet" href="<?php echo Uri::root(); ?>media/com_ambassadorhub/css/ambassadorhub.css">
<script src="<?php echo Uri::root(); ?>media/com_ambassadorhub/js/ambassadorhub.js" defer></script>

<?php echo AmbassadorhubHelper::renderTopbar('login'); ?>
<div class="ambassadorhub ambassadorhub-login">
    <div class="ambassadorhub-login__panel">
        <h1 class="ambassadorhub-login__title"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_TITLE'); ?></h1>

        <form class="ambassadorhub-login__form" action="<?php echo Route::_('index.php?task=user.login'); ?>" method="post">
            <label class="ambassadorhub-caption" for="ah-login-username"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_USERNAME'); ?></label>
            <input class="ambassadorhub-login__input" type="text" id="ah-login-username" name="username" autocomplete="username" required autofocus>

            <label class="ambassadorhub-caption" for="ah-login-password"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_PASSWORD'); ?></label>
            <input class="ambassadorhub-login__input" type="password" id="ah-login-password" name="password" autocomplete="current-password" required>

            <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
            <?php echo HTMLHelper::_('form.token'); ?>

            <button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_SUBMIT'); ?></button>
        </form>
    </div>

    <?php if (\defined('JDEBUG') && JDEBUG) : ?>
        <div class="ambassadorhub-login__panel ambassadorhub-login__quick-panel">
            <h2 class="ambassadorhub-login__quick-title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_QUICK_TITLE'); ?></h2>
            <p class="ambassadorhub-caption" style="margin-top: 0.35rem;"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_QUICK_DESC'); ?></p>

            <?php foreach ($quickLoginGroups as $labelKey => $accounts) : ?>
                <div class="ambassadorhub-login__quick-group">
                    <div class="ambassadorhub-caption ambassadorhub-login__quick-group-label"><?php echo Text::_($labelKey); ?></div>
                    <?php foreach ($accounts as $account) : ?>
                        <form action="<?php echo Route::_('index.php?task=user.login'); ?>" method="post">
                            <input type="hidden" name="username" value="<?php echo $this->escape($account['username']); ?>">
                            <input type="hidden" name="password" value="Password123!">
                            <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <button type="submit" class="ambassadorhub-login__quick-account"><?php echo $this->escape($account['name']); ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
