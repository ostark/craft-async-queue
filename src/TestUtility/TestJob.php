<?php namespace ostark\AsyncQueue\TestUtility;

use craft\queue\BaseJob;
use craft\queue\QueueInterface;

class TestJob extends BaseJob
{

    public int $counter = 1;

    /**
     * @param \yii\queue\Queue|QueueInterface $queue The queue the job belongs to
     */
    public function execute($queue): void
    {
        sleep(10);
    }


    protected function defaultDescription(): ?string
    {
        return 'Async Queue Test Job ' . $this->counter;
    }
}
