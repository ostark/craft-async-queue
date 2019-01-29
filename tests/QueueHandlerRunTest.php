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

    public function tearDown()
    {
        parent::tearDown();
        unlink(TEST_FILE);
    }

    /**
     * @covers \ostark\AsyncQueue\QueueHandler::startBackgroundProcess
     */
    public function test_startBackgroundProcess_default_dummy_script_success()
    {
        $command = new \ostark\AsyncQueue\QueueCommand('craft.php', 'queue/run');
        $handler = new QueueHandler($command);
        $process = $handler->startBackgroundProcess();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals(\Symfony\Component\Process\Process::STATUS_TERMINATED, $process->getStatus());

        // Wait 0.25 seconds
        usleep(250000);

        $content = json_decode(file_get_contents(TEST_FILE), true);

        $this->assertTrue(is_array($content), 'Unable to read and json_decode test file.');
        $this->assertContains('craft.php', $content['$argv']);
        $this->assertContains('queue/run', $content['$argv']);
        $this->assertGreaterThanOrEqual($content['timestamp'], time());


    }


}