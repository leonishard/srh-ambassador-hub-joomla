<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Administrator\View\Event\HtmlView  $this
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
?>
<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&task=event.save'); ?>" method="post" name="adminForm" id="event-form" class="form-validate">

	<div class="row">
		<div class="col-lg-9">
			<div class="card">
				<div class="card-body">
					<?php foreach ($this->form->getFieldset('event') as $field) : ?>
						<?php if ($field->hidden) : ?>
							<?php echo $field->input; ?>
						<?php else : ?>
							<div class="mb-3">
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
