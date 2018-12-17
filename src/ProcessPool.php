<?php namespace ostark\AsyncQueue;

use Craft;
use yii\caching\CacheInterface;

/**
 * ProcessPool
 *
 * @author    Oliver Stark
 * @package   AsyncQueue
 * @since     1.3.0
 *
 */
class ProcessPool
{

    const CACHE_KEY = 'async-queue-pool';

    /**
     * @var integer
     */
    public $maxItems;

    /**
     * @var integer
     */
    public $lifetime;

    /**
     * @var \yii\caching\CacheInterface
     */
    public $cache;


    /**
     * QueuePool constructor.
     *
     * @param \ostark\AsyncQueue\Settings $settings
     * @param \yii\caching\CacheInterface $cache
     */
    public function __construct(Settings $settings, CacheInterface $cache)
    {
        $this->maxItems = $settings->concurrency;
        $this->lifetime = $settings->poolLifetime;
        $this->cache    = $cache;
    }

    /**
     * Check if there is room
     *
     * @param mixed $context
     *
     * @return bool
     */
    public function canIUse($context = null)
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;
        $this->logPoolUsage($poolUsage, $context);
        return ($poolUsage < $this->maxItems) ? true : false;

    }

    /**
     * Add one item to pool
     *
     * @param mixed $context
     *
     */
    public function increment($context = null)
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;
        $this->logPoolUsage($poolUsage, $context);
        $this->cache->set(self::CACHE_KEY, $poolUsage + 1, $this->lifetime);
    }

    /**
     * Remove one item from pool
     *
     * @param mixed $context
     */
    public function decrement($context = null)
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;
        $this->logPoolUsage($poolUsage, $context);
        if ($poolUsage > 1) {
            $this->cache->set(self::CACHE_KEY, $poolUsage - 1, $this->lifetime);
        } else {
            $this->cache->delete(self::CACHE_KEY);
        }
    }

    /**
     * @param int  $currentUsage
     * @param mixed $context
     */
    protected function logPoolUsage($currentUsage, $context = null)
    {
        if (!YII_DEBUG) {
            return;
        }
        Craft::debug(
            Craft::t(
                'async-queue',
                'ProcessPool::{method}() ({currentUsage} of {max}, context: {context})', [
                    'currentUsage' => $currentUsage,
                    'max'          => $this->maxItems,
                    'method'       => debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1]['function'],
                    'context'      => print_r($context, true)
                ]
            ),
            'async-queue'
        );
    }

}
