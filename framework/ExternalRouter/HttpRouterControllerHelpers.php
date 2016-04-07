<?php

namespace Perfumer\Framework\ExternalRouter;

trait HttpRouterControllerHelpers
{
    /**
     * Shortcut for HttpRouter getPrefix() method
     *
     * @param $name
     * @param $default
     * @return mixed
     */
    protected function p($name = null, $default = null)
    {
        return $this->getExternalRouter()->getPrefix($name, $default);
    }

    /**
     * Shortcut for HttpRouter getId() method
     *
     * @param $index
     * @return mixed
     */
    protected function i($index = null)
    {
        return $this->getExternalRouter()->getId($index);
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
        return $this->getExternalRouter()->getFields($keys, $default);
    }

    /**
     * Shortcut for HttpRouter getQuery() method
     *
     * @param $name
     * @param $default
     * @return mixed
     * @deprecated
     */
    protected function q($name = null, $default = null)
    {
        return $this->getExternalRouter()->getQuery($name, $default);
    }

    /**
     * Shortcut for HttpRouter getArg() method
     *
     * @param $name
     * @param $default
     * @return mixed
     * @deprecated
     */
    protected function a($name = null, $default = null)
    {
        return $this->getExternalRouter()->getArg($name, $default);
    }

    /**
     * Shortcut for HttpRouter generateUrl() method
     *
     * @param $url
     * @param $id
     * @param $query
     * @param $prefixes
     * @return string
     */
    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->getExternalRouter()->generateUrl($url, $id, $query, $prefixes);
    }
}
