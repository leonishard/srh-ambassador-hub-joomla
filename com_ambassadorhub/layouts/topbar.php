<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * Shared topbar partial, included (via output buffering) from AmbassadorhubHelper::renderTopbar().
 * Expects $activeView (string) in scope.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();

$navItems = [
	'discover' => ['view' => 'discover', 'label' => 'COM_AMBASSADORHUB_PAGE_DISCOVER'],
	'groups'   => ['view' => 'groups', 'label' => 'COM_AMBASSADORHUB_PAGE_GROUPS'],
	'events'   => ['view' => 'events', 'label' => 'COM_AMBASSADORHUB_PAGE_EVENTS'],
	'friends'  => ['view' => 'friends', 'label' => 'COM_AMBASSADORHUB_PAGE_FRIENDS'],
	'mycircle' => ['view' => 'mycircle', 'label' => 'COM_AMBASSADORHUB_PAGE_MYCIRCLE'],
];

if (!$user->guest && $user->authorise('ambassadorhub.review', 'com_ambassadorhub')) {
	$navItems['review'] = ['view' => 'review', 'label' => 'COM_AMBASSADORHUB_PAGE_REVIEW'];
}
?>
<div class="ambassadorhub-topbar">
	<a class="ambassadorhub-topbar__brand" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=home'); ?>">
		SRH Ambassador Hub
	</a>

	<nav class="ambassadorhub-topbar__nav">
		<?php foreach ($navItems as $key => $item) : ?>
			<a
				class="ambassadorhub-topbar__nav-item<?php echo $activeView === $key ? ' ambassadorhub-topbar__nav-item--active' : ''; ?>"
				href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=' . $item['view']); ?>"
			>
				<?php echo Text::_($item['label']); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="ambassadorhub-topbar__footer">
		<?php if ($user->guest) : ?>
			<span class="ambassadorhub-topbar__user-text">
				<span><?php echo Text::_('COM_AMBASSADORHUB_TOPBAR_GUEST'); ?></span>
				<span class="ambassadorhub-topbar__user-role"><?php echo Text::_('COM_AMBASSADORHUB_TOPBAR_BROWSING_PUBLICLY'); ?></span>
			</span>
		<?php else : ?>
			<span class="ambassadorhub-topbar__user-text">
				<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile'); ?>">
					<?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
				</a>
			</span>
		<?php endif; ?>

		<div class="ambassadorhub-topbar__actions">
			<button type="button" class="ambassadorhub-topbar__btn" data-ambassadorhub-theme-toggle>
				<?php echo Text::_('COM_AMBASSADORHUB_TOPBAR_TOGGLE_THEME'); ?>
			</button>

			<?php if ($user->guest) : ?>
				<a class="ambassadorhub-topbar__btn" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=login'); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_TOPBAR_SIGN_IN'); ?>
				</a>
			<?php else : ?>
				<form action="<?php echo Route::_('index.php', true); ?>" method="post" class="ambassadorhub-topbar__logout-form">
					<input type="hidden" name="option" value="com_users">
					<input type="hidden" name="task" value="user.logout">
					<input type="hidden" name="return" value="<?php echo base64_encode(Route::_('index.php?option=com_ambassadorhub&view=home', false, Route::TLS_IGNORE, true)); ?>">
					<?php echo HTMLHelper::_('form.token'); ?>
					<button type="submit" class="ambassadorhub-topbar__btn">
						<?php echo Text::_('COM_AMBASSADORHUB_TOPBAR_LOG_OUT'); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>
