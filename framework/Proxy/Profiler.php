<?php

namespace Perfumer\Framework\Proxy;

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
     * @return $this
     */
    public function start($key)
    {
        $this->started[$key] = microtime(true);

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function finish($key)
    {
        if (isset($this->started[$key])) {
            $microtime = microtime(true);

            $this->finished[$key] = $microtime - $this->started[$key];

            unset($this->started[$key]);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function cancel($key)
    {
        if (isset($this->started[$key])) {
            unset($this->started[$key]);
        }

        return $this;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getFinished($key = null)
    {
        return isset($this->finished[$key]) ? $this->finished[$key] : $this->finished;
    }
}
