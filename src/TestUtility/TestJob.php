<?php namespace ostark\AsyncQueue\TestUtility;

use craft\queue\BaseJob;
use craft\queue\QueueInterface;

class TestJob extends BaseJob
{

    public $counter = 1;

    /**
     * @param \yii\queue\Queue|QueueInterface $queue The queue the job belongs to
     */
    public function execute($queue)
    {
        sleep(10);
    }


    public function defaultDescription()
    {
        return 'Async Queue Test Job ' . $this->counter;
    }
}
