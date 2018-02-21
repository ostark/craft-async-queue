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
use craft\queue\Command;
use craft\queue\Queue;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use yii\base\ActionEvent;
use yii\base\Event;
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

    const LOCK_NAME = 'async-queue-lock';
    const LOCK_TIMEOUT = 60;

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

        Event::on(Command::class, Command::EVENT_AFTER_ACTION, function(ActionEvent $event) {
            if ('run' === $event->action->id) {
                $this->setInProgress(false);
            }
        });

        // Listen to
        PushEvent::on(Queue::class, Queue::EVENT_AFTER_PUSH, function (PushEvent $event) {

            // Disable frontend queue runner
            Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

            if ($event->job instanceof BaseJob) {
                Craft::trace(
                    Craft::t(
                        'async-queue',
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
        if ($this->isInProgress()) {
            return false;
        }

        $cmd = $this->getCommand();
        $cwd = CRAFT_BASE_PATH;

        $process = new Process($cmd, $cwd);

        try {
            $process->run();
            $this->setInProgress(true);
        } catch (\Exception $e) {
            Craft::error($e, __METHOD__);
        }

        Craft::trace(
            Craft::t(
                'async-queue',
                'Job status: {status}. Exit code: {code}', ['status' => $process->getStatus(), 'code' => $process->getExitCodeText()]
            ),
            __METHOD__
        );

        return true;
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

    protected function isInProgress()
    {
        if (\Craft::$app->getCache()->get(self::LOCK_NAME)) {
             Craft::trace(
                 Craft::t(
                     'async-queue',
                     'Background process running'
                 ),
                 __METHOD__
             );
             return true;
         }

        return false;
    }

    protected function setInProgress($progress = true)
    {
        // set
        if ($progress) {
            return \Craft::$app->getCache()->set(self::LOCK_NAME, self::LOCK_NAME, self::LOCK_TIMEOUT);
        }

        // remove
        return \Craft::$app->getCache()->delete(self::LOCK_NAME);

    }


}
