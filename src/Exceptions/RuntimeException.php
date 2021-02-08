<?php namespace ostark\AsyncQueue\Exceptions;

use Symfony\Component\Process\Process;

class RuntimeException extends \RuntimeException implements ProcessException
{

    /**
     * @var Process
     */
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
