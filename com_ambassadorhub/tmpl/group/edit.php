<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Group\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$group = $this->item;
?>
<div class="ambassadorhub ambassadorhub-group-edit">
	<h1><?php echo Text::sprintf('COM_AMBASSADORHUB_GROUP_EDIT_TITLE', $this->escape($group->name)); ?></h1>

	<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=group.save'); ?>" method="post">
		<input type="hidden" name="id" value="<?php echo (int) $group->id; ?>">

		<div class="ambassadorhub-field">
			<label for="group-name"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_NAME_LABEL'); ?></label>
			<input type="text" id="group-name" name="name" value="<?php echo $this->escape($group->name); ?>" required>
		</div>

		<div class="ambassadorhub-field">
			<label for="group-category"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_CATEGORY_LABEL'); ?></label>
			<input type="text" id="group-category" name="category" value="<?php echo $this->escape($group->category ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="group-cover-image"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_COVER_IMAGE_LABEL'); ?></label>
			<input type="text" id="group-cover-image" name="cover_image" value="<?php echo $this->escape($group->cover_image ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="group-profile-picture"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_PROFILE_PICTURE_LABEL'); ?></label>
			<input type="text" id="group-profile-picture" name="profile_picture" value="<?php echo $this->escape($group->profile_picture ?? ''); ?>">
		</div>

		<div class="ambassadorhub-field">
			<label for="group-description"><?php echo Text::_('COM_AMBASSADORHUB_FIELD_DESCRIPTION_LABEL'); ?></label>
			<textarea id="group-description" name="description" rows="8"><?php echo $this->escape($group->description ?? ''); ?></textarea>
		</div>

		<button type="submit" class="ambassadorhub-btn"><?php echo Text::_('JSAVE'); ?></button>
		<a class="ambassadorhub-btn" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=group&id=' . (int) $group->id); ?>">
			<?php echo Text::_('JCANCEL'); ?>
		</a>

		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
