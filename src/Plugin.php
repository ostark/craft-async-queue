<?php
/**
 * AsyncQueue plugin for Craft CMS 3.x
 *
 * A queue handler that moves queue execution to a non-blocking background process
 *
 * @link      http://www.fortrabbit.com
 * @copyright Copyright (c) 2017 Oliver Stark
 */

namespace ostark\asyncqueue;


use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\ResolveResourcePathEvent;
use craft\services\Resources;
use ostark\asyncqueue\Queue as AsyncQueue;
use yii\base\Event;


/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Oliver Stark
 * @package   AsyncQueue
 * @since     1.0.0
 *
 */
class Plugin extends BasePlugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * AsyncQueue::$plugin
     *
     * @var Plugin
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * AsyncQueue::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Replace default queue
        Craft::$app->setComponents([
            'queue' => AsyncQueue::class
        ]);

        Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;


        // Inject some jobs for demo purpose
        if (Craft::$app instanceof \craft\web\Application)
        {
            if (\Craft::$app->request->fullPath === 'services') {
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
                Craft::$app->getQueue()->push(new DemoJob());
            }
        }

    }

}
