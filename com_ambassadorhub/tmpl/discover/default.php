<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Discover\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$isGuestVar = $this->isGuest;

/**
 * Renders one Discover section (title + blurb + card grid), or nothing if empty --
 * shared markup for the Recommended/Popular/New sections below.
 *
 * @param   string    $titleKey  Language key for the section heading.
 * @param   string    $blurbKey  Language key for the section blurb.
 * @param   object[]  $groups    The Groups to render.
 *
 * @return  void
 */
$renderSection = function (string $titleKey, string $blurbKey, array $groups) use ($isGuestVar) {
	if (!$groups) {
		return;
	}
	?>
	<section style="margin-top: 2.5rem;">
		<h2 class="ambassadorhub-mono" style="font-size: 0.8125rem; color: var(--ash-fg); letter-spacing: 0.06em;">
			<?php echo Text::_($titleKey); ?>
		</h2>
		<p class="ambassadorhub-caption" style="margin-top: 0.35rem;"><?php echo Text::_($blurbKey); ?></p>

		<div class="ambassadorhub-card-grid">
			<?php foreach ($groups as $group) :
				$detailUrl = Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id);
				$imageUrl  = $group->cover_image ?: $group->profile_picture;
				?>
				<div class="ambassadorhub-card">
					<a href="<?php echo $detailUrl; ?>" class="ambassadorhub-card__link">
						<?php if ($imageUrl) : ?>
							<img class="ambassadorhub-card__poster" src="<?php echo $this->escape($imageUrl); ?>" alt="">
						<?php else : ?>
							<div class="ambassadorhub-card__poster ambassadorhub-card__poster--placeholder"></div>
						<?php endif; ?>
						<div class="ambassadorhub-card__title"><?php echo $this->escape($group->name); ?></div>
						<div class="ambassadorhub-card__meta ambassadorhub-caption">
							<?php if (!empty($group->category)) : ?>
								<span><?php echo $this->escape($group->category); ?></span>
							<?php endif; ?>
							<span><?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?></span>
						</div>
						<div class="ambassadorhub-card__description"><?php echo $this->escape($group->description); ?></div>
					</a>

					<?php if (!$isGuestVar) : ?>
						<div class="ambassadorhub-card__footer">
							<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.join'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
								<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_JOIN'); ?></button>
								<?php echo HTMLHelper::_('form.token'); ?>
							</form>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
};

echo AmbassadorhubHelper::renderTopbar('discover');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_DISCOVER'); ?></h1>
	<p class="ambassadorhub-caption" style="margin-top: 0.5rem;">
		<?php echo Text::_($this->isGuest ? 'COM_AMBASSADORHUB_DISCOVER_SUBTITLE_GUEST' : 'COM_AMBASSADORHUB_DISCOVER_SUBTITLE_AUTH'); ?>
	</p>

	<?php if (!$this->hasGroups) : ?>
		<p class="ambassadorhub-caption" style="margin-top: 1.5rem;"><?php echo Text::_('COM_AMBASSADORHUB_GROUPS_NONE'); ?></p>
	<?php elseif ($this->nothingToDiscover) : ?>
		<p class="ambassadorhub-caption" style="margin-top: 1.5rem;"><?php echo Text::_('COM_AMBASSADORHUB_DISCOVER_ALL_JOINED'); ?></p>
	<?php endif; ?>

	<?php $renderSection('COM_AMBASSADORHUB_DISCOVER_RECOMMENDED_TITLE', 'COM_AMBASSADORHUB_DISCOVER_RECOMMENDED_BLURB', $this->recommended); ?>
	<?php $renderSection('COM_AMBASSADORHUB_DISCOVER_POPULAR_TITLE', 'COM_AMBASSADORHUB_DISCOVER_POPULAR_BLURB', $this->popular); ?>
	<?php $renderSection('COM_AMBASSADORHUB_DISCOVER_NEW_TITLE', 'COM_AMBASSADORHUB_DISCOVER_NEW_BLURB', $this->newest); ?>
</div>
