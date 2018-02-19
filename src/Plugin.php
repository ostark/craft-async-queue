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
use craft\queue\BaseJob;
use craft\queue\Queue;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
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
     * @var bool mutex
     */
    protected $inProgress = false;

    /**
     * Init plugin
     */
    public function init()
    {
        parent::init();

        // Listen to
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, function (PushEvent $event) {

            // Disable frontend queue runner
            Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

            if ($event->job instanceof BaseJob) {
                Craft::trace(
                    Craft::t(
                        'craft-async-queue',
                        'Handling PushEvent for {job} job', ['job' => $event->job->getDescription()]
                    ),
                    __METHOD__
                );
            }

            // Run queue in the background
            $this->startBackgroundProcess();
        });

    }


    /**
     * Runs craft queue/run in the background
     *
     * @return bool
     */
    protected function startBackgroundProcess()
    {
        if ($this->inProgress) {

            Craft::trace(
                Craft::t(
                    'craft-async-queue',
                    'Background process running'
                ),
                __METHOD__
            );

            return false;
        }

        $cmd = $this->getCommand();
        $cwd = CRAFT_BASE_PATH;

        $process = new Process($cmd, $cwd);

        try {
            $process->run();
            $this->inProgress = true;
        } catch (\Exception $e) {
            Craft::error($e, __METHOD__);
        }

        Craft::trace(
            Craft::t(
                'craft-async-queue',
                'Job status: {status}. Exit code: {code}', ['status' => $process->getStatus(), 'code' => $process->getExitCodeText()]
            ),
            __METHOD__
        );

        return $this->inProgress;
    }


    /**
     * Construct queue command
     *
     * @return string
     */
    protected function getCommand()
    {
        $executableFinder = new PhpExecutableFinder();
        if (false === $php = $executableFinder->find(false)) {
            return null;
        } else {
            $cmd = array_merge(
                [$php],
                $executableFinder->findArguments(),
                ['craft', 'queue/run -v']
            );

            return $this->getBackgroundCommand(implode(' ', $cmd));
        }
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
            return 'nice ' . $cmd . ' > /dev/null 2>&1 &';
        }
    }


}
