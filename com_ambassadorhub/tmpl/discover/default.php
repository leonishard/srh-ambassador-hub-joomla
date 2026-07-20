<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Discover\HtmlView  $this
 */

use Joomla\CMS\Language\Text;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

echo AmbassadorhubHelper::renderTopbar('discover');
?>
<div class="ambassadorhub ambassadorhub-page">
	<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_DISCOVER'); ?></h1>
	<p class="ambassadorhub-caption"><?php echo Text::_('COM_AMBASSADORHUB_STUB_NOTE'); ?></p>
</div>
