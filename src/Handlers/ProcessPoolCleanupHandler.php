<?php namespace ostark\AsyncQueue\Handlers;

use craft\queue\Command;
use ostark\AsyncQueue\Plugin;
use yii\base\ActionEvent;

/**
 * Class ProcessPoolCleanupHandler
 *
 * @package ostark\AsyncQueue\Handlers
 */
class ProcessPoolCleanupHandler
{
    /**
     * @var \ostark\AsyncQueue\Plugin
     */
    protected $plugin;

    /**
     * BackgroundQueue constructor.
     *
     * @param \ostark\AsyncQueue\Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param \yii\base\ActionEvent $event
     */
    public function __invoke(ActionEvent $event)
    {
        if ('run' === $event->action->id) {
            $this->plugin->getPool()->decrement(Command::class . '::run() ' . Command::EVENT_AFTER_ACTION);
        }
    }
}
