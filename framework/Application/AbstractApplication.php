<?php

namespace Perfumer\Framework\Application;

use Perfumer\Component\Container\Container;

abstract class AbstractApplication
{
    /**
     * @return void
     */
    public function run()
    {
        $this->before();

        $container = new Container();
        $container->registerBundles($this->getBundles());

        $this->after($container);

        $container->get('proxy')->run();
    }

    /**
     * @return array
     */
    abstract public function getBundles();

    /**
     * @return void
     */
    protected function before()
    {
    }

    /**
     * @param Container $container
     * @return void
     */
    protected function after(Container $container)
    {
    }
}