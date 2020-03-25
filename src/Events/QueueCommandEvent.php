<?php namespace ostark\AsyncQueue\Events;

use yii\base\Event;

class QueueCommandEvent extends Event
{
    /**
     * @var string
     */
    public $commandLine;

    /**
     * @var bool
     */
    public $useDefaultDecoration = true;
}
