<?php

use ostark\AsyncQueue\QueueHandler;
use PHPUnit\Framework\TestCase;

class QueueHandlerTest extends TestCase
{

    /**
     * @var QueueHandler
     */
    public $handler;

    public function setUp()
    {
        parent::setUp();
        $this->handler = new QueueHandler();

    }

    public function testGetCommand()
    {
        $parts = $this->handler->getCommand();

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->assertContains('start /B', $parts, "Command does not contain 'start /B'");
        } else {
            $this->assertContains('nice', $parts, "Command does not contain 'nice'");
        }

        $this->assertContains('craft', $parts, "Command does not contain 'craft'");

    }

    /**
     * @coversNothing
     */
    public function testStartBackgroundProcess()
    {
        $this->handler->startBackgroundProcess();
        $this->assertTrue(true);
    }
}
