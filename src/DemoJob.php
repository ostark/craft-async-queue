<?php
/**
 * Created by PhpStorm.
 * User: os
 * Date: 22.08.17
 * Time: 17:50
 */

namespace ostark\asyncqueue;

use craft\queue\BaseJob;

class DemoJob extends BaseJob
{

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        \Craft::info('DemoJob >>> before sleep');
        sleep(5);
        \Craft::info('DemoJob >>> after sleep');

    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Sleeping for 5 seconds');
    }
}
