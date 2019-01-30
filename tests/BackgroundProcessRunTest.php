<?php

use ostark\AsyncQueue\BackgroundProcess;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ostark\AsyncQueue\BackgroundProcess
 */
class BackgroundProcessRunTest extends TestCase
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
     * @covers \ostark\AsyncQueue\BackgroundProcess::start
     */
    public function test_start_default_dummy_script_success()
    {
        $command = new \ostark\AsyncQueue\QueueCommand('craft.php', 'queue/run');
        $bgProcess = new BackgroundProcess($command);
        $process = $bgProcess->start();

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
