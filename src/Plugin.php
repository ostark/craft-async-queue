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
use Symfony\Component\Process\Process;
use yii\queue\PushEvent;


/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
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
            $this->startBackgroundProcess((string)$event->id);
        });

    }

    /**
     * @param string $id
     */
    protected function startBackgroundProcess(string $id)
    {
        $process = new Process($this->getCommand($id), CRAFT_BASE_PATH);
        $process->run();
    }

    /**
     * Construct queue command
     *
     * @param string $id
     *
     * @return string
     */
    protected function getCommand(string $id): string
    {
        $cmd    = "%s craft queue/run --verbose=1";
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
