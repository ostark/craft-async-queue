<?php
/**
 * Created by PhpStorm.
 * User: os
 * Date: 22.08.17
 * Time: 17:50
 */

namespace ostark\asyncqueue;


use craft\queue\BaseJob;
use craft\queue\QueueInterface;

class DemoJob extends BaseJob
{

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        echo 'I AM A JOB';
        \Craft::info('DemoJob - before sleep');
        sleep(20);
        \Craft::info('DemoJob - after sleep');

    }
}
