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
     * @var string
     */
    public $phpBinary = 'php';

    /**
     * @var string|null The description of the job being pushed into the queue
     */
    private $_jobDescription;

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

        $this->startBackgroundProcess($id);

        return $id;
    }


    protected function startBackgroundProcess(string $id)
    {

        $command = $this->getCommand($id);
        $cwd     = CRAFT_BASE_PATH;

        $process = new Process($command, $cwd);
        $process->run();
        die($process->getOutput());

    }

    protected function getCommand($id)
    {
        $cmd     = '%s craft queue/run %s %d %d';
        $cmd     = $this->getBackgroundCommand($cmd);
        $binary  = $this->phpBinary;
        $ttr     = 30;
        $attempt = 1;

        return sprintf($cmd, $binary, $id, $ttr, $attempt);

    }

    /**
     * @param string $cmd
     *
     * @return string
     */
    protected function getBackgroundCommand(string $cmd)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return 'start /B ' . $cmd . ' > NUL';
        } else {
            return $cmd . ' > /dev/null 2>&1 &';
        }
    }


}
