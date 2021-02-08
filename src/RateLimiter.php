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
        try {
            $reserved = $this->queue->getTotalReserved();
        } catch (\Exception $exception) {
            $reserved = 0;
        }

        $currentUsage = $this->internalCount + $reserved;

        $this->logAttempt($currentUsage, $context);

        return ($currentUsage < $this->maxItems) ? true : false;
    }

    public function increment(): void
    {
        $this->internalCount++;
    }


    protected function logAttempt(int $currentUsage, string $context = null): void
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
