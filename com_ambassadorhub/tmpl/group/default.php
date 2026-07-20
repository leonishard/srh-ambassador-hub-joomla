<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Group\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user  = Factory::getApplication()->getIdentity();
$group = $this->item;
?>
<div class="ambassadorhub ambassadorhub-group">

	<?php if (!empty($group->cover_image)) : ?>
		<div class="ambassadorhub-group-banner">
			<img src="<?php echo $this->escape($group->cover_image); ?>" alt="">
		</div>
	<?php endif; ?>

	<div class="ambassadorhub-group-header">
		<?php if (!empty($group->profile_picture)) : ?>
			<img class="ambassadorhub-group-icon" src="<?php echo $this->escape($group->profile_picture); ?>" alt="">
		<?php endif; ?>

		<h1><?php echo $this->escape($group->name); ?></h1>

		<?php if (!empty($group->category)) : ?>
			<p class="ambassadorhub-group-category"><?php echo $this->escape($group->category); ?></p>
		<?php endif; ?>

		<p class="ambassadorhub-group-members">
			<?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?>
			<?php if (!empty($group->manager_name)) : ?>
				&middot; <?php echo Text::sprintf('COM_AMBASSADORHUB_GROUP_MANAGED_BY', $this->escape($group->manager_name)); ?>
			<?php endif; ?>
		</p>

		<div class="ambassadorhub-group-actions">
			<?php if ($user->guest) : ?>
				<a href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_LOGIN_TO_JOIN'); ?></a>
			<?php elseif ($this->isMember) : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.leave'); ?>" method="post">
					<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
					<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_LEAVE'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php else : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.join'); ?>" method="post">
					<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
					<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_JOIN'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>

			<?php if ($this->canManage) : ?>
				<a class="ambassadorhub-btn" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&layout=edit&id=' . (int) $group->id); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EDIT'); ?>
				</a>
			<?php endif; ?>

			<?php if ($this->isCoordinator) : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.delete'); ?>" method="post" onsubmit="return confirm('<?php echo $this->escape(Text::_('JGLOBAL_CONFIRM_DELETE')); ?>');">
					<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
					<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_DELETE'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<?php if (!empty($group->description)) : ?>
		<div class="ambassadorhub-group-description">
			<?php echo nl2br($this->escape($group->description)); ?>
		</div>
	<?php endif; ?>

	<div class="ambassadorhub-group-posts">
		<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_POSTS_HEADING'); ?></h2>
		<p><?php echo Text::_('COM_AMBASSADORHUB_STUB_NOTE'); ?></p>
	</div>

	<div class="ambassadorhub-group-events">
		<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_HEADING'); ?></h2>
		<p><?php echo Text::_('COM_AMBASSADORHUB_STUB_NOTE'); ?></p>
	</div>
</div>
