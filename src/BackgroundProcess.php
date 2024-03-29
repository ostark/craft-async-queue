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
        $process = Process::fromShellCommandline($cmd);

        try {
            $process->start();
        } catch (\Symfony\Component\Process\Exception\RuntimeException $runtimeException) {
            $runtimeException = new RuntimeException($runtimeException->getMessage());
            $runtimeException->setProcess($process);
            throw $runtimeException;
        } catch (\Symfony\Component\Process\Exception\LogicException $logicException) {
            $logicException = new LogicException($logicException->getMessage());
            $logicException->setProcess($process);
            throw $logicException;
        }

        return $process;
    }
}
