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

    /**
     * @coversNothing
     */
    public function testStartBackgroundProcess()
    {
        $this->handler->startBackgroundProcess();
        $this->assertTrue(true);
    }
}
