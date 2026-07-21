<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Mycircle\HtmlView  $this
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Same-day timestamps show as a time (e.g. "14:05"), older ones as a short date --
 * matches the reference app's post/activity timestamp treatment.
 *
 * @param   string  $sqlDate  A MySQL datetime string.
 *
 * @return  string
 */
$formatTimestamp = static function (string $sqlDate): string {
	$date = new DateTime($sqlDate);
	$now  = new DateTime();

	return $date->format('Y-m-d') === $now->format('Y-m-d') ? $date->format('H:i') : $date->format('M d');
};

$formatEventDate = static fn (string $sqlDate): string => (new DateTime($sqlDate))->format('M d');
$formatEventTime = static fn (string $sqlDate): string => (new DateTime($sqlDate))->format('H:i');

echo AmbassadorhubHelper::renderTopbar('mycircle');
?>
<div class="ambassadorhub ambassadorhub-page">
	<div class="ambassadorhub-my-circle">
		<header class="ambassadorhub-my-circle__header">
			<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_MYCIRCLE'); ?></h1>
		</header>

		<?php if ($this->isGuest) : ?>
			<p class="ambassadorhub-caption" style="margin-top: 0.5rem;">
				<?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_SIGNIN_PROMPT'); ?>
			</p>
		<?php else : ?>
			<section class="ambassadorhub-my-circle__stats">
				<div class="ambassadorhub-stat-card">
					<div class="ambassadorhub-stat-card__value ambassadorhub-mono"><?php echo (int) $this->stats['groupsFollowed']; ?></div>
					<span class="ambassadorhub-stat-card__label ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_GROUPS_FOLLOWED'); ?></span>
				</div>
				<div class="ambassadorhub-stat-card">
					<div class="ambassadorhub-stat-card__value ambassadorhub-mono"><?php echo (int) $this->stats['acceptedFriends']; ?></div>
					<span class="ambassadorhub-stat-card__label ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_ACCEPTED_FRIENDS'); ?></span>
				</div>
				<div class="ambassadorhub-stat-card">
					<div class="ambassadorhub-stat-card__value ambassadorhub-mono"><?php echo (int) $this->stats['upcomingEvents']; ?></div>
					<span class="ambassadorhub-stat-card__label ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_UPCOMING_EVENTS'); ?></span>
				</div>
				<div class="ambassadorhub-stat-card">
					<div class="ambassadorhub-stat-card__value ambassadorhub-mono"><?php echo (int) $this->stats['eventsAttending']; ?></div>
					<span class="ambassadorhub-stat-card__label ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_EVENTS_ATTENDING'); ?></span>
				</div>
			</section>

			<div class="ambassadorhub-my-circle__dashboard">
				<section class="ambassadorhub-my-circle__posts">
					<h2 class="ambassadorhub-my-circle__section-title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_RECENT_POSTS_HEADER'); ?></h2>

					<?php if (empty($this->recentPosts)) : ?>
						<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_NO_RECENT_POSTS'); ?></p>
					<?php else : ?>
						<div class="ambassadorhub-my-circle__post-list">
							<?php foreach ($this->recentPosts as $post) : ?>
								<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $post->group_id); ?>" class="ambassadorhub-post-preview">
									<?php if (!empty($post->media)) : ?>
										<img class="ambassadorhub-post-preview__thumb" src="<?php echo $this->escape($post->media); ?>" alt="">
									<?php endif; ?>
									<div class="ambassadorhub-post-preview__body">
										<div class="ambassadorhub-post-preview__header">
											<span class="ambassadorhub-post-preview__group"><?php echo $this->escape($post->group_name); ?></span>
											<span class="ambassadorhub-post-preview__time ambassadorhub-mono ambassadorhub-caption"><?php echo $formatTimestamp($post->created); ?></span>
										</div>
										<div class="ambassadorhub-post-preview__author ambassadorhub-caption">
											<?php echo Text::sprintf('COM_AMBASSADORHUB_MYCIRCLE_POSTED_BY', $this->escape($post->author_name)); ?>
										</div>
										<div class="ambassadorhub-post-preview__content"><?php echo $this->escape($post->content); ?></div>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>

				<aside class="ambassadorhub-my-circle__sidebar">
					<section>
						<h2 class="ambassadorhub-my-circle__section-title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_UPCOMING_EVENTS_HEADER'); ?></h2>

						<?php if (empty($this->upcomingEvents)) : ?>
							<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_NO_UPCOMING_EVENTS'); ?></p>
						<?php else : ?>
							<div class="ambassadorhub-my-circle__event-list">
								<?php foreach ($this->upcomingEvents as $event) : ?>
									<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $event->group_id); ?>" class="ambassadorhub-event-row">
										<div class="ambassadorhub-event-row__date ambassadorhub-mono">
											<span><?php echo $formatEventDate($event->event_date); ?></span>
											<span class="ambassadorhub-event-row__time"><?php echo $formatEventTime($event->event_date); ?></span>
										</div>
										<div class="ambassadorhub-event-row__body">
											<div class="ambassadorhub-event-row__title"><?php echo $this->escape($event->title); ?></div>
											<div class="ambassadorhub-caption">
												<?php echo $this->escape($event->group_name); ?><?php echo $event->location ? ' · ' . $this->escape($event->location) : ''; ?>
											</div>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</section>

					<section>
						<h2 class="ambassadorhub-my-circle__section-title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_ACTIVITY_HEADER'); ?></h2>

						<?php if (empty($this->activity)) : ?>
							<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_NO_ACTIVITY'); ?></p>
						<?php else : ?>
							<div class="ambassadorhub-my-circle__ledger">
								<?php foreach ($this->activity as $entry) : ?>
									<div class="ambassadorhub-ledger-entry" style="--accent: var(<?php echo $entry['accent']; ?>);">
										<span class="ambassadorhub-ledger-entry__time ambassadorhub-mono"><?php echo $formatTimestamp($entry['timestamp']); ?></span>
										<span class="ambassadorhub-ledger-entry__text"><?php echo $entry['text']; ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</section>

					<section>
						<h2 class="ambassadorhub-my-circle__section-title ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_ATTENDING_HEADER'); ?></h2>

						<?php if (empty($this->attendingEvents)) : ?>
							<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_MYCIRCLE_NO_ATTENDING'); ?></p>
						<?php else : ?>
							<div class="ambassadorhub-my-circle__event-list">
								<?php foreach ($this->attendingEvents as $event) : ?>
									<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $event->group_id); ?>" class="ambassadorhub-event-row">
										<div class="ambassadorhub-event-row__date ambassadorhub-mono">
											<span><?php echo $formatEventDate($event->event_date); ?></span>
											<span class="ambassadorhub-event-row__time"><?php echo $formatEventTime($event->event_date); ?></span>
										</div>
										<div class="ambassadorhub-event-row__body">
											<div class="ambassadorhub-event-row__title"><?php echo $this->escape($event->title); ?></div>
											<div class="ambassadorhub-caption">
												<?php echo $this->escape($event->group_name); ?><?php echo $event->location ? ' · ' . $this->escape($event->location) : ''; ?>
											</div>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</section>
				</aside>
			</div>
		<?php endif; ?>
	</div>
</div>
