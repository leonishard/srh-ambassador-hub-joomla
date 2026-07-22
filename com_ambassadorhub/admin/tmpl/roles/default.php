<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Administrator\View\Roles\HtmlView  $this
 */

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renders a comma-separated list of Group names, or a muted "none yet" placeholder.
 *
 * @param   string[]  $groupNames  The Group names.
 * @param   string    $emptyKey    Language key for the empty-state placeholder.
 *
 * @return  void
 */
$renderGroupList = function (array $groupNames, string $emptyKey) {
	if (!$groupNames) {
		echo '<span class="text-muted">' . Text::_($emptyKey) . '</span>';

		return;
	}

	echo htmlspecialchars(implode(', ', $groupNames), ENT_QUOTES, 'UTF-8');
};
?>
<div class="ambassadorhub-roles">
	<p><?php echo Text::_('COM_AMBASSADORHUB_ROLES_INTRO'); ?></p>

	<div class="card mb-4">
		<div class="card-body">
			<h3 class="card-title"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_HOW_ENFORCED_TITLE'); ?></h3>
			<ul class="mb-0">
				<li><?php echo Text::_('COM_AMBASSADORHUB_ROLES_HOW_ENFORCED_COORDINATOR'); ?></li>
				<li><?php echo Text::_('COM_AMBASSADORHUB_ROLES_HOW_ENFORCED_MANAGER'); ?></li>
				<li><?php echo Text::_('COM_AMBASSADORHUB_ROLES_HOW_ENFORCED_STUDENT'); ?></li>
			</ul>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header">
			<span class="badge bg-danger"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COORDINATORS_HEADING'); ?></span>
		</div>
		<div class="card-body">
			<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COORDINATORS_DESC'); ?></p>

			<?php if (empty($this->coordinators)) : ?>
				<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COORDINATORS_NONE'); ?></p>
			<?php else : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_NAME'); ?></th>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_USERNAME'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->coordinators as $person) : ?>
							<tr>
								<td><?php echo htmlspecialchars($person->name, ENT_QUOTES, 'UTF-8'); ?></td>
								<td><?php echo htmlspecialchars($person->username, ENT_QUOTES, 'UTF-8'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header">
			<span class="badge bg-warning text-dark"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_GROUP_MANAGERS_HEADING'); ?></span>
		</div>
		<div class="card-body">
			<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_GROUP_MANAGERS_DESC'); ?></p>

			<?php if (empty($this->groupManagers)) : ?>
				<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_GROUP_MANAGERS_NONE'); ?></p>
			<?php else : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_NAME'); ?></th>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_USERNAME'); ?></th>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_MANAGES'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->groupManagers as $person) : ?>
							<tr>
								<td><?php echo htmlspecialchars($person->name, ENT_QUOTES, 'UTF-8'); ?></td>
								<td><?php echo htmlspecialchars($person->username, ENT_QUOTES, 'UTF-8'); ?></td>
								<td><?php $renderGroupList($person->managed_groups, 'COM_AMBASSADORHUB_ROLES_MANAGES_NONE'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header">
			<span class="badge bg-secondary"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_STUDENTS_HEADING'); ?></span>
		</div>
		<div class="card-body">
			<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_STUDENTS_DESC'); ?></p>

			<?php if (empty($this->plainStudents)) : ?>
				<p class="text-muted"><?php echo Text::_('COM_AMBASSADORHUB_ROLES_STUDENTS_NONE'); ?></p>
			<?php else : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_NAME'); ?></th>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_USERNAME'); ?></th>
							<th><?php echo Text::_('COM_AMBASSADORHUB_ROLES_COLUMN_MEMBER_OF'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->plainStudents as $person) : ?>
							<tr>
								<td><?php echo htmlspecialchars($person->name, ENT_QUOTES, 'UTF-8'); ?></td>
								<td><?php echo htmlspecialchars($person->username, ENT_QUOTES, 'UTF-8'); ?></td>
								<td><?php $renderGroupList($person->member_of_groups, 'COM_AMBASSADORHUB_ROLES_MEMBER_OF_NONE'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
