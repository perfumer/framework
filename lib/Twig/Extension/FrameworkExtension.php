<?php

namespace Perfumer\Twig\Extension;

use Perfumer\Proxy\Core as Proxy;

class FrameworkExtension extends \Twig_Extension
{
    protected $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function getName()
    {
        return 'framework_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('url', [$this, 'url']),
            new \Twig_SimpleFunction('prefix', [$this, 'prefix']),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('query', [$this, 'query']),
            new \Twig_SimpleFunction('arg', [$this, 'arg'])
        ];
    }

    public function url($url, $id = null, $query = [], $ignore_prefixes = false)
    {
        $generated_url = '/' . trim($url, '/');

        if ($this->proxy->p() && !$ignore_prefixes)
            $generated_url = '/' . implode('/', $this->proxy->p()) . $generated_url;

        if ($id)
            $generated_url .= '-' . $id;

        if ($query)
        {
            $query_parts = [];

            foreach ($query as $key => $value)
                $query_parts[] = $key . '=' . urlencode($value);

            $generated_url .= '?' . implode('&', $query_parts);
        }

        return $generated_url;
    }

    public function prefix($name)
    {
        return $this->proxy->getPrefix($name);
    }

    public function id()
    {
        return $this->proxy->getId();
    }

    public function query($name)
    {
        return $this->proxy->getQuery($name);
    }

    public function arg($name)
    {
        return $this->proxy->getArg($name);
    }
}