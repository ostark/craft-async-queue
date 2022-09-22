<?php namespace ostark\AsyncQueue;

use craft\base\Model;

class Settings extends Model
{
    public int $concurrency;

    public bool $enabled = true;


    /**
     * Settings constructor.
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'concurrency'  => (int) $this->env('ASYNC_QUEUE_CONCURRENCY', 1),
            'enabled'      => ($this->env('DISABLE_ASYNC_QUEUE', '0') == '1') ? false : true
        ], $config);

        parent::__construct($config);
    }

    /**
     * Env var access with default
     *
     * @param mixed  $default
     *
     */
    protected function env(string $name, $default = null): false|string
    {
        if (getenv($name) === false) {
            return (string)$default;
        }

        return getenv($name);
    }

}
