<?php

/**
 * @package     SRH.Site
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Site\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_ambassadorhub (site).
 *
 * @since  0.1.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Dispatch a controller task.
     *
     * @return  void
     *
     * @since   0.1.0
     */
    public function dispatch()
    {
        $this->app->getDocument()->getWebAssetManager()
            ->useStyle('com_ambassadorhub.frontend')
            ->useScript('com_ambassadorhub.theme-toggle');

        parent::dispatch();
    }
}
