<?php

use Perfumer\Component\Container\Container;
use Perfumer\Component\Container\Exception\ContainerException;
use Perfumer\Component\Container\Exception\NotFoundException;
use Perfumer\Component\Container\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testGetSharedService()
    {
        $container = new Container();

        // Define a shared service
        $container->addDefinitions([
            'shared_service' => [
                'class' => stdClass::class,
                'shared' => true,
            ],
        ]);

        // Get the service twice, should return the same instance
        $service1 = $container->get('shared_service');
        $service2 = $container->get('shared_service');

        $this->assertSame($service1, $service2);
    }

    public function testGetNonSharedService()
    {
        $container = new Container();

        // Define a non-shared service
        $container->addDefinitions([
            'non_shared_service' => [
                'class' => stdClass::class,
            ],
        ]);

        // Get the service twice, should return different instances
        $service1 = $container->get('non_shared_service');
        $service2 = $container->get('non_shared_service');

        $this->assertNotSame($service1, $service2);
    }

    public function testGetAliasService()
    {
        $container = new Container();

        // Define an alias service
        $container->addDefinitions([
            'original_service' => [
                'class' => stdClass::class,
            ],
            'alias_service' => [
                'alias' => 'original_service',
            ],
        ]);

        // Get the alias service, should return the original service instance
        $service = $container->get('alias_service');

        $this->assertInstanceOf(stdClass::class, $service);
    }

    public function testGetFallbackService()
    {
        $container1 = new Container();
        $container2 = new Container();

        // Define a fallback container
        $container1->setFallbackContainer($container2);

        // Define a service in the second container
        $container2->addDefinitions([
            'fallback_service' => [
                'class' => stdClass::class,
            ],
        ]);

        // Get the fallback service from the first container
        $service = $container1->get('fallback_service');

        $this->assertInstanceOf(stdClass::class, $service);
    }

    public function testGetServiceWithInit()
    {
        $container = new Container();

        // Define a service with an "init" function
        $container->addDefinitions([
            'init_service' => [
                'class' => stdClass::class,
                'init' => function (Container $container, array $parameters) {
                    return new stdClass();
                },
            ],
        ]);

        // Get the service, should return the object created by the "init" function
        $service = $container->get('init_service');

        $this->assertInstanceOf(stdClass::class, $service);
    }
}