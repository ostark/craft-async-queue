<?php

use ostark\AsyncQueue\ProcessPool;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ostark\AsyncQueue\ProcessPool
 */
class ProcessPoolTest extends TestCase
{

    /**
     * @covers \ostark\AsyncQueue\ProcessPool::canIUse
     */
    public function test_canIUse_success()
    {
        $pool = $this->makeProcessPool();

        $pool->maxItems = 2;

        $this->assertTrue($pool->canIUse());
    }

    /**
     * @covers \ostark\AsyncQueue\ProcessPool::canIUse
     */
    public function test_canIUse_fail()
    {
        $pool = $this->makeProcessPool();

        $pool->maxItems = 2;
        $pool->increment('test');
        $pool->increment('test');
        $bool = $pool->canIUse();

        $this->assertFalse($bool);
    }


    /**
     * @covers \ostark\AsyncQueue\ProcessPool::increment
     */
    public function test_increment_increment_one()
    {
        $pool = $this->makeProcessPool();

        $pool->increment('test');
        $usage = $pool->cache->get(ProcessPool::CACHE_KEY);

        $this->assertEquals(1, $usage);
    }

    /**
     * @covers \ostark\AsyncQueue\ProcessPool::decrement
     */
    public function test_increment_increment_three_decrement_one()
    {
        $pool = $this->makeProcessPool();

        $pool->increment('test'); // 1
        $pool->increment('test'); // 2
        $pool->increment('test'); // 3
        $pool->decrement('test');

        $usage = $pool->cache->get(ProcessPool::CACHE_KEY);

        $this->assertEquals(2, $usage);
    }


    /**
     * @return \ostark\AsyncQueue\ProcessPool
     */
    protected function makeProcessPool()
    {
        return new ProcessPool(
            new \ostark\AsyncQueue\Settings(),
            new \yii\caching\ArrayCache()
        );
    }
}
