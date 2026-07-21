<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Friends\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();

echo AmbassadorhubHelper::renderTopbar('friends');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_FRIENDS'); ?></h1>

	<?php if ($user->guest) : ?>
		<p class="ambassadorhub-caption" style="margin-top: 0.5rem;">
			<?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_SIGNIN_PROMPT'); ?>
		</p>
	<?php else : ?>
		<section class="ambassadorhub-friends-section">
			<h2 class="ambassadorhub-friends-section__title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_ADD_HEADER'); ?></h2>
			<form class="ambassadorhub-friends-add" action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.send'); ?>" method="post">
				<select class="ambassadorhub-friends-add__select" name="addressee_id" required>
					<option value=""><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_SELECT_STUDENT'); ?></option>
					<?php foreach ($this->addableStudents as $student) : ?>
						<option value="<?php echo (int) $student->id; ?>"><?php echo $this->escape($student->name); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_SEND_REQUEST'); ?></button>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</section>

		<section class="ambassadorhub-friends-section">
			<h2 class="ambassadorhub-friends-section__title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_YOUR_FRIENDS_HEADER'); ?></h2>
			<?php if (empty($this->accepted)) : ?>
				<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_NO_FRIENDS'); ?></p>
			<?php endif; ?>
			<?php foreach ($this->accepted as $friendship) : ?>
				<div class="ambassadorhub-friends-row">
					<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $friendship->other_student_id); ?>">
						<?php echo $this->escape($friendship->other_name); ?>
					</a>
					<div class="ambassadorhub-friends-row__actions">
						<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.remove'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $friendship->id; ?>">
							<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_REMOVE'); ?></button>
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
					</div>
				</div>
			<?php endforeach; ?>
		</section>

		<?php if (!empty($this->incoming)) : ?>
			<section class="ambassadorhub-friends-section">
				<h2 class="ambassadorhub-friends-section__title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_REQUESTS_HEADER'); ?></h2>
				<?php foreach ($this->incoming as $friendship) : ?>
					<div class="ambassadorhub-friends-row">
						<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $friendship->other_student_id); ?>">
							<?php echo $this->escape($friendship->other_name); ?>
						</a>
						<div class="ambassadorhub-friends-row__actions">
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.accept'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $friendship->id; ?>">
								<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_ACCEPT'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.remove'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $friendship->id; ?>">
								<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_DECLINE'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if (!empty($this->outgoing)) : ?>
			<section class="ambassadorhub-friends-section">
				<h2 class="ambassadorhub-friends-section__title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_SENT_HEADER'); ?></h2>
				<?php foreach ($this->outgoing as $friendship) : ?>
					<div class="ambassadorhub-friends-row">
						<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $friendship->other_student_id); ?>">
							<?php echo $this->escape($friendship->other_name); ?>
						</a>
						<div class="ambassadorhub-friends-row__actions">
							<span class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_PENDING'); ?></span>
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.remove'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $friendship->id; ?>">
								<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_CANCEL'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</div>
