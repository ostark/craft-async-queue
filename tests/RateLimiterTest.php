<?php

use ostark\AsyncQueue\BackgroundProcess;
use PHPUnit\Framework\TestCase;
use yii\queue\PushEvent;

/**
 * @covers \ostark\AsyncQueue\RateLimiter
 * @covers \ostark\AsyncQueue\Handlers\BackgroundQueueHandler
 */
class RateLimiterTest extends TestCase
{
    public ostark\AsyncQueue\Plugin $plugin;

    public function setUp(): void
    {
        parent::setUp();

        $dummyCommand = new \ostark\AsyncQueue\QueueCommand('craft.php', '--sleep');
        $this->plugin = new \ostark\AsyncQueue\Plugin('async-queue', null, []);
        $this->plugin->set('async_process', new BackgroundProcess($dummyCommand));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(TEST_FILE);
    }


    public function test_can_setup_handler_and_invoke_event(): void
    {
        $this->plugin = new \ostark\AsyncQueue\Plugin('async-queue', null, []);
        $handler = new \ostark\AsyncQueue\Handlers\BackgroundQueueHandler($this->plugin);

        $handler->__invoke(new PushEvent());

        $this->assertSame(1, $this->plugin->getRateLimiter()->getInternalCount());
    }

    public function test_respect_the_limit_when_adding_multiple_jobs_in_one_request(): void
    {
        $handler = new \ostark\AsyncQueue\Handlers\BackgroundQueueHandler($this->plugin);

        $handler->__invoke(new PushEvent());
        $handler->__invoke(new PushEvent());
        $handler->__invoke(new PushEvent());
        $handler->__invoke(new PushEvent());

        $this->assertSame(
            $this->plugin->getRateLimiter()->maxItems,
            $this->plugin->getRateLimiter()->getInternalCount()
        );
    }

    public function test_stop_spawning_processes_when_too_many_jobs_are_reserved(): void
    {
        // Fake the reserved count
        $queue = new class extends \craft\queue\Queue {
            public function getTotalReserved(): int
            {
                return 5;
            }
        };

        $this->plugin->set('async_rate_limiter', new \ostark\AsyncQueue\RateLimiter($queue, $this->plugin->getSettings()));
        $handler = new \ostark\AsyncQueue\Handlers\BackgroundQueueHandler($this->plugin);

        $handler->__invoke(new PushEvent());

        $this->assertSame(0, $this->plugin->getRateLimiter()->getInternalCount());
    }


    public function test_stop_spawning_processes_when_counting_jobs_failed(): void
    {
        // Fake the reserved count
        $queue = new class extends \craft\queue\Queue {
            public function getTotalReserved(): int
            {
                throw new \Exception('Unable to count jobs');
            }
        };

        $this->plugin->set('async_rate_limiter', new \ostark\AsyncQueue\RateLimiter($queue, $this->plugin->getSettings()));
        $handler = new \ostark\AsyncQueue\Handlers\BackgroundQueueHandler($this->plugin);

        $handler->__invoke(new PushEvent());

        $this->assertFalse($this->plugin->getRateLimiter()->canIUse());
    }
}
