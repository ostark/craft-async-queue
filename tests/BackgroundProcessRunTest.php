<?php

use ostark\AsyncQueue\BackgroundProcess;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ostark\AsyncQueue\BackgroundProcess
 */
class BackgroundProcessRunTest extends TestCase
{


    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unlink(TEST_FILE);
    }

    /**
     * @covers \ostark\AsyncQueue\BackgroundProcess::start
     */
    public function test_start_default_dummy_script_success(): void
    {
        $command   = new \ostark\AsyncQueue\QueueCommand('craft.php', 'queue/run');
        $bgProcess = new BackgroundProcess($command);
        $process   = $bgProcess->start();

        $process->wait();

        // give it some time to write the test file
        usleep(150000);


        $this->assertEquals(0, $process->getExitCode());
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals(\Symfony\Component\Process\Process::STATUS_TERMINATED, $process->getStatus());

        $this->assertFileExists(TEST_FILE);

        $content = json_decode(file_get_contents(TEST_FILE), true);
        $this->assertTrue(is_array($content), 'Unable to read and json_decode test file.');
        $this->assertStringContainsString('craft.php', $content['$argv'][0]);
        $this->assertStringContainsString('queue/run', $content['$argv'][1]);
        $this->assertGreaterThanOrEqual($content['timestamp'], time());
    }

    /**
     * @covers \ostark\AsyncQueue\BackgroundProcess::start
     */
    public function test_process_does_not_block(): void
    {
        $command   = new \ostark\AsyncQueue\QueueCommand('craft.php', '--sleep');
        $bgProcess = new BackgroundProcess($command);
        $process   = $bgProcess->start();

        // give it some time to write the test file
        usleep(150000);

        $this->assertFileExists(TEST_FILE);

        $content = json_decode(file_get_contents(TEST_FILE), true);
        $this->assertGreaterThanOrEqual($content['timestamp'], time());

    }
}
