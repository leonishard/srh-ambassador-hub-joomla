<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Groups\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();

echo AmbassadorhubHelper::renderTopbar('groups');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_GROUPS'); ?></h1>
	<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_GROUPS_SUBTITLE'); ?></p>

	<p class="ambassadorhub-caption" style="margin-top: 1rem;">
		<?php echo Text::_('COM_AMBASSADORHUB_GROUPS_PROPOSE_COMING_SOON'); ?>
	</p>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_GROUPS_NONE'); ?></p>
	<?php else : ?>
		<div class="ambassadorhub-card-grid">
			<?php foreach ($this->items as $group) :
				$isMember  = \in_array((int) $group->id, $this->joinedGroupIds, true);
				$detailUrl = Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id);
				$imageUrl  = $group->cover_image ?: $group->profile_picture;
				?>
				<div class="ambassadorhub-card">
					<a href="<?php echo $detailUrl; ?>" class="ambassadorhub-card__link">
						<?php if ($imageUrl) : ?>
							<img class="ambassadorhub-card__poster" src="<?php echo $this->escape($imageUrl); ?>" alt="">
						<?php endif; ?>
						<div class="ambassadorhub-card__title"><?php echo $this->escape($group->name); ?></div>
						<div class="ambassadorhub-card__meta ambassadorhub-caption">
							<?php if (!empty($group->category)) : ?>
								<span><?php echo $this->escape($group->category); ?></span>
							<?php endif; ?>
						</div>
						<div class="ambassadorhub-card__description"><?php echo $this->escape($group->description); ?></div>
					</a>

					<p class="ambassadorhub-caption">
						<?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?>
					</p>

					<div class="ambassadorhub-card__footer">
						<?php if ($user->guest) : ?>
							<a class="ambassadorhub-btn ambassadorhub-btn--outline" href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>">
								<?php echo Text::_('COM_AMBASSADORHUB_GROUP_LOGIN_TO_JOIN'); ?>
							</a>
						<?php elseif ($isMember) : ?>
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
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php echo $this->pagination->getListFooter(); ?>
	<?php endif; ?>
</div>
