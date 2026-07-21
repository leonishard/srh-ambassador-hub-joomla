<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Review\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

echo AmbassadorhubHelper::renderTopbar('review');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_REVIEW'); ?></h1>
	<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_SUBTITLE'); ?></p>

	<section style="margin-top: 2rem;">
		<h2><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_PROPOSALS_HEADING'); ?></h2>

		<?php if (empty($this->pendingProposals)) : ?>
			<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_PROPOSALS_NONE'); ?></p>
		<?php else : ?>
			<div class="ambassadorhub-card-grid">
				<?php foreach ($this->pendingProposals as $proposal) : ?>
					<div class="ambassadorhub-card">
						<div class="ambassadorhub-card__title"><?php echo $this->escape($proposal->name); ?></div>
						<div class="ambassadorhub-card__meta ambassadorhub-caption">
							<?php if (!empty($proposal->category)) : ?>
								<span><?php echo $this->escape($proposal->category); ?></span>
							<?php endif; ?>
						</div>
						<?php if (!empty($proposal->description)) : ?>
							<div class="ambassadorhub-card__description"><?php echo $this->escape($proposal->description); ?></div>
						<?php endif; ?>
						<p class="ambassadorhub-caption">
							<?php echo Text::sprintf('COM_AMBASSADORHUB_REVIEW_SUBMITTED_BY', $this->escape($proposal->submitted_by_name)); ?>
						</p>
						<div class="ambassadorhub-card__footer">
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=review.approveProposal'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $proposal->id; ?>">
								<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_APPROVE'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=review.rejectProposal'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $proposal->id; ?>">
								<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--danger"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_REJECT'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<section style="margin-top: 3rem;">
		<h2><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_EVENTS_HEADING'); ?></h2>

		<?php if (empty($this->pendingEvents)) : ?>
			<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_EVENTS_NONE'); ?></p>
		<?php else : ?>
			<div class="ambassadorhub-card-grid">
				<?php foreach ($this->pendingEvents as $event) : ?>
					<div class="ambassadorhub-card">
						<div class="ambassadorhub-card__title"><?php echo $this->escape($event->title); ?></div>
						<div class="ambassadorhub-card__meta ambassadorhub-caption">
							<span><?php echo $this->escape($event->group_name); ?></span>
							<?php if (!empty($event->category)) : ?>
								<span><?php echo $this->escape($event->category); ?></span>
							<?php endif; ?>
							<span class="ambassadorhub-mono"><?php echo HTMLHelper::_('date', $event->event_date, 'd M, H:i'); ?></span>
						</div>
						<?php if (!empty($event->description)) : ?>
							<div class="ambassadorhub-card__description"><?php echo $this->escape($event->description); ?></div>
						<?php endif; ?>
						<?php if (!empty($event->location)) : ?>
							<p class="ambassadorhub-caption"><?php echo $this->escape($event->location); ?></p>
						<?php endif; ?>
						<p class="ambassadorhub-caption">
							<?php echo Text::sprintf('COM_AMBASSADORHUB_REVIEW_SUBMITTED_BY', $this->escape($event->submitted_by_name)); ?>
						</p>
						<div class="ambassadorhub-card__footer">
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=review.approveEvent'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $event->id; ?>">
								<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_APPROVE'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=review.rejectEvent'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $event->id; ?>">
								<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--danger"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_REJECT'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<section style="margin-top: 3rem;">
		<h2><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_GROUPS_HEADING'); ?></h2>

		<?php if (empty($this->groups)) : ?>
			<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_GROUPS_NONE'); ?></p>
		<?php else : ?>
			<?php foreach ($this->groups as $group) : ?>
				<div class="ambassadorhub-group-detail__event-row">
					<span>
						<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id); ?>"><?php echo $this->escape($group->name); ?></a>
						<span class="ambassadorhub-caption">
							&mdash; <?php echo $group->manager_name ? $this->escape($group->manager_name) : Text::_('COM_AMBASSADORHUB_FIELD_MANAGER_NONE'); ?>
						</span>
					</span>
					<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=review.reassignManager'); ?>" method="post" style="display:flex; gap:0.5rem; align-items:center;">
						<input type="hidden" name="group_id" value="<?php echo (int) $group->id; ?>">
						<select name="manager_student_id">
							<option value="0"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_MANAGER_NONE'); ?></option>
							<?php foreach ($this->students as $student) : ?>
								<option value="<?php echo (int) $student->id; ?>" <?php echo (int) $group->manager_student_id === (int) $student->id ? 'selected' : ''; ?>>
									<?php echo $this->escape($student->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_REVIEW_REASSIGN'); ?></button>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>
</div>
