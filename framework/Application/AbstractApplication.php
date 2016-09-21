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

        $this->after();

        $container->get('proxy')->run();
    }

    /**
     * @return array
     */
    protected function getBundles()
    {
        return [];
    }

    /**
     * @return void
     */
    protected function before()
    {
    }


    /**
     * @return void
     */
    protected function after()
    {
    }
}