<?php
/**
 * Created by PhpStorm.
 * User: os
 * Date: 22.08.17
 * Time: 15:37
 */

namespace ostark\asyncqueue;

use craft\queue\Queue as CraftDefaultQueue;
use Symfony\Component\Process\Process;

class Queue extends CraftDefaultQueue
{
    /**
     * @var int default time to reserve a job
     */
    public $ttr = 0;

    /**
     * @var string|null The description of the job being pushed into the queue
     */
    private $_jobDescription;

    /**
     * @var bool
     */
    protected $backgroundProcessIsRunning = false;

    /**
     * @param mixed|\yii\queue\Job $job
     *
     * @return null|string
     */
    public function push($job)
    {

        // Capture the description so pushMessage() can access it
        if ($job instanceof JobInterface) {
            $this->_jobDescription = $job->getDescription();
        } else {
            $this->_jobDescription = null;
        }
        if (($id = parent::push($job)) === null) {
            return null;
        }

        // Run one 'queue/run' process at a time
        if (!$this->backgroundProcessIsRunning) {
            $this->startBackgroundProcess($id);
            $this->backgroundProcessIsRunning = true;
        }

        return $id;
    }


    /**
     * @param string $id
     */
    protected function startBackgroundProcess(string $id)
    {
        $command = $this->getCommand($id);
        $cwd     = CRAFT_BASE_PATH;

        $process = new Process($command, $cwd);
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
        $cmd    = '%s craft queue/run -v';
        $cmd    = $this->getBackgroundCommand($cmd);
        $binary = $this->getPhpBinary();

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

    /**
     * @return string
     */
    protected function getPhpBinary(): string
    {
        return getenv('PATH_PHP_BINARY') ?? '/usr/bin/php';
    }

}
