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

/**
 * Renders one Event as a full card (poster + overlapping date badge, title, meta,
 * description, location) -- shared markup for the pending/upcoming/past sections below.
 *
 * @param   object   $event  The Event row.
 * @param   boolean  $muted  Whether to render it visually deprioritized (past Events).
 *
 * @return  void
 */
$canManageForCards = $this->canManage;
$renderEventCard   = function ($event, bool $muted = false) use ($canManageForCards) {
	$date = new DateTime($event->event_date);
	?>
	<div class="ambassadorhub-card<?php echo $muted ? ' ambassadorhub-card--muted' : ''; ?>">
		<div class="ambassadorhub-card__poster-wrap">
			<?php if (!empty($event->poster)) : ?>
				<img class="ambassadorhub-card__poster" src="<?php echo htmlspecialchars($event->poster, ENT_QUOTES, 'UTF-8'); ?>" alt="">
			<?php else : ?>
				<div class="ambassadorhub-card__poster ambassadorhub-card__poster--placeholder"></div>
			<?php endif; ?>
			<div class="ambassadorhub-card__date">
				<span class="ambassadorhub-card__date-month ambassadorhub-mono"><?php echo strtoupper($date->format('M')); ?></span>
				<span class="ambassadorhub-card__date-day ambassadorhub-mono"><?php echo $date->format('d'); ?></span>
				<span class="ambassadorhub-card__date-time ambassadorhub-mono"><?php echo $date->format('H:i'); ?></span>
			</div>
		</div>
		<div class="ambassadorhub-card__title" style="margin-top: 0.5rem;"><?php echo htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php if ($event->status !== 'approved') : ?>
			<div class="ambassadorhub-card__meta">
				<span class="ambassadorhub-status-tag ambassadorhub-status-tag--<?php echo $event->status; ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_STATUS_' . strtoupper($event->status)); ?>
				</span>
			</div>
		<?php endif; ?>
		<?php if (!empty($event->description)) : ?>
			<div class="ambassadorhub-card__description"><?php echo htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php endif; ?>
		<?php if (!empty($event->location)) : ?>
			<div class="ambassadorhub-caption"><?php echo htmlspecialchars($event->location, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php endif; ?>
		<?php if ($canManageForCards) : ?>
			<div class="ambassadorhub-card__footer">
				<a class="ambassadorhub-caption" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=event&id=' . (int) $event->id); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENT_EDIT'); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<?php
};

