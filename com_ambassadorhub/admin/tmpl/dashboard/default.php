<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Administrator\View\Dashboard\HtmlView  $this
 */

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
?>
<div class="ambassadorhub-dashboard">
	<p><?php echo Text::_('COM_AMBASSADORHUB_MANAGER_DASHBOARD_INTRO'); ?></p>
	<p>
		<?php echo Text::_('COM_AMBASSADORHUB_MANAGER_DASHBOARD_NOTE'); ?>
	</p>
</div>
