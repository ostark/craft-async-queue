<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers \ostark\AsyncQueue\QueueCommand
 */
class QueueCommandTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // Unset envar
        putenv("PHP_BINARY=");
    }


    /**
     * @covers \ostark\AsyncQueue\QueueCommand::getPreparedCommand
     */
    public function test_getPreparedCommand_with_defaults()
    {
        $command = new \ostark\AsyncQueue\QueueCommand();
        $prepared = $command->getPreparedCommand();

        $this->assertContains('php', $prepared);
        $this->assertContains(\ostark\AsyncQueue\QueueCommand::DEFAULT_SCRIPT, $prepared);
        $this->assertContains(\ostark\AsyncQueue\QueueCommand::DEFAULT_ARGS, $prepared);
    }

    /**
     * @covers \ostark\AsyncQueue\QueueCommand::getPreparedCommand
     */
    public function test_getPreparedCommand_wrong_PHP_BINARY()
    {
        putenv("PHP_BINARY=php.txt");

        $this->expectException(\ostark\AsyncQueue\Exceptions\PhpExecutableNotFound::class);

        $command = new \ostark\AsyncQueue\QueueCommand();
        $command->getPreparedCommand();
    }

    /**
     * @covers \ostark\AsyncQueue\QueueCommand::getPreparedCommand
     */
    public function test_getPreparedCommand_alternative_decoration()
    {
        // Add handler
        \yii\base\Event::on(
            \ostark\AsyncQueue\QueueCommand::class,
            \ostark\AsyncQueue\QueueCommand::EVENT_PREPARE_COMMAND,
            function(\ostark\AsyncQueue\Events\QueueCommandEvent $event) {
                $event->useDefaultDecoration = false;
                $event->commandLine = "BEFORE {$event->commandLine} AFTER";
            }
        );

        $command = new \ostark\AsyncQueue\QueueCommand();
        $prepared = $command->getPreparedCommand();

        $this->assertStringStartsWith('BEFORE', $prepared);
        $this->assertStringEndsWith('AFTER', $prepared);

        // Remove handler
        \yii\base\Event::off(
            \ostark\AsyncQueue\QueueCommand::class,
            \ostark\AsyncQueue\QueueCommand::EVENT_PREPARE_COMMAND
        );
    }


    /**
     * @covers \ostark\AsyncQueue\QueueCommand::getPreparedCommand
     */
    public function test_getPreparedCommand_alternative_script_args()
    {
        $script = 'foo.php';
        $args = '--bar';

        $command = new \ostark\AsyncQueue\QueueCommand($script, $args);
        $prepared = $command->getPreparedCommand();

        $this->assertContains($script, $prepared);
        $this->assertContains($args, $prepared);
    }

}
