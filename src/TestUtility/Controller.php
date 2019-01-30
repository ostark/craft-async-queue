<?php namespace ostark\AsyncQueue\TestUtility;

use craft\web\Controller as BaseController;
use ostark\AsyncQueue\Events\TestJobEvent;
use yii\web\Response;

/**
 * Class Controller
 *
 * @package ostark\AsyncQueue\TestUtility
 */
class Controller extends BaseController
{
    /**
     * @inheritdoc
     */
    //public $enableCsrfValidation = false;

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
}

