<?php namespace ostark\AsyncQueue\Handlers;

use Craft;
use craft\queue\BaseJob;
use craft\queue\JobInterface;
use ostark\AsyncQueue\Exceptions\LogicException;
use ostark\AsyncQueue\Exceptions\PhpExecutableNotFound;
use ostark\AsyncQueue\Exceptions\RuntimeException;
use ostark\AsyncQueue\Plugin;
use yii\queue\PushEvent;

class BackgroundQueueHandler
{
    /**
     * @var \ostark\AsyncQueue\Plugin
     */
    protected $plugin;

    /**
     * BackgroundQueue constructor.
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function __invoke(PushEvent $event): void
    {

        $context = ($event->job instanceof JobInterface)
            ? $event->job->getDescription()
            : 'Not instanceof craft\queue\JobInterface';

        // Run queue in the background
        if ($this->plugin->getRateLimiter()->canIUse($context)) {

            try {
                $process = $this->plugin->getProcess()->start();
                $this->plugin->getRateLimiter()->increment();
                $handled = true;

                $process->wait();

            } catch (PhpExecutableNotFound) {
                Craft::error(
                    'QueueHandler::startBackgroundProcess() (PhpExecutableNotFound)',
                    'async-queue'
                );
            } catch (RuntimeException | LogicException $e) {
                Craft::error(
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


    protected function logPushEvent(PushEvent $event, bool $handled = false): void
    {
        if (!YII_DEBUG) {
            return;
        }

        if ($event->job instanceof BaseJob) {
            Craft::info(
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
