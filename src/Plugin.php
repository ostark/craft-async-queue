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
use craft\events\RegisterTemplateRootsEvent;
use craft\queue\BaseJob;
use craft\queue\Command;
use craft\queue\JobInterface;
use craft\queue\Queue;
use ostark\AsyncQueue\Exceptions\LogicException;
use ostark\AsyncQueue\Exceptions\PhpExecutableNotFound;
use ostark\AsyncQueue\Exceptions\RuntimeException;
use ostark\AsyncQueue\Handlers\BackgroundQueueHandler;
use ostark\AsyncQueue\Handlers\ProcessPoolCleanupHandler;
use ostark\AsyncQueue\TestUtility\Utility;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\caching\CacheInterface;
use yii\queue\PushEvent;


/**
 * AsyncQueue
 *
 * @author    Oliver Stark
 * @package   AsyncQueue
 * @since     1.0.0
 *
 * @method \ostark\AsyncQueue\Settings getSettings()
 *
 */
class Plugin extends BasePlugin
{
    /**
     * Init plugin
     */
    public function init()
    {
        parent::init();

        // Don't do anything if not enabled
        if (!$this->getSettings()->enabled) {
            return;
        }

        // Register plugin components
        $this->setComponents([
            'async_process' => BackgroundProcess::class,
            'async_pool'    => ProcessPool::class,
        ]);

        // Tell yii about the concrete implementation of CacheInterface
        Craft::$container->set(CacheInterface::class, Craft::$app->getCache());

        // Register event handlers
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, new BackgroundQueueHandler($this));
        Event::on(Command::class, Command::EVENT_AFTER_ACTION, new ProcessPoolCleanupHandler($this));

        // Register CP Utility
        Utility::setup($this);

    }


    // ServiceLocators
    // =========================================================================

    /**
     * @return \ostark\AsyncQueue\BackgroundProcess
     */
    public function getProcess(): BackgroundProcess
    {
        return $this->get('async_process');
    }

    /**
     * @return \ostark\AsyncQueue\ProcessPool
     */
    public function getPool(): ProcessPool
    {
        return $this->get('async_pool');
    }


    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

}
