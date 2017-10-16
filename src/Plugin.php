<?php
/**
 * AsyncQueue plugin for Craft CMS 3.x
 *
 * A queue handler that moves queue execution to a non-blocking background process
 *
 * @link      http://www.fortrabbit.com
 * @copyright Copyright (c) 2017 Oliver Stark
 */

namespace ostark\AsyncQueue;


use Craft;
use craft\base\Plugin as BasePlugin;
use craft\queue\Queue;
use Symfony\Component\Process\Process;
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

        // Listen to
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, function (PushEvent $event) {

            // Prevent frontend queue runner
            Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

            // Run queue in the background
            $this->startBackgroundProcess();
        });

    }


    /**
     * Runs craft queue/run in the background
     */
    protected function startBackgroundProcess()
    {
        $process = new Process($this->getCommand(), CRAFT_BASE_PATH);
        $process->run();
    }


    /**
     * Construct queue command
     *
     * @return string
     */
    protected function getCommand(): string
    {
        $cmd    = "%s craft queue/run";
        $cmd    = $this->getBackgroundCommand($cmd);
        $binary = getenv('PATH_PHP_BINARY') ?? '/usr/bin/php';

        return sprintf($cmd, $binary);
    }


    /**
     * Extend command with background syntax
     *
     * @param string $cmd
     *
     * @return string
     */
    protected function getBackgroundCommand(string $cmd): string
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return 'start /B ' . $cmd . ' > NUL';
        } else {
            return $cmd . ' > /dev/null 2>&1 &';
        }
    }

}
