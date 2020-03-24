<?php


namespace ostark\AsyncQueue;


use craft\queue\Queue;

class RateLimiter
{

    /**
     * @var integer
     */
    public $maxItems;

    /**
     * @var \craft\queue\Queue
     */
    protected $queue;

    /**
     * @var integer
     */
    protected $internalCount = 0;

    public function __construct(Queue $queue, Settings $settings)
    {
        $this->queue    = $queue;
        $this->maxItems = $settings->concurrency;
    }

    /**
     * Check if actual processes jobs is lower than the upper limit
     *
     */
    public function canIUse(string $context = null): bool
    {
        $reserved     = $this->queue->getTotalReserved();
        $currentUsage = $this->internalCount + $reserved;

        $this->logAttempt($currentUsage, $context);

        return ($reserved < $this->maxItems) ? true : false;
    }

    public function increment(string $context = null)
    {
        $this->internalCount++;
    }

    public function decrement(string $context = null)
    {
        // dummy
    }


    protected function logAttempt(int $currentUsage, string $context = null)
    {

        \Craft::debug(
            \Craft::t(
                'async-queue',
                'RateLimiter ({currentUsage} of {max}, context: {context})', [
                    'currentUsage' => $currentUsage,
                    'max'          => $this->maxItems,
                    'context'      => print_r($context, true)
                ]
            ),
            'async-queue'
        );
    }

}
