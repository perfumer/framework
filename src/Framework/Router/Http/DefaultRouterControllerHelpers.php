<?php

namespace Perfumer\Framework\Router\Http;

trait DefaultRouterControllerHelpers
{
    /**
     * Shortcut for HttpRouter getId() method
     *
     * @param $index
     * @return mixed
     */
    protected function i($index = null)
    {
        return $this->getRouter()->getId($index);
    }

    /**
     * Shortcut for HttpRouter getFields() method
     *
     * @param string|array|null $keys
     * @param mixed $default
     * @return mixed
     */
    protected function f($keys = null, $default = null)
    {
        return $this->getRouter()->getFields($keys, $default);
    }

    /**
     * Shortcut for HttpRouter generateUrl() method
     *
     * @param $url
     * @param $id
     * @param $query
     * @return string
     */
    public function generateUrl($url, $id = null, $query = [])
    {
        return $this->getRouter()->generateUrl($url, $id, $query);
    }
}
