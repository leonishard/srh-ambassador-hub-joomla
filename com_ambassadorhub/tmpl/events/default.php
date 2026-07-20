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
$orderCol  = $input->getCmd('filter_order', 'a.event_date');
$orderDirn = $input->getCmd('filter_order_Dir', 'ASC');

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

	<form method="get" class="ambassadorhub-filter-bar">
		<input type="hidden" name="option" value="com_ambassadorhub">
		<input type="hidden" name="view" value="events">

		<div>
			<label for="filter_category"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_CATEGORIES'); ?></label>
			<select name="filter_category" id="filter_category" onchange="this.form.submit()">
				<option value=""><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_ALL_CATEGORIES'); ?></option>
				<?php foreach ($this->categories as $cat) : ?>
					<option value="<?php echo $this->escape($cat); ?>" <?php echo $cat === $category ? 'selected' : ''; ?>>
						<?php echo $this->escape($cat); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="filter_order"><?php echo Text::_('JGLOBAL_SORT_BY'); ?></label>
			<select name="filter_order" id="filter_order" onchange="this.form.submit()">
				<option value="a.event_date" <?php echo $orderCol === 'a.event_date' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_FIELD_EVENT_DATE_LABEL'); ?></option>
				<option value="a.title" <?php echo $orderCol === 'a.title' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_FIELD_TITLE_LABEL'); ?></option>
			</select>
		</div>

		<div>
			<label for="filter_order_Dir"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_DIRECTION'); ?></label>
			<select name="filter_order_Dir" id="filter_order_Dir" onchange="this.form.submit()">
				<option value="ASC" <?php echo $orderDirn === 'ASC' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_SOONEST_FIRST'); ?></option>
				<option value="DESC" <?php echo $orderDirn === 'DESC' ? 'selected' : ''; ?>><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_LATEST_FIRST'); ?></option>
			</select>
		</div>

		<div>
			<label for="filter_upcoming_only"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_UPCOMING_ONLY'); ?></label>
			<input type="checkbox" name="filter_upcoming_only" id="filter_upcoming_only" value="1" onchange="this.form.submit()" <?php echo $this->upcomingOnly ? 'checked' : ''; ?>>
		</div>
	</form>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_NONE'); ?></p>
	<?php else : ?>
		<div class="ambassadorhub-card-grid">
			<?php foreach ($this->items as $event) :
				$isAttending = \in_array((int) $event->id, $this->attendingEventIds, true);
				$date        = new DateTime($event->event_date);
				?>
				<div class="ambassadorhub-card">
					<div class="ambassadorhub-card__date">
						<span class="ambassadorhub-card__date-month ambassadorhub-mono"><?php echo strtoupper($date->format('M')); ?></span>
						<span class="ambassadorhub-card__date-day ambassadorhub-mono"><?php echo $date->format('d'); ?></span>
						<span class="ambassadorhub-card__date-time ambassadorhub-mono"><?php echo $date->format('H:i'); ?></span>
					</div>

					<div class="ambassadorhub-card__title"><?php echo $this->escape($event->title); ?></div>

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

					<div class="ambassadorhub-caption ambassadorhub-card__interested">
						<?php echo Text::plural('COM_AMBASSADORHUB_EVENT_N_INTERESTED', (int) $event->attendee_count); ?>
						<?php if (!empty($event->attendee_names)) : ?>
							&mdash; <?php echo $this->escape(implode(', ', $event->attendee_names)); ?>
						<?php endif; ?>
					</div>

					<div class="ambassadorhub-card__footer">
						<?php if ($user->guest) : ?>
							<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>">
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
