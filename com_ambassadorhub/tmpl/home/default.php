<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 *
 * @var  \SRH\Component\Ambassadorhub\Site\View\Home\HtmlView  $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use SRH\Component\Ambassadorhub\Site\Helper\AmbassadorhubHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$user = Factory::getApplication()->getIdentity();

echo AmbassadorhubHelper::renderTopbar('home');
?>
<div class="ambassadorhub ambassadorhub-page">
	<div class="ambassadorhub-home">
		<div class="ambassadorhub-home__hero">
			<span class="ambassadorhub-home__eyebrow ambassadorhub-mono"><?php echo Text::_('COM_AMBASSADORHUB_HOME_EYEBROW'); ?></span>
			<h1><?php echo Text::_('COM_AMBASSADORHUB_PAGE_HOME'); ?></h1>
			<p class="ambassadorhub-home__description"><?php echo Text::_('COM_AMBASSADORHUB_HOME_INTRO'); ?></p>

			<div class="ambassadorhub-home__actions">
				<?php if ($user->guest) : ?>
					<a class="ambassadorhub-btn" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=login'); ?>">
						<?php echo Text::_('COM_AMBASSADORHUB_HOME_CTA_GUEST'); ?>
					</a>
				<?php else : ?>
					<a class="ambassadorhub-btn" href="<?php echo Route::_('index.php?option=com_ambassadorhub&view=discover'); ?>">
						<?php echo Text::_('COM_AMBASSADORHUB_HOME_CTA_MEMBER'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
