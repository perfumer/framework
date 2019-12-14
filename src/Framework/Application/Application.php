<?php

namespace Perfumer\Framework\Application;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\Module;
use Perfumer\Framework\Proxy\Proxy;

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
     * @var bool
     */
    protected $is_configured = false;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @return void
     */
    public function run(): void
    {
        $this->before();

        $this->container = new Container();
        
        $this->configure();

        $this->is_configured = true;

        $this->after();
        
        $this->container->registerSharedService('application', $this);

        /** @var Proxy $proxy */
        $proxy = $this->container->get('proxy');

        $proxy->setModules($this->modules);

        $proxy->run();
    }

    /**
     * @return void
     */
    protected function before(): void
    {
    }

    /**
     * @return void
     */
    protected function after(): void
    {
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
    }

    /**
     * @param $file
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @throws ApplicationException
     */
    protected function addResources($file, $env = null, $build_type = null, $flavor = null): void
    {
        if ($this->is_configured) {
            throw new ApplicationException('Application is already configured. You can not add new resources.');
        }

        if (!$this->passVariants($env, $build_type, $flavor)) {
            return;
        }

        $this->container->addResourcesFromFile($file);
    }

    /**
     * @param $file
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @throws ApplicationException
     */
    protected function addDefinitions($file, $env = null, $build_type = null, $flavor = null): void
    {
        if ($this->is_configured) {
            throw new ApplicationException('Application is already configured. You can not add new service definitions.');
        }

        if (!$this->passVariants($env, $build_type, $flavor)) {
            return;
        }

        $this->container->addDefinitionsFromFile($file);
    }

    /**
     * @param Module $module
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @throws ApplicationException
     */
    protected function addModule(Module $module, $env = null, $build_type = null, $flavor = null): void
    {
        if ($this->is_configured) {
            throw new ApplicationException('Application is already configured. You can not add new stacks.');
        }

        if (!$this->passVariants($env, $build_type, $flavor)) {
            return;
        }

        $this->modules[$module->name] = $module;
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
    public function setBuildType(string $build_type): void
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
    public function setEnv(string $env): void
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
    public function setFlavor(string $flavor): void
    {
        $this->flavor = $flavor;
    }

    /**
     * @param $env
     * @param $build_type
     * @param $flavor
     * @return bool
     */
    protected function passVariants($env, $build_type, $flavor): bool
    {
        if ($env !== null && $env !== $this->env) {
            return false;
        }

        if ($build_type !== null && $build_type !== $this->build_type) {
            return false;
        }

        if ($flavor !== null && $flavor !== $this->flavor) {
            return false;
        }

        return true;
    }
}
