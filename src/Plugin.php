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
            'async_handler' => QueueHandler::class,
            'async_pool'    => ProcessPool::class,
        ]);

        // Tell yii about the concrete implementation of CacheInterface
        Craft::$container->set(CacheInterface::class, Craft::$app->getCache());

        // Register event handlers
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, [$this, 'runQueueInBackground']);
        Event::on(Command::class, Command::EVENT_AFTER_ACTION, [$this, 'freeProcessPool']);
        Utility::setup($this);

    }


    // ServiceLocators
    // =========================================================================

    /**
     * @return \ostark\AsyncQueue\QueueHandler
     */
    public function getHandler(): QueueHandler
    {
        return $this->get('async_handler');
    }

    /**
     * @return \ostark\AsyncQueue\ProcessPool
     */
    public function getPool(): ProcessPool
    {
        return $this->get('async_pool');
    }

    // EventHandlers
    // =========================================================================

    /**
     * @param \yii\queue\PushEvent $event
     */
    public function runQueueInBackground(PushEvent $event)
    {
        // Disable frontend queue runner
        Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

        $context = ($event->job instanceof JobInterface)
            ? $event->job->getDescription()
            : 'Not instanceof craft\queue\JobInterface';

        // Run queue in the background
        if ($this->getPool()->canIUse($context)) {
            try {
                $this->getHandler()->startBackgroundProcess();
                $this->getPool()->increment($context);
                $handled = true;
            } catch (PhpExecutableNotFound $e) {
                Craft::debug(
                    'QueueHandler::startBackgroundProcess() (PhpExecutableNotFound)',
                    'async-queue'
                );
            } catch (RuntimeException | LogicException $e) {
                Craft::debug(
                    Craft::t(
                        'async-queue',
                        'QueueHandler::startBackgroundProcess() (Job status: {status}. Exit code: {code})', [
                            'status' => $e->getProcess()->getStatus(),
                            'code'   => $e->getProcess()->getExitCodeText()
                        ]
                    ),
                    'async-queue'
                );
            }
        }

        // Log what's going on
        $this->logPushEvent($event, $handled ?? false);
    }

    /**
     * @param \yii\base\ActionEvent $event
     */
    public function freeProcessPool(ActionEvent $event)
    {
        if ('run' === $event->action->id) {
            $this->getPool()->decrement(Command::class . '::run() ' . Command::EVENT_AFTER_ACTION);
        }
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

    /**
     * @param \yii\queue\PushEvent $event
     * @param bool                 $handled
     */
    protected function logPushEvent(PushEvent $event, $handled = false)
    {
        if (!YII_DEBUG) {
            return;
        }
        if ($event->job instanceof BaseJob) {
            Craft::debug(
                Craft::t(
                    'async-queue',
                    'New PushEvent for {job} job - ({handled})', [
                        'job'     => $event->job->getDescription(),
                        'handled' => $handled ? 'handled' : 'skipped'
                    ]
                ),
                'async-queue'
            );
        }
    }
}
