<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Event\HtmlView  $this
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
$event = $this->item;
$date  = new DateTime($event->event_date);

$statusClasses = [
	'pending'  => 'ambassadorhub-status-tag--pending',
	'approved' => 'ambassadorhub-status-tag--approved',
	'rejected' => 'ambassadorhub-status-tag--rejected',
];

echo AmbassadorhubHelper::renderTopbar('events');
?>
<div class="ambassadorhub ambassadorhub-page">
	<p class="ambassadorhub-caption">
		<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=events'); ?>">&larr; <?php echo Text::_('COM_AMBASSADORHUB_EVENT_BACK_TO_EVENTS'); ?></a>
	</p>

	<div class="ambassadorhub-group-detail__cover">
		<?php if (!empty($event->poster)) : ?>
			<img src="<?php echo $this->escape($event->poster); ?>" alt="">
		<?php else : ?>
			<div class="ambassadorhub-group-detail__cover-placeholder"></div>
		<?php endif; ?>
	</div>

	<div class="ambassadorhub-group-detail__header">
		<div>
			<div class="ambassadorhub-card__meta ambassadorhub-caption" style="margin-bottom: 0.5rem;">
				<?php if ($event->status !== 'approved') : ?>
					<span class="ambassadorhub-status-tag <?php echo $statusClasses[$event->status] ?? ''; ?>">
						<?php echo Text::_('COM_AMBASSADORHUB_STATUS_' . strtoupper($event->status)); ?>
					</span>
				<?php endif; ?>
				<?php if (!empty($event->category)) : ?>
					<span><?php echo $this->escape($event->category); ?></span>
				<?php endif; ?>
				<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $event->group_id); ?>">
					<?php echo $this->escape($event->group_name); ?>
				</a>
			</div>
			<h1><?php echo $this->escape($event->title); ?></h1>
			<p class="ambassadorhub-caption" style="margin-top: 0.5rem;">
				<?php echo HTMLHelper::_('date', $event->event_date, 'l, j F Y'); ?> &middot; <?php echo $date->format('H:i'); ?>
				<?php echo !empty($event->location) ? ' &middot; ' . $this->escape($event->location) : ''; ?>
			</p>
			<?php if (!empty($event->host_name)) : ?>
				<p class="ambassadorhub-caption" style="margin-top: 0.25rem;">
					<?php echo Text::_('COM_AMBASSADORHUB_EVENT_HOSTED_BY_LABEL'); ?>
					<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $event->submitted_by); ?>">
						<?php echo $this->escape($event->host_name); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>

		<div class="ambassadorhub-group-detail__header-actions">
			<?php if ($user->guest) : ?>
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=login'); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_EVENT_LOGIN_TO_RSVP'); ?>
				</a>
			<?php elseif ($this->isAttending) : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=events.unrsvp'); ?>" method="post">
					<input type="hidden" name="id" value="<?php echo (int) $event->id; ?>">
					<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_EVENT_CANCEL_RSVP'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php else : ?>
				<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=events.rsvp'); ?>" method="post">
					<input type="hidden" name="id" value="<?php echo (int) $event->id; ?>">
					<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_EVENT_RSVP'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<?php if (!empty($event->description)) : ?>
		<p class="ambassadorhub-card__description" style="margin-top: 1.5rem;"><?php echo nl2br($this->escape($event->description)); ?></p>
	<?php endif; ?>

	<section style="margin-top: 3rem;">
		<h2 class="ambassadorhub-mono" style="font-size: 0.8125rem; color: var(--ash-fg); letter-spacing: 0.06em;">
			<?php echo Text::plural('COM_AMBASSADORHUB_EVENT_N_INTERESTED', (int) $event->attendee_count); ?>
		</h2>

		<?php if (empty($this->attendees)) : ?>
			<p class="ambassadorhub-caption" style="margin-top: 0.5rem;"><?php echo Text::_('COM_AMBASSADORHUB_EVENT_NO_RSVPS'); ?></p>
		<?php else : ?>
			<div style="margin-top: 0.5rem;">
				<?php foreach ($this->attendees as $attendee) : ?>
					<a class="ambassadorhub-friends-row" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $attendee->id); ?>">
						<span><?php echo $this->escape($attendee->name); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
</div>
