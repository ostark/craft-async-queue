<?php namespace ostark\AsyncQueue\Exceptions;

use Symfony\Component\Process\Process;

interface ProcessException
{
    public function setProcess(Process $process);

    public function getProcess(): Process;

}
