<?php

use ostark\AsyncQueue\QueueHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ostark\AsyncQueue\QueueHandler
 */
class QueueHandlerRunTest extends TestCase
{


    public function setUp()
    {
        parent::setUp();

    }


    /**
     * @covers \ostark\AsyncQueue\QueueHandler::startBackgroundProcess
     */
    public function test_startBackgroundProcess_default_dummy_script_success()
    {
        $command = new \ostark\AsyncQueue\QueueCommand('craft.php', 'queue/run --error');
        $handler = new QueueHandler($command);
        $process = $handler->startBackgroundProcess();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals(\Symfony\Component\Process\Process::STATUS_TERMINATED, $process->getStatus());
    }


}
