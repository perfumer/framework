<?php

return [
    'bundle.console_resolver' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\BundleResolver\\ConsoleResolver',
        'arguments' => ['@bundle_resolver/bundles']
    ],

    'bundle.http_resolver' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\BundleResolver\\HttpResolver',
        'arguments' => ['@bundle_resolver/bundles']
    ],

    'cache.ephemeral' => [
        'shared' => true,
        'class' => 'Stash\\Pool',
        'arguments' => ['#cache.ephemeral_driver']
    ],

    'cache.ephemeral_driver' => [
        'shared' => true,
        'class' => 'Stash\\Driver\\Ephemeral'
    ],

    'cache.file' => [
        'shared' => true,
        'class' => 'Stash\\Pool',
        'arguments' => ['#cache.file_driver']
    ],

    'cache.file_driver' => [
        'shared' => true,
        'class' => 'Stash\\Driver\\FileSystem',
        'after' => function(\Perfumer\Component\Container\Container $container, \Stash\Driver\FileSystem $driver) {
            $driver->setOptions(['path' => __DIR__ . '/../../../tmp/cache/']);
        }
    ],

    'cache.memcache' => [
        'shared' => true,
        'class' => 'Stash\\Pool',
        'arguments' => ['#cache.memcache_driver']
    ],

    'cache.memcache_driver' => [
        'shared' => true,
        'class' => 'Stash\\Driver\\Memcache',
        'after' => function(\Perfumer\Component\Container\Container $container, \Stash\Driver\Memcache $driver) {
            $driver->setOptions();
        }
    ],

    'configurator.proxy' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Configurator\\ProxyConfigurator',
        'arguments' => ['#proxy']
    ],

    'configurator.translator' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Configurator\\SymfonyTranslatorConfigurator',
        'arguments' => ['#translator']
    ],

    'cookie' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Session\\Cookie'
    ],

    'logger' => [
        'shared' => true,
        'class' => 'Monolog\\Logger',
        'arguments' => ['Logger', ['#logger.file_handler']]
    ],

    'logger.file_handler' => [
        'shared' => true,
        'class' => 'Monolog\\Handler\\RotatingFileHandler',
        'arguments' => [__DIR__ . '/../../../tmp/logs/example.log', 10, \Monolog\Logger::WARNING]
    ],

    'profiler' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Proxy\\Profiler'
    ],

    'propel.connection_manager' => [
        'class' => 'Propel\\Runtime\\Connection\\ConnectionManagerSingle',
        'after' => function(\Perfumer\Component\Container\Container $container, \Propel\Runtime\Connection\ConnectionManagerSingle $connection_manager) {
            $connection_manager->setConfiguration([
                'dsn' => $container->getParam('propel/dsn'),
                'user' => $container->getParam('propel/db_user'),
                'password' => $container->getParam('propel/db_password'),
                'settings' => [
                    'charset' => 'utf8',
                ]
            ]);
        }
    ],

    'propel.service_container' => [
        'shared' => true,
        'class' => 'Propel\\Runtime\\Propel',
        'static' => 'getServiceContainer',
        'after' => function(\Perfumer\Component\Container\Container $container, $service_container) {
            $project = $container->getParam('propel/project');
            $database = $container->getParam('propel/database');
            $connection_manager = $container->get('propel.connection_manager');
            $service_container->setAdapterClass($project, $database);
            $service_container->setConnectionManager($project, $connection_manager);
        }
    ],

    'proxy' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Proxy\\Proxy',
        'arguments' => ['container', [
            'debug' => '@proxy/debug'
        ]]
    ],

    'session' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Session\\Pool',
        'arguments' => ['#cache.memcache']
    ],

    'storage.database' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Container\\Storage\\DatabaseStorage'
    ],

    'translator' => [
        'shared' => true,
        'class' => 'Symfony\\Component\\Translation\\Translator',
        'arguments' => ['en_US'],
        'after' => function (\Perfumer\Component\Container\Container $container, \Symfony\Component\Translation\Translator $translator) {
            $translator->addLoader('file', new \Symfony\Component\Translation\Loader\PhpFileLoader());
        }
    ],

    'twig' => [
        'shared' => true,
        'class' => 'Twig_Environment',
        'arguments' => ['#twig.filesystem_loader', [
            'cache' => __DIR__ . '/../../../tmp/twig/'
        ]]
    ],

    'twig.filesystem_loader' => [
        'shared' => true,
        'class' => 'Twig_Loader_Filesystem'
    ],

    'twig.console_router_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\ConsoleRouterExtension',
        'arguments' => ['container']
    ],

    'twig.http_router_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\HttpRouterExtension',
        'arguments' => ['container']
    ],

    'twig.framework_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\FrameworkExtension',
        'arguments' => ['container']
    ],

    'validator' => [
        'init' => function (\Perfumer\Component\Container\Container $container) {
            return \Symfony\Component\Validator\Validation::createValidatorBuilder()
                ->setTranslator($container->get('translator'))
                ->getValidator();
        }
    ],

    'view.serialize' => [
        'class' => 'Perfumer\\Framework\\View\\SerializeView',
        'arguments' => ['json']
    ],

    'view.status' => [
        'class' => 'Perfumer\\Framework\\View\\StatusView',
        'arguments' => ['json']
    ],
];
