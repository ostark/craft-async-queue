<?php namespace ostark\AsyncQueue\Exceptions;

use Symfony\Component\Process\Process;

class LogicException extends \LogicException implements ProcessException
{
    protected $process;

    public function setProcess(Process $process): void
    {
        $this->process = $process;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }
}
