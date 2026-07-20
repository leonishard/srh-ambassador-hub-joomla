<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Administrator\View\Events\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canEdit   = $user->authorise('core.edit', 'com_ambassadorhub');

$statusClasses = [
	'pending'  => 'bg-warning',
	'approved' => 'bg-success',
	'rejected' => 'bg-danger',
];
?>
<form action="<?php echo Route::_('index.php?option=com_ambassadorhub&view=events'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table" id="eventList">
				<caption class="visually-hidden"><?php echo Text::_('COM_AMBASSADORHUB_EVENTS_TABLE_CAPTION'); ?></caption>
				<thead>
					<tr>
						<td class="w-1 text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</td>
						<th scope="col" class="w-1 text-center">
							<?php echo Text::_('COM_AMBASSADORHUB_FIELD_STATUS_LABEL'); ?>
						</th>
						<th scope="col">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_AMBASSADORHUB_FIELD_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="d-none d-md-table-cell">
							<?php echo Text::_('COM_AMBASSADORHUB_FIELD_GROUP_LABEL'); ?>
						</th>
						<th scope="col" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_AMBASSADORHUB_FIELD_EVENT_DATE_LABEL', 'a.event_date', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-5 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
							</td>
							<td class="text-center">
								<span class="badge <?php echo $statusClasses[$item->status] ?? 'bg-secondary'; ?>">
									<?php echo Text::_('COM_AMBASSADORHUB_STATUS_' . strtoupper($item->status)); ?>
								</span>
							</td>
							<th scope="row">
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_ambassadorhub&task=event.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</th>
							<td class="d-none d-md-table-cell">
								<?php echo $this->escape($item->group_name ?: ''); ?>
							</td>
							<td class="d-none d-md-table-cell">
								<?php echo HTMLHelper::_('date', $item->event_date, Text::_('DATE_FORMAT_LC5')); ?>
							</td>
							<td class="d-none d-md-table-cell">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php echo $this->pagination->getListFooter(); ?>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
