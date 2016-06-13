<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\AbstractConfigurator;

class ProxyConfigurator extends AbstractConfigurator
{
    /**
     * @var Proxy
     */
    protected $proxy;

    /**
     * @param Proxy $proxy
     */
    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'proxy';
    }

    /**
     * @return array
     */
    public function getResourceKeys()
    {
        return [
            'controller_overrides', 'template_overrides', 'sync_subscribers', 'async_subscribers'
        ];
    }

    /**
     * @param array $resources
     */
    public function configure(array $resources = [])
    {
        if (isset($resources['controller_overrides'])) {
            $this->proxy->addControllersOverrides($resources['controller_overrides']);
        }

        if (isset($resources['template_overrides'])) {
            $this->proxy->addTemplateOverrides($resources['template_overrides']);
        }

        if (isset($resources['sync_subscribers'])) {
            $this->proxy->addSyncSubscribers($resources['sync_subscribers']);
        }

        if (isset($resources['async_subscribers'])) {
            $this->proxy->addAsyncSubscribers($resources['async_subscribers']);
        }
    }
}