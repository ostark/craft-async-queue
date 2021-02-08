<?php namespace ostark\AsyncQueue\TestUtility;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Utilities;
use craft\web\View;
use ostark\AsyncQueue\Plugin;
use ostark\AsyncQueue\QueueCommand;
use yii\base\Event;

class Utility extends \craft\base\Utility
{

    /**
     * Returns the utility’s unique identifier.
     *
     * @return string
     */
    public static function id(): string
    {
        return 'async-queue-test';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Async Queue Test';
    }


    public static function iconPath(): string
    {
        return __DIR__ . '/icon.svg';
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        /** @var \craft\queue\Queue $queue */
        $queue  = \Craft::$app->getQueue();
        $plugin = Plugin::getInstance();

        $checks = [
            'Queue runner concurrency' => $plugin->getSettings()->concurrency,
            'Reserved jobs'            => $queue->getTotalReserved(),
            'Jobs waiting'             => $queue->getTotalWaiting(),
            'Jobs failed'              => $queue->getTotalFailed(),
            'Command line'             => (new QueueCommand())->getPreparedCommand()
        ];


        return \Craft::$app->getView()->renderTemplate('async-queue/template', ['checks' => $checks]);
    }


    public static function setup(Plugin $plugin): void
    {
        if (!\Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        // Register the Utility
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Utility::class;
        });

        // Tune the template path
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) use ($plugin) {
            $e->roots[$plugin->getHandle()] = __DIR__;
        });

        // Tune the controller mapping
        $plugin->controllerMap['test'] = [
            'class' => Controller::class,
        ];

    }
}
