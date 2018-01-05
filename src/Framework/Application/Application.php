<?php

namespace Perfumer\Framework\Application;

use Perfumer\Component\Container\AbstractBundle;
use Perfumer\Component\Container\Container;

class Application
{
    const HTTP = 'http';
    const CLI = 'cli';
    const PROD = 'prod';
    const DEV = 'dev';
    const STAGE = 'stage';
    const TEST = 'test';
    
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $build_type;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $flavor;

    /**
     * @return void
     */
    public function run()
    {
        $this->before();

        $this->container = new Container();
        
        $this->configure();

        $this->after();
        
        $this->container->registerSharedService('application', $this);

        $this->container->get('proxy')->run();
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

    /**
     * @return void
     */
    protected function configure()
    {
    }
    
    protected function addBundle(AbstractBundle $bundle, $env = null, $build_type = null, $flavor = null)
    {
        if ($env !== null && $env !== $this->env) {
            return;
        }

        if ($build_type !== null && $build_type !== $this->build_type) {
            return;
        }

        if ($flavor !== null && $flavor !== $this->flavor) {
            return;
        }
        
        $this->container->registerBundle($bundle);
    }

    /**
     * @return string
     */
    public function getBuildType(): string
    {
        return $this->build_type;
    }

    /**
     * @param string $build_type
     */
    public function setBuildType(string $build_type)
    {
        $this->build_type = $build_type;
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @param string $env
     */
    public function setEnv(string $env)
    {
        $this->env = $env;
    }

    /**
     * @return string
     */
    public function getFlavor(): string
    {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor(string $flavor)
    {
        $this->flavor = $flavor;
    }
}