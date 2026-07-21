<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Events\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user      = Factory::getApplication()->getIdentity();
$input     = Factory::getApplication()->getInput();
$category  = $input->getString('filter_category', '');
$groupId   = $input->getInt('filter_group_id', 0);
$hostId    = $input->getInt('filter_host', 0);
$orderDirn = $input->getCmd('filter_order_Dir', 'ASC');

$hasActiveFilters = $groupId > 0 || $hostId > 0 || $category !== '' || $this->timeScope === 'upcoming' || $orderDirn !== 'ASC';

$statusClasses = [
	'pending'  => 'ambassadorhub-status-tag--pending',
	'approved' => 'ambassadorhub-status-tag--approved',
	'rejected' => 'ambassadorhub-status-tag--rejected',
];

echo AmbassadorhubHelper::renderTopbar('events');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_EVENTS'); ?></h1>
	<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_SUBTITLE'); ?></p>

	<?php if (!empty($this->items) || $hasActiveFilters) : ?>
		<form method="get" class="ambassadorhub-filter-bar">
			<input type="hidden" name="option" value="com_ambassadorhub">
			<input type="hidden" name="view" value="events">

			<select name="filter_group_id" onchange="this.form.submit()">
				<option value="0"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_GROUPS'); ?></option>
				<?php foreach ($this->groupOptions as $groupOption) : ?>
					<option value="<?php echo (int) $groupOption->id; ?>" <?php echo $groupId === (int) $groupOption->id ? 'selected' : ''; ?>>
						<?php echo $this->escape($groupOption->name); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<select name="filter_host" onchange="this.form.submit()">
				<option value="0"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_HOSTS'); ?></option>
				<?php foreach ($this->hostOptions as $hostOption) : ?>
					<option value="<?php echo (int) $hostOption->id; ?>" <?php echo $hostId === (int) $hostOption->id ? 'selected' : ''; ?>>
						<?php echo $this->escape($hostOption->name); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<?php if (!empty($this->categories)) : ?>
				<select name="filter_category" onchange="this.form.submit()">
					<option value=""><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_CATEGORIES'); ?></option>
					<?php foreach ($this->categories as $cat) : ?>
						<option value="<?php echo $this->escape($cat); ?>" <?php echo $cat === $category ? 'selected' : ''; ?>>
							<?php echo $this->escape($cat); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<select name="filter_time_scope" onchange="this.form.submit()">
				<option value="all" <?php echo $this->timeScope === 'all' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_TIME'); ?></option>
				<option value="upcoming" <?php echo $this->timeScope === 'upcoming' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_UPCOMING_ONLY'); ?></option>
			</select>

			<select name="filter_order_Dir" onchange="this.form.submit()">
				<option value="ASC" <?php echo $orderDirn === 'ASC' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_SOONEST_FIRST'); ?></option>
				<option value="DESC" <?php echo $orderDirn === 'DESC' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_LATEST_FIRST'); ?></option>
			</select>

			<?php if ($hasActiveFilters) : ?>
				<a class="ambassadorhub-caption" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=events'); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_EVENTS_CLEAR_FILTERS'); ?>
				</a>
			<?php endif; ?>
		</form>
	<?php endif; ?>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_NONE'); ?></p>
	<?php else : ?>
		<div class="ambassadorhub-card-grid">
			<?php foreach ($this->items as $event) :
				$isAttending = \in_array((int) $event->id, $this->attendingEventIds, true);
				$date        = new DateTime($event->event_date);
				?>
				<div class="ambassadorhub-card">
					<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=event&id=' . (int) $event->id); ?>" class="ambassadorhub-card__link">
						<div class="ambassadorhub-card__poster-wrap">
							<?php if (!empty($event->poster)) : ?>
								<img class="ambassadorhub-card__poster" src="<?php echo $this->escape($event->poster); ?>" alt="">
							<?php else : ?>
								<div class="ambassadorhub-card__poster ambassadorhub-card__poster--placeholder"></div>
							<?php endif; ?>

							<div class="ambassadorhub-card__date">
								<span class="ambassadorhub-card__date-month ambassadorhub-mono"><?php echo strtoupper($date->format('M')); ?></span>
								<span class="ambassadorhub-card__date-day ambassadorhub-mono"><?php echo $date->format('d'); ?></span>
								<span class="ambassadorhub-card__date-time ambassadorhub-mono"><?php echo $date->format('H:i'); ?></span>
							</div>
						</div>

						<div class="ambassadorhub-card__title" style="margin-top: 0.5rem;"><?php echo $this->escape($event->title); ?></div>

						<div class="ambassadorhub-card__meta ambassadorhub-caption">
							<?php if (!empty($event->status) && $event->status !== 'approved') : ?>
								<span class="ambassadorhub-status-tag <?php echo $statusClasses[$event->status] ?? ''; ?>">
									<?php echo Text::_('COM_AMBASSADORHUB_STATUS_' . strtoupper($event->status)); ?>
								</span>
							<?php endif; ?>
							<?php if (!empty($event->category)) : ?>
								<span><?php echo $this->escape($event->category); ?></span>
							<?php endif; ?>
							<span><?php echo $this->escape($event->group_name); ?></span>
						</div>

						<?php if (!empty($event->description)) : ?>
							<div class="ambassadorhub-card__description"><?php echo nl2br($this->escape($event->description)); ?></div>
						<?php endif; ?>

						<?php if (!empty($event->location)) : ?>
							<div class="ambassadorhub-caption"><?php echo $this->escape($event->location); ?></div>
						<?php endif; ?>

						<?php if (!empty($event->host_name)) : ?>
							<div class="ambassadorhub-caption"><?php echo Text::sprintf('COM_AMBASSADORHUB_EVENTS_HOSTED_BY', $this->escape($event->host_name)); ?></div>
						<?php endif; ?>

						<div class="ambassadorhub-caption ambassadorhub-card__interested">
							<?php echo Text::plural('COM_AMBASSADORHUB_EVENT_N_INTERESTED', (int) $event->attendee_count); ?>
							<?php if (!empty($event->attendee_names)) : ?>
								&mdash; <?php echo $this->escape(implode(', ', $event->attendee_names)); ?>
							<?php endif; ?>
						</div>
					</a>

					<div class="ambassadorhub-card__footer">
						<?php if ($user->guest) : ?>
							<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=login'); ?>">
								<?php echo Text::_('COM_AMBASSADORHUB_EVENT_LOGIN_TO_RSVP'); ?>
							</a>
						<?php elseif ($isAttending) : ?>
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
			<?php endforeach; ?>
		</div>

		<?php echo $this->pagination->getListFooter(); ?>
	<?php endif; ?>
</div>
