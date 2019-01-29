<?php namespace ostark\AsyncQueue\Exceptions;

use Symfony\Component\Process\Process;

/**
 * Class RuntimeException
 *
 * @package ostark\AsyncQueue\Exceptions
 */
class RuntimeException extends \RuntimeException implements ProcessException
{
    protected $process;

    public function setProcess(Process $process)
    {
        $this->process = $process;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }
}
