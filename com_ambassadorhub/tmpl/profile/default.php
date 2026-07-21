<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Profile\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user       = Factory::getApplication()->getIdentity();
$profile    = $this->profile;
$returnUrl  = Route::_('index.php?option=com_ambassadorhub&view=profile' . ($profile ? '&id=' . (int) $profile->id : ''), false);

echo AmbassadorhubHelper::renderTopbar('profile');
?>
<div class="ambassadorhub ambassadorhub-page">
	<?php if (!$profile) : ?>
		<?php if ($user->guest) : ?>
			<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_PROFILE'); ?></h1>
			<p class="ambassadorhub-caption" style="margin-top: 0.5rem;">
				<?php echo Text::_('COM_AMBASSADORHUB_PROFILE_SIGNIN_PROMPT'); ?>
			</p>
		<?php else : ?>
			<h1><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_NOT_FOUND'); ?></h1>
		<?php endif; ?>
	<?php else : ?>
		<div class="ambassadorhub-student-profile__header">
			<div class="ambassadorhub-student-profile__identity">
				<div class="ambassadorhub-student-profile__photo">
					<?php if (!empty($profile->profile_picture)) : ?>
						<img src="<?php echo $this->escape($profile->profile_picture); ?>" alt="">
					<?php else : ?>
						<div class="ambassadorhub-student-profile__photo-placeholder"></div>
					<?php endif; ?>
				</div>
				<div>
					<h1><?php echo $this->escape($profile->name); ?></h1>
					<?php if (!empty($profile->program)) : ?>
						<div class="ambassadorhub-caption" style="margin-top: 0.25rem;"><?php echo $this->escape($profile->program); ?></div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ($this->isOwner) : ?>
				<button
					type="button"
					class="ambassadorhub-btn"
					data-ambassadorhub-toggle="profile-edit-panel"
					data-label-show="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_PROFILE_EDIT')); ?>"
					data-label-hide="<?php echo $this->escape(Text::_('COM_AMBASSADORHUB_CANCEL')); ?>"
				>
					<?php echo Text::_('COM_AMBASSADORHUB_PROFILE_EDIT'); ?>
				</button>
			<?php elseif (!$user->guest) : ?>
				<?php if ($this->friendStatus === 'none') : ?>
					<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.send'); ?>" method="post">
						<input type="hidden" name="addressee_id" value="<?php echo (int) $profile->id; ?>">
						<input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
						<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_ADD_FRIEND'); ?></button>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				<?php elseif ($this->friendStatus === 'pending_outgoing') : ?>
					<span class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_PENDING'); ?></span>
				<?php elseif ($this->friendStatus === 'pending_incoming') : ?>
					<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.accept'); ?>" method="post" style="display:inline;">
						<input type="hidden" name="id" value="<?php echo (int) $this->friendshipId; ?>">
						<input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
						<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_ACCEPT'); ?></button>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
					<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.remove'); ?>" method="post" style="display:inline;">
						<input type="hidden" name="id" value="<?php echo (int) $this->friendshipId; ?>">
						<input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
						<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_FRIENDS_DECLINE'); ?></button>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				<?php elseif ($this->friendStatus === 'accepted') : ?>
					<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=friendship.remove'); ?>" method="post">
						<input type="hidden" name="id" value="<?php echo (int) $this->friendshipId; ?>">
						<input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
						<button type="submit" class="ambassadorhub-btn ambassadorhub-btn--outline"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_FRIEND_REMOVE'); ?></button>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ($this->isOwner) : ?>
			<form id="profile-edit-panel" class="ambassadorhub-inline-form" action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=profile.save'); ?>" method="post" hidden style="margin-top: 1.5rem;">
				<label for="profile-picture"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_PROFILE_PICTURE_LABEL'); ?></label>
				<input type="text" id="profile-picture" name="profile_picture" value="<?php echo $this->escape($profile->profile_picture ?? ''); ?>" placeholder="https://...">

				<label for="profile-bio"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_BIO_LABEL'); ?></label>
				<textarea id="profile-bio" name="bio" rows="4"><?php echo $this->escape($profile->bio ?? ''); ?></textarea>

				<label class="ambassadorhub-caption" style="display:flex;align-items:center;gap:0.5rem;">
					<input type="checkbox" name="show_groups_on_profile" value="1" <?php echo $this->groupsVisible ? 'checked' : ''; ?>>
					<?php echo Text::_('COM_AMBASSADORHUB_PROFILE_SHOW_GROUPS'); ?>
				</label>
				<label class="ambassadorhub-caption" style="display:flex;align-items:center;gap:0.5rem;">
					<input type="checkbox" name="show_friends_on_profile" value="1" <?php echo $this->friendsVisible ? 'checked' : ''; ?>>
					<?php echo Text::_('COM_AMBASSADORHUB_PROFILE_SHOW_FRIENDS'); ?>
				</label>
				<label class="ambassadorhub-caption" style="display:flex;align-items:center;gap:0.5rem;">
					<input type="checkbox" name="show_contributions_on_profile" value="1" <?php echo $this->contributionsVisible ? 'checked' : ''; ?>>
					<?php echo Text::_('COM_AMBASSADORHUB_PROFILE_SHOW_CONTRIBUTIONS'); ?>
				</label>

				<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_SAVE'); ?></button>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		<?php else : ?>
			<p style="margin-top: 1.5rem;"><?php echo nl2br($this->escape($profile->bio ?? '')); ?></p>
		<?php endif; ?>

		<?php if ($this->contributionsVisible) : ?>
			<section style="margin-top: 3rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_CONTRIBUTIONS_HEADER'); ?></h2>
				<div class="ambassadorhub-student-profile__stats">
					<div class="ambassadorhub-student-profile__stat">
						<div class="ambassadorhub-student-profile__stat-value ambassadorhub-mono"><?php echo (int) $this->contributions['posts']; ?></div>
						<span class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_POSTS_COUNT'); ?></span>
					</div>
					<div class="ambassadorhub-student-profile__stat">
						<div class="ambassadorhub-student-profile__stat-value ambassadorhub-mono"><?php echo (int) $this->contributions['comments']; ?></div>
						<span class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_COMMENTS_COUNT'); ?></span>
					</div>
					<div class="ambassadorhub-student-profile__stat">
						<div class="ambassadorhub-student-profile__stat-value ambassadorhub-mono"><?php echo (int) $this->contributions['eventsHosted']; ?></div>
						<span class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_EVENTS_HOSTED_COUNT'); ?></span>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($this->groupsVisible) : ?>
			<section style="margin-top: 3rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_GROUPS_HEADER'); ?></h2>
				<?php if (empty($this->groups)) : ?>
					<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_NO_GROUPS'); ?></p>
				<?php else : ?>
					<div class="ambassadorhub-card-grid">
						<?php foreach ($this->groups as $group) : ?>
							<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id); ?>" class="ambassadorhub-card ambassadorhub-card__link">
								<div class="ambassadorhub-card__title"><?php echo $this->escape($group->name); ?></div>
								<?php if (!empty($group->is_manager)) : ?>
									<div class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_MANAGER_TAG'); ?></div>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ($this->friendsVisible) : ?>
			<section style="margin-top: 3rem;">
				<h2><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_FRIENDS_HEADER'); ?></h2>
				<?php if (empty($this->friends)) : ?>
					<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_PROFILE_NO_FRIENDS'); ?></p>
				<?php else : ?>
					<?php foreach ($this->friends as $friend) : ?>
						<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=profile&id=' . (int) $friend->id); ?>" class="ambassadorhub-friends-row">
							<span><?php echo $this->escape($friend->name); ?></span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</div>
