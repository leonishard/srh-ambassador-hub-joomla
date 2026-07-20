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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();
?>
<div class="ambassadorhub ambassadorhub-groups">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_GROUPS'); ?></h1>

	<p class="ambassadorhub-propose-note">
		<?php echo Text::_('COM_AMBASSADORHUB_GROUPS_PROPOSE_COMING_SOON'); ?>
	</p>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_GROUPS_NONE'); ?></p>
	<?php else : ?>
		<ul class="ambassadorhub-group-list">
			<?php foreach ($this->items as $group) :
				$isMember = \in_array((int) $group->id, $this->joinedGroupIds, true);
				$detailUrl = Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id);
				?>
				<li class="ambassadorhub-group-card">
					<h2><a href="<?php echo $detailUrl; ?>"><?php echo $this->escape($group->name); ?></a></h2>

					<?php if (!empty($group->category)) : ?>
						<p class="ambassadorhub-group-category"><?php echo $this->escape($group->category); ?></p>
					<?php endif; ?>

					<p class="ambassadorhub-group-members">
						<?php echo Text::plural('COM_AMBASSADORHUB_GROUP_N_MEMBERS', (int) $group->member_count); ?>
					</p>

					<?php if ($user->guest) : ?>
						<p><a href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_LOGIN_TO_JOIN'); ?></a></p>
					<?php elseif ($isMember) : ?>
						<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.leave'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
							<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_LEAVE'); ?></button>
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
					<?php else : ?>
						<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.join'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">
							<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('COM_AMBASSADORHUB_GROUP_JOIN'); ?></button>
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php echo $this->pagination->getListFooter(); ?>
	<?php endif; ?>
</div>
