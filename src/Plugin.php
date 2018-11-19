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
use craft\queue\BaseJob;
use craft\queue\Command;
use craft\queue\Queue;
use yii\base\Controller;
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
 */
class Plugin extends BasePlugin
{
    /**
     * Init plugin
     */
    public function init()
    {
        parent::init();

        // Register plugin components
        $this->setComponents([
            'async_handler' => QueueHandler::class,
            'async_pool'    => ProcessPool::class,
        ]);

        // Tell yii about the concrete implementation of CacheInterface
        Craft::$container->set(CacheInterface::class, Craft::$app->getCache());

        // EventHandlers
        // =========================================================================

        PushEvent::on(
            Queue::class,
            Queue::EVENT_AFTER_PUSH,
            function (PushEvent $event) {
                // Disable frontend queue runner
                Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

                // Run queue in the background
                if ($this->getPool()->canIUse()) {
                    $this->getHandler()->startBackgroundProcess();
                    $this->getPool()->increment();
                    $handled = true;
                }

                // Log what's going on
                $this->logPushEvent($event, $handled ?? false);
            }
        );

        Event::on(
            Command::class,
            Command::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                if ('run' === $event->action->id) {
                    file_put_contents('/var/www/app/storage/logs/async-queue.log', "COMMAND::EVENT_AFTER_ACTION TRIGGERED!!!\n", FILE_APPEND);
                    $this->getPool()->decrement();
                }
            }
        );

        Event::on(
            Controller::class,
            Controller::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                if ('run' === $event->action->id) {
                    file_put_contents('/var/www/app/storage/logs/async-queue.log', "CONTROLLER::EVENT_AFTER_ACTION TRIGGERED!!!\n", FILE_APPEND);
                    $this->getPool()->decrement();
                }
            }
        );
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


    /**
     * @param \yii\queue\PushEvent $event
     * @param bool                 $handled
     */
    protected function logPushEvent(PushEvent $event, $handled = false)
    {
        if ($event->job instanceof BaseJob) {
            Craft::trace(
                Craft::t(
                    'async-queue',
                    'New PushEvent for {job} job - ({handled})', [
                        'job'     => $event->job->getDescription(),
                        'handled' => $handled ? 'handled' : 'skipped'
                    ]
                ),
                __METHOD__
            );
        }
    }

}
