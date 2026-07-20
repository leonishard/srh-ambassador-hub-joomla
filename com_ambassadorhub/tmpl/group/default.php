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
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user  = Factory::getApplication()->getIdentity();
$group = $this->item;

echo AmbassadorhubHelper::renderTopbar('groups');
?>
<div class="ambassadorhub ambassadorhub-page">

	<?php if (!empty($group->cover_image)) : ?>
		<div class="ambassadorhub-group-detail__cover">
			<img src="<?php echo $this->escape($group->cover_image); ?>" alt="">
		</div>
	<?php endif; ?>

	<div class="ambassadorhub-group-detail__header">
		<div class="ambassadorhub-group-detail__identity">
			<?php if (!empty($group->profile_picture)) : ?>
				<div class="ambassadorhub-group-detail__avatar">
					<img src="<?php echo $this->escape($group->profile_picture); ?>" alt="">
				</div>
			<?php endif; ?>

			<div>
				<h1><?php echo $this->escape($group->name); ?></h1>
				<p class="ambassadorhub-caption">
					<?php if (!empty($group->category)) : ?>
						<span><?php echo $this->escape($group->category); ?></span> &middot;
					<?php endif; ?>
					<?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?>
					<?php if (!empty($group->manager_name)) : ?>
						&middot; <?php echo Text::sprintf('COM_AMBASSADORHUB_GROUP_MANAGED_BY', $this->escape($group->manager_name)); ?>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="ambassadorhub-group-detail__header-actions">
			<?php if ($user->guest) : ?>
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_GROUP_LOGIN_TO_JOIN'); ?>
				</a>
			<?php elseif ($this->isMember) : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.leave'); ?>" method="post">
					<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
					<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_LEAVE'); ?></button>
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
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&layout=edit&id=' . (int) $group->id); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EDIT'); ?>
				</a>
			<?php endif; ?>

			<?php if ($this->isCoordinator) : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.delete'); ?>" method="post" onsubmit="return confirm('<?php echo $this->escape(Text::_('JGLOBAL_CONFIRM_DELETE')); ?>');">
					<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
					<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--danger"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_DELETE'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<div class="ambassadorhub-group-detail__body">
		<div class="ambassadorhub-group-detail__main">
			<?php if (!empty($group->description)) : ?>
				<p class="ambassadorhub-card__description"><?php echo nl2br($this->escape($group->description)); ?></p>
			<?php endif; ?>

			<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_POSTS_HEADING'); ?></h2>
			<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_STUB_NOTE'); ?></p>
		</div>

		<div class="ambassadorhub-group-detail__sidebar">
			<h3><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_HEADING'); ?></h3>

			<?php if ($this->canManage) : ?>
				<p>
					<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=event&group_id=' . (int) $group->id); ?>">
						<?php echo Text::_('COM_AMBASSADORHUB_EVENT_ADD'); ?>
					</a>
				</p>
			<?php endif; ?>

			<?php if (empty($this->upcomingEvents) && empty($this->pastEvents)) : ?>
				<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_NONE'); ?></p>
			<?php endif; ?>

			<?php if (!empty($this->upcomingEvents)) : ?>
				<h3><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_UPCOMING'); ?></h3>
				<?php foreach ($this->upcomingEvents as $event) : ?>
					<div class="ambassadorhub-group-detail__event-row">
						<span>
							<span class="ambassadorhub-mono ambassadorhub-caption"><?php echo HTMLHelper::_('date', $event->event_date, 'd M'); ?></span>
							<?php echo $this->escape($event->title); ?>
							<?php if ($this->canManage && $event->status !== 'approved') : ?>
								<span class="ambassadorhub-status-tag ambassadorhub-status-tag--<?php echo $event->status; ?>">
									<?php echo Text::_('COM_AMBASSADORHUB_STATUS_' . strtoupper($event->status)); ?>
								</span>
							<?php endif; ?>
						</span>
						<?php if ($this->canManage) : ?>
							<a class="ambassadorhub-caption" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=event&id=' . (int) $event->id); ?>">
								<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENT_EDIT'); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if (!empty($this->pastEvents)) : ?>
				<h3><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_PAST'); ?></h3>
				<?php foreach ($this->pastEvents as $event) : ?>
					<div class="ambassadorhub-group-detail__event-row">
						<span>
							<span class="ambassadorhub-mono ambassadorhub-caption"><?php echo HTMLHelper::_('date', $event->event_date, 'd M'); ?></span>
							<?php echo $this->escape($event->title); ?>
						</span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
