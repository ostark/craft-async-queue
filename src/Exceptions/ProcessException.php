<?php namespace ostark\AsyncQueue\Exceptions;

use Symfony\Component\Process\Process;

/**
 * Interface ProcessException
 *
 * @package ostark\AsyncQueue\Exceptions
 */
interface ProcessException
{
    public function setProcess(Process $process);

    public function getProcess(): Process;

}
