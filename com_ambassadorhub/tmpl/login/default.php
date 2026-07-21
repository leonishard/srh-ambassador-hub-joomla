<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Login\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// Seed accounts from the sample data set (see the reference app's scripts/seed.js) --
// only shown when server debug mode is on, so this never appears on a real deployment.
$quickLoginGroups = [
	'COM_AMBASSADORHUB_LOGIN_QUICK_COORDINATOR' => [
		['name' => 'Alice Nguyen', 'username' => 'alice.coordinator'],
	],
	'COM_AMBASSADORHUB_LOGIN_QUICK_MANAGERS' => [
		['name' => 'Ben Fischer', 'username' => 'ben.fischer'],
		['name' => 'Carla Weber', 'username' => 'carla.weber'],
		['name' => 'Diego Alvarez', 'username' => 'diego.alvarez'],
	],
	'COM_AMBASSADORHUB_LOGIN_QUICK_STUDENTS' => [
		['name' => 'Elena Rossi', 'username' => 'elena.rossi'],
		['name' => 'Finn Becker', 'username' => 'finn.becker'],
		['name' => 'Grace Adeyemi', 'username' => 'grace.adeyemi'],
		['name' => 'Hassan Ali', 'username' => 'hassan.ali'],
	],
];

echo AmbassadorhubHelper::renderTopbar('login');
?>
<div class="ambassadorhub ambassadorhub-login">
	<div class="ambassadorhub-login__panel">
		<h1 class="ambassadorhub-login__title"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_TITLE'); ?></h1>

		<form class="ambassadorhub-login__form" action="<?php echo Route::_('index.php', true); ?>" method="post">
			<label class="ambassadorhub-caption" for="ah-login-username"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_USERNAME'); ?></label>
			<input class="ambassadorhub-login__input" type="text" id="ah-login-username" name="username" autocomplete="username" required>

			<label class="ambassadorhub-caption" for="ah-login-password"><?php echo Text::_('COM_AMBASSADORHUB_LOGIN_PASSWORD'); ?></label>
			<input class="ambassadorhub-login__input" type="password" id="ah-login-password" name="password" autocomplete="current-password" required>

			<input type="hidden" name="option" value="com_users">
			<input type="hidden" name="task" value="user.login">
			<input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>">
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
						<form action="<?php echo Route::_('index.php', true); ?>" method="post">
							<input type="hidden" name="username" value="<?php echo $this->escape($account['username']); ?>">
							<input type="hidden" name="password" value="Password123!">
							<input type="hidden" name="option" value="com_users">
							<input type="hidden" name="task" value="user.login">
							<input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button type="submit" class="ambassadorhub-login__quick-account"><?php echo $this->escape($account['name']); ?></button>
						</form>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
