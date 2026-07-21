<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Event\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$event  = $this->item;
$isNew  = empty($event->id);

echo AmbassadorhubHelper::renderTopbar('events');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1>
		<?php echo Text::sprintf($isNew ? 'COM_AMBASSADORHUB_EVENT_NEW_TITLE' : 'COM_AMBASSADORHUB_EVENT_EDIT_TITLE', $this->escape($this->groupName)); ?>
	</h1>

	<?php if (!$isNew && $event->status === 'pending') : ?>
		<p class="ambassadorhub-event-status-note"><?php echo Text::_('COM_AMBASSADORHUB_EVENT_PENDING_NOTE'); ?></p>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=event.save'); ?>" method="post">
		<input type="hidden" name="id" value="<?php echo (int) $event->id; ?>">
		<input type="hidden" name="group_id" value="<?php echo (int) $this->groupId; ?>">

		<div class="ambassadorhub-field">
			<label for="event-title"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_TITLE_LABEL'); ?></label>
			<input type="text" id="event-title" name="title" value="<?php echo $this->escape($event->title); ?>" required>
		</div>

		<div class="ambassadorhub-field">
			<label for="event-date"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_EVENT_DATE_LABEL'); ?></label>
			<input type="datetime-local" id="event-date" name="event_date" value="<?php echo $this->escape(str_replace(' ', 'T', substr((string) $event->event_date, 0, 16))); ?>" required>
		</div>

		<div class="ambassadorhub-field">
			<label for="event-location"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_LOCATION_LABEL'); ?></label>
			<input type="text" id="event-location" name="location" value="<?php echo $this->escape($event->location ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="event-category"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_CATEGORY_LABEL'); ?></label>
			<input type="text" id="event-category" name="category" value="<?php echo $this->escape($event->category ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="event-poster"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_POSTER_LABEL'); ?></label>
			<input type="text" id="event-poster" name="poster" value="<?php echo $this->escape($event->poster ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="event-description"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_DESCRIPTION_LABEL'); ?></label>
			<textarea id="event-description" name="description" rows="8"><?php echo $this->escape($event->description ?? ''); ?></textarea>
		</div>

		<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('JSAVE'); ?></button>
		<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $this->groupId); ?>">
			<?php echo Text::_('JCANCEL'); ?>
		</a>

		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
