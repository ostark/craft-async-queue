<?php
/**
 * AsyncQueue plugin for Craft CMS 3.x
 *
 * A queue handler that moves queue execution to a non-blocking background process
 *
 * @link      https://www.fortrabbit.com
 * @copyright Copyright (c) 2017 Oliver Stark
 */

namespace ostark\AsyncQueue;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\queue\Queue;
use ostark\AsyncQueue\Handlers\BackgroundQueueHandler;
use ostark\AsyncQueue\TestUtility\Utility;
use yii\queue\PushEvent;


/**
 * Main plugin class
 *
 * @property \ostark\AsyncQueue\BackgroundProcess $process
 * @property \ostark\AsyncQueue\RateLimiter       $rateLimiter
 * @method \ostark\AsyncQueue\Settings getSettings()
 */
class Plugin extends BasePlugin
{
    /**
     * Init plugin
     */
    public function init(): void
    {
        parent::init();

        // Don't do anything if not enabled
        if (!$this->getSettings()->enabled) {
            return;
        }

        // Disable the 'web queue handler', just in case
        Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

        // Register plugin components
        $this->setComponents([
            'async_process'      => BackgroundProcess::class,
            'async_rate_limiter' => RateLimiter::class
        ]);

        // Register event handlers
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, new BackgroundQueueHandler($this));

        // Register CP Utility
        Utility::setup($this);

    }


    // ServiceLocators
    // =========================================================================

    public function getProcess(): BackgroundProcess
    {
        return $this->get('async_process');
    }

    public function getRateLimiter(): RateLimiter
    {
        return $this->get('async_rate_limiter');
    }


    /**
     * Creates and returns the model used to store the plugin’s settings.
     */
    protected function createSettingsModel() : Settings
    {
        return new Settings();
    }

}
