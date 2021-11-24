<?php

namespace Perfumer\Framework\Application;

use Perfumer\Component\Container\Container;
use Perfumer\Component\Container\Exception\NotFoundException;
use Perfumer\Framework\Controller\Module;
use Perfumer\Framework\Proxy\Exception\ForwardException;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Proxy;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\VarDumper;

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
     * @param $request mixed
     *
     * @return void
     * @throws NotFoundException
     * @throws ProxyException
     * @throws ForwardException
     */
    public function run($request = null): void
    {
        $this->beforeContainerInit();

        $this->container = new Container();

        $this->configure();

        $this->is_configured = true;

        $this->afterContainerInit();

        // VarDumper initialization
        // "symfony/var-dumper" lib must be included to composer
        $var_dumper_remote = $this->container->getParam('var_dumper/remote');
        $var_dumper_server = $this->container->getParam('var_dumper/server');

        if ($var_dumper_remote && $var_dumper_server && class_exists('\\Symfony\\Component\\VarDumper\\VarDumper')) {
            $cloner = new VarCloner();
            $fallbackDumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper() : new HtmlDumper();
            $dumper = new ServerDumper($var_dumper_server, $fallbackDumper, [
                'cli' => new CliContextProvider(),
                'source' => new SourceContextProvider(),
            ]);

            VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            });
        }

        $this->container->registerSharedService('application', $this);

        /** @var Proxy $proxy */
        $proxy = $this->container->get('proxy');

        $proxy->setModules($this->modules);

        $proxy->initExternalRequestResponse($request);

        $this->afterRequestResponseInit();

        $proxy->run();
    }

    /**
     * @return void
     */
    protected function beforeContainerInit(): void
    {
        $this->before();
    }

    /**
     * @return void
     */
    protected function afterContainerInit(): void
    {
        $this->after();
    }

    /**
     * @return void
     */
    protected function afterRequestResponseInit(): void
    {
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
    }

    /**
     * @return void
     * @deprecated use beforeContainerInit() instead
     */
    protected function before(): void
    {
    }

    /**
     * @return void
     * @deprecated use afterContainerInit() instead
     */
    protected function after(): void
    {
    }

    /**
     * @param Package $package
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @throws ApplicationException
     */
    public function addPackage(Package $package, $env = null, $build_type = null, $flavor = null): void
    {
        if ($this->is_configured) {
            throw new ApplicationException('Application is already configured. You can not add new packages.');
        }

        if (!$this->passVariants($env, $build_type, $flavor)) {
            return;
        }

        $package->configure($this);
    }

    /**
     * @param $file
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @throws ApplicationException
     */
    public function addResources($file, $env = null, $build_type = null, $flavor = null): void
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
    public function addDefinitions($file, $env = null, $build_type = null, $flavor = null): void
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
    public function addModule(Module $module, $env = null, $build_type = null, $flavor = null): void
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
