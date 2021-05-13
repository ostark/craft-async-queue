<?php namespace ostark\AsyncQueue\TestUtility;

use craft\web\Controller as BaseController;
use yii\web\Response;


class Controller extends BaseController
{
    /**
     * @inheritdoc
     */

    public function actionRun(): Response
    {
        // Push the job multiple times to the queue
        foreach (range(1, 10) as $counter) {
            \Craft::$app->getQueue()->push(new TestJob([
                'counter' => $counter,
            ]));
        }

        // Redirect back
        return $this->redirectToPostedUrl();
    }

    public function actionRunWithDelay(): Response
    {
        // Push the job multiple times to the queue with a 10 second delay
        $queue = \Craft::$app->getQueue();
        foreach (range(1, 10) as $counter) {
            $queue->delay($counter*10);
            $queue->push(new TestJob([
                'counter' => $counter,
            ]));
        }

        // Redirect back
        return $this->redirectToPostedUrl();
    }
}

