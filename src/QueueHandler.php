<?php namespace ostark\AsyncQueue;

use Craft;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * QueueHandler
 *
 * @author    Oliver Stark
 * @package   AsyncQueue
 * @since     1.3.0
 *
 */
class QueueHandler
{

    /**
     * Runs craft queue/run in the background
     */
    public function startBackgroundProcess()
    {
        $cmd = $this->getCommand();
        $cwd = CRAFT_BASE_PATH;

        $process = new Process($cmd, $cwd);

        try {
            $process->run();
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        Craft::debug(
            Craft::t(
                'async-queue',
                'Job status: {status}. Exit code: {code}', ['status' => $process->getStatus(), 'code' => $process->getExitCodeText()]
            ),
            __METHOD__
        );

    }


    /**
     * Construct queue command
     *
     * @return string
     * @throws \Exception
     */
    protected function getCommand()
    {
        $executableFinder = new PhpExecutableFinder();
        if (false === $php = $executableFinder->find(false)) {
            throw new \Exception('Unable to find php binary.');
        }

        $cmd = array_merge(
            ['"' . $php . '"'],
            $executableFinder->findArguments(),
            ['craft', 'queue/run -v']
        );

        return $this->getBackgroundCommand(implode(' ', $cmd));
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
