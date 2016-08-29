<?php

namespace Perfumer\Helper;

class Profiler
{
    /**
     * @var array
     */
    protected $started = [];

    /**
     * @var array
     */
    protected $finished = [];

    /**
     * @param string $key
     * @return void
     */
    public function start($key)
    {
        $this->started[$key] = microtime(true);
    }

    /**
     * @param string $key
     * @return void
     */
    public function finish($key)
    {
        if (isset($this->started[$key])) {
            $microtime = microtime(true);

            $this->finished[$key] = round(1000 * ($microtime - $this->started[$key]), 1);

            unset($this->started[$key]);
        }
    }

    /**
     * @param string $key
     * @return void
     */
    public function cancel($key)
    {
        if (isset($this->started[$key])) {
            unset($this->started[$key]);
        }
    }

    /**
     * @param array|string|null $key
     * @return mixed
     */
    public function getFinished($key = null)
    {
        if ($key === null) {
            return $this->finished;
        }

        if (is_array($key)) {
            return Arr::fetch($this->finished, $key);
        }

        return isset($this->finished[$key]) ? $this->finished[$key] : null;
    }
}
