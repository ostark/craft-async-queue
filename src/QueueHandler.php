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
                'QueueHandler::startBackgroundProcess() (Job status: {status}. Exit code: {code})', [
                    'status' => $process->getStatus(),
                    'code'   => $process->getExitCodeText()
                ]
            ),
            'async-queue'
        );

    }


    /**
     * Construct queue command
     *
     * @return array
     * @throws \Exception
     */
    public function getCommand()
    {
        $finder = new PhpExecutableFinder();
        $php    = $finder->find(false);

        if (false === $php) {
            throw new \Exception('Unable to find php binary.');
        }

        $php = $this->preparePath($php);

        return $this->prepareCommandForBackgroundExec([$php, 'craft', 'queue/run', '-v']);
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
     * @param array $parts
     *
     * @return array
     */
    protected function prepareCommandForBackgroundExec(array $parts): array
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            array_unshift($parts, "start /B");
            array_push($parts, "> NUL");
            return $parts;
        }

        array_unshift($parts, "nice");
        array_push($parts, " > /dev/null 2>&1 &");

        return $parts;
    }

}