echo AmbassadorhubHelper::renderTopbar('groups');
?>
<div class="ambassadorhub ambassadorhub-page">
	<p class="ambassadorhub-caption">
		<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=groups'); ?>">&larr; <?php echo Text::_('COM_AMBASSADORHUB_GROUP_BACK_TO_GROUPS'); ?></a>
	</p>

	<div class="ambassadorhub-group-detail__cover">
		<?php if (!empty($group->cover_image)) : ?>
			<img src="<?php echo $this->escape($group->cover_image); ?>" alt="">
		<?php else : ?>
			<div class="ambassadorhub-group-detail__cover-placeholder"></div>
		<?php endif; ?>
	</div>

	<div class="ambassadorhub-group-detail__header">
		<div class="ambassadorhub-group-detail__identity">
			<div class="ambassadorhub-group-detail__avatar">
				<?php if (!empty($group->profile_picture)) : ?>
					<img src="<?php echo $this->escape($group->profile_picture); ?>" alt="">
				<?php else : ?>
					<div class="ambassadorhub-group-detail__avatar-placeholder"></div>
				<?php endif; ?>
			</div>

			<div>
				<h1><?php echo $this->escape($group->name); ?></h1>
				<p class="ambassadorhub-caption">
					<?php if (!empty($group->category)) : ?>
						<span><?php echo $this->escape($group->category); ?></span> &middot;
					<?php endif; ?>
					<?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?>
				</p>
				<?php if (!empty($group->manager_name)) : ?>
					<p class="ambassadorhub-caption">
						<?php echo Text::sprintf('COM_AMBASSADORHUB_GROUP_MANAGED_BY', $this->escape($group->manager_name)); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="ambassadorhub-group-detail__header-actions">
			<?php if ($this->canManage) : ?>
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&layout=edit&id=' . (int) $group->id); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EDIT'); ?>
				</a>
				<button
					type="button"
					class="ambassadorhub-btn"
					data-ambassadorhub-toggle="new-post-panel"
					data-label-show="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_POST_NEW_LABEL')); ?>"
					data-label-hide="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_CANCEL')); ?>"
				>
					<?php echo Text::_('COM_AMBASSADORHUB_POST_NEW_LABEL'); ?>
				</button>
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=event&group_id=' . (int) $group->id); ?>">
					<?php echo Text::_('COM_AMBASSADORHUB_EVENT_ADD'); ?>
				</a>
			<?php endif; ?>

			<?php if ($user->guest) : ?>
				<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=login'); ?>">
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
				<p class="ambassadorhub-card__description" style="margin-top: 1.5rem;"><?php echo nl2br($this->escape($group->description)); ?></p>
			<?php endif; ?>

			<?php if ($this->canManage) : ?>
				<form id="new-post-panel" class="ambassadorhub-inline-form" action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=post.save'); ?>" method="post" hidden>
					<input type="hidden" name="group_id" value="<?php echo (int) $group->id; ?>">
					<label for="post-content"><?php echo Text::_('COM_AMBASSADORHUB_POST_NEW_LABEL'); ?></label>
					<textarea id="post-content" name="content" rows="3" placeholder="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_POST_PLACEHOLDER')); ?>" required></textarea>
					<label for="post-media"><?php echo Text::_('COM_AMBASSADORHUB_POST_MEDIA_LABEL'); ?></label>
					<input type="text" id="post-media" name="media" placeholder="https://...">
					<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_POST_SUBMIT'); ?></button>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>

			<section style="margin-top: 2rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_POSTS_HEADING'); ?></h2>

				<?php if (empty($this->posts)) : ?>
					<p class="ambassadorhub-caption" style="margin-top: 0.5rem;"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_POSTS_NONE'); ?></p>
				<?php else : ?>
					<div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
						<?php foreach ($this->posts as $post) : ?>
							<div class="ambassadorhub-card ambassadorhub-post">
								<div class="ambassadorhub-post__meta ambassadorhub-caption">
									<span><?php echo $this->escape($post->author_name); ?></span>
									<span class="ambassadorhub-mono"><?php echo HTMLHelper::_('date', $post->created, 'd M, H:i'); ?></span>
								</div>
								<div class="ambassadorhub-post__content"><?php echo nl2br($this->escape($post->content)); ?></div>

								<?php if (!empty($post->media)) : ?>
									<div class="ambassadorhub-post__media">
										<img src="<?php echo $this->escape($post->media); ?>" alt="">
									</div>
								<?php endif; ?>

								<?php if (!empty($post->comments)) : ?>
									<div class="ambassadorhub-post__comments">
										<?php foreach ($post->comments as $comment) :
											$canDeleteComment = $this->canManage || ($this->currentStudentId && (int) $comment->author_id === (int) $this->currentStudentId);
											?>
											<div class="ambassadorhub-comment-row">
												<div class="ambassadorhub-comment-row__body">
													<div class="ambassadorhub-comment-row__meta">
														<span class="ambassadorhub-comment-row__author"><?php echo $this->escape($comment->author_name); ?></span>
														<span class="ambassadorhub-mono ambassadorhub-caption"><?php echo HTMLHelper::_('date', $comment->created, 'd M, H:i'); ?></span>
														<?php if ($canDeleteComment) : ?>
															<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=comment.delete'); ?>" method="post" style="display:inline;">
																<input type="hidden" name="id" value="<?php echo (int) $comment->id; ?>">
																<button type="submit" class="ambassadorhub-caption" style="background:none;border:none;cursor:pointer;color:var(--status-rejected);padding:0;">
																	<?php echo Text::_('JACTION_DELETE'); ?>
																</button>
																<?php echo HTMLHelper::_('form.token'); ?>
															</form>
														<?php endif; ?>
													</div>
													<div class="ambassadorhub-comment-row__text"><?php echo $this->escape($comment->content); ?></div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<?php if ($this->isMember) : ?>
									<form class="ambassadorhub-post__comment-form" action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=comment.save'); ?>" method="post">
										<input type="hidden" name="post_id" value="<?php echo (int) $post->id; ?>">
										<input type="text" name="content" placeholder="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_COMMENT_PLACEHOLDER')); ?>" required>
										<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_COMMENT_SUBMIT'); ?></button>
										<?php echo HTMLHelper::_('form.token'); ?>
									</form>
								<?php else : ?>
									<p class="ambassadorhub-caption ambassadorhub-post__join-prompt"><?php echo Text::_('COM_AMBASSADORHUB_COMMENT_JOIN_TO_COMMENT'); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<?php if ($this->canManage && !empty($this->pendingEvents)) : ?>
				<section style="margin-top: 3rem;">
					<h2 class="ambassadorhub-mono" style="font-size: 0.8125rem; color: var(--ash-fg); letter-spacing: 0.06em;">
						<?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_AWAITING'); ?>
					</h2>
					<div class="ambassadorhub-card-grid">
						<?php foreach ($this->pendingEvents as $event) : $renderEventCard($event); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<section style="margin-top: 3rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_UPCOMING'); ?></h2>
				<?php if (empty($this->upcomingEvents)) : ?>
					<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_NO_UPCOMING'); ?></p>
				<?php else : ?>
					<div class="ambassadorhub-card-grid">
						<?php foreach ($this->upcomingEvents as $event) : $renderEventCard($event); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<section style="margin-top: 3rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_PAST'); ?></h2>
				<?php if (empty($this->pastEvents)) : ?>
					<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_EVENTS_NO_PAST'); ?></p>
				<?php else : ?>
					<div class="ambassadorhub-card-grid">
						<?php foreach ($this->pastEvents as $event) : $renderEventCard($event, true); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		</div>

		<?php if (!empty($this->members)) : ?>
			<aside class="ambassadorhub-group-detail__sidebar">
				<h3><?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS_HEADER', \count($this->members)); ?></h3>
				<?php foreach ($this->members as $member) : ?>
					<a class="ambassadorhub-group-detail__event-row" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $member->id); ?>">
						<span><?php echo $this->escape($member->name); ?></span>
					</a>
				<?php endforeach; ?>
			</aside>
		<?php endif; ?>
	</div>
</div>
