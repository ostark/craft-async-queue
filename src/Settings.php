<?php namespace ostark\AsyncQueue;

use craft\base\Model;

/**
 * Settings
 *
 * @author    Oliver Stark
 * @package   AsyncQueue
 * @since     1.3.0
 *
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var integer
     */
    public $concurrency;

    /**
     * @var integer
     */
    public $poolLifetime;


    /**
     * Settings constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'concurrency'  => (int)$this->env('ASYNC_QUEUE_CONCURRENCY', 2),
            'poolLifetime' => (int)$this->env('ASYNC_QUEUE_POOL_LIFETIME', 3600),
        ], $config);

        parent::__construct($config);
    }

    /**
     * Env var access with default
     *
     * @param      $name
     * @param null $default
     *
     * @return array|false|null|string
     */
    protected function env($name, $default = null)
    {
        if (getenv($name) === false) {
            return $default;
        }

        return getenv($name);
    }

}
