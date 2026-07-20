<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Home\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();
?>
<div class="ambassadorhub ambassadorhub-home">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_HOME'); ?></h1>
	<p><?php echo Text::_('COM_AMBASSADORHUB_HOME_INTRO'); ?></p>

	<?php if ($user->guest) : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_HOME_CTA_GUEST'); ?></p>
	<?php else : ?>
		<p><?php echo Text::_('COM_AMBASSADORHUB_HOME_CTA_MEMBER'); ?></p>
	<?php endif; ?>
</div>
