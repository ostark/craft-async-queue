<?php namespace ostark\AsyncQueue;

use ostark\AsyncQueue\Events\QueueCommandEvent;
use ostark\AsyncQueue\Exceptions\PhpExecutableNotFound;
use Symfony\Component\Process\PhpExecutableFinder;
use yii\base\Component;

class QueueCommand extends Component
{
    public const DEFAULT_SCRIPT = "craft";

    public const DEFAULT_ARGS = "queue/run";

    public const EVENT_PREPARE_COMMAND = 'prepareCommand';

    protected string $scriptName;

    protected string $scriptArgs;

    /**
     * QueueCommand constructor.
     *
     * @param string|null $scriptName
     * @param string|null $scriptArgs
     */
    public function __construct(string $scriptName = null, string $scriptArgs = null, array $config = [])
    {
        parent::__construct($config);

        $this->scriptName = $scriptName ?: self::DEFAULT_SCRIPT;
        $this->scriptArgs = $scriptArgs ?: self::DEFAULT_ARGS;
    }

    /**
     * @param callable|null $wrapper
     *
     * @throws \ostark\AsyncQueue\Exceptions\PhpExecutableNotFound
     */
    public function getPreparedCommand(callable $wrapper = null): string
    {
        $finder = new PhpExecutableFinder();
        $php    = $finder->find(false);

        if (false === $php) {
            throw new PhpExecutableNotFound('Unable to find php executable.');
        }

        $commandLine = implode(" ", [$php, $this->scriptName, $this->scriptArgs]);

        return $this->decorate($commandLine);
    }


    /**
     * Wrapper
     */
    protected function decorate(string $commandLine): string
    {
        // Allow others to decorate the command
        $event = new QueueCommandEvent(['commandLine' => $commandLine]);
        $this->trigger(self::EVENT_PREPARE_COMMAND, $event);
        $commandLine = $event->commandLine;

        if (!$event->useDefaultDecoration) {
            return $commandLine;
        }

        // default decoration
        return "nice -n 15 {$commandLine} > /dev/null 2>&1 &";
    }

}
