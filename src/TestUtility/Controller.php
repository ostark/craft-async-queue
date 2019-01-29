<?php namespace ostark\AsyncQueue\TestUtility;

use craft\web\Controller as BaseController;
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
        foreach (range(1, 10) as $counter) {
            \Craft::$app->getQueue()->push(new TestJob([
                'counter' => $counter
            ]));
        }
        return $this->redirectToPostedUrl();
    }
}



