<?php namespace ostark\AsyncQueue;

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
     * @return bool
     */
    public function canIUse()
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;

        return ($poolUsage < $this->maxItems) ? true : false;

    }

    /**
     * Add one item to pool
     */
    public function increment()
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;
        $this->cache->set(self::CACHE_KEY, $poolUsage + 1, $this->lifetime);
    }

    /**
     * Remove one item from pool
     */
    public function decrement()
    {
        $poolUsage = $this->cache->get(self::CACHE_KEY) ?: 0;

        if ($poolUsage > 1) {
            $this->cache->set(self::CACHE_KEY, $poolUsage - 1, $this->lifetime);
        } else {
            $this->cache->delete(self::CACHE_KEY);
        }
    }

}
