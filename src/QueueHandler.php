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
        file_put_contents('/var/www/app/storage/logs/async-queue.log', $cmd . "\n", FILE_APPEND);
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
        $finder = new PhpExecutableFinder();
        $php    = $finder->find(false);

        if (false === $php) {
            throw new \Exception('Unable to find php binary.');
        }

        $php = $this->preparePath($php);

        return $this->getBackgroundCommand(implode(' ', [$php, 'craft', 'queue/run', '-v']));
    }


    /**
     * Quote path for windows or keep it as it is
     *
     * @param string $path
     *
     * @return string
     */
    protected function preparePath($path)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return '"' . $path . '"';
        }

        return $path;

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
        }

        return 'nice ' . $cmd . ' > /dev/null 2>&1 &';

    }

}
