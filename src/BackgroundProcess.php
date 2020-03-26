<?php namespace ostark\AsyncQueue;

use ostark\AsyncQueue\Exceptions\LogicException;
use ostark\AsyncQueue\Exceptions\RuntimeException;
use Symfony\Component\Process\Process;

class BackgroundProcess
{

    /**
     * @var \ostark\AsyncQueue\QueueCommand
     */
    protected $command;

    /**
     * BackgroundProcess constructor.
     *
     * @param \ostark\AsyncQueue\QueueCommand|null $command
     */
    public function __construct(QueueCommand $command = null)
    {
        $this->command = $command ?: new QueueCommand();
    }


    /**
     * Runs craft queue/run in the background
     *
     * @return \Symfony\Component\Process\Process
     *
     * @throws \ostark\AsyncQueue\Exceptions\PhpExecutableNotFound
     * @throws \ostark\AsyncQueue\Exceptions\RuntimeException
     * @throws \ostark\AsyncQueue\Exceptions\LogicException
     */
    public function start()
    {
        $cmd = $this->command->getPreparedCommand();
        $cwd = realpath(CRAFT_BASE_PATH);

        $process = Process::fromShellCommandline($cmd, $cwd);

        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
            $e = new RuntimeException($e->getMessage());
            $e->setProcess($process);
            throw $e;
        } catch (\Symfony\Component\Process\Exception\LogicException $e) {
            $e = new LogicException($e->getMessage());
            $e->setProcess($process);
            throw $e;
        }

        return $process;
    }
}
