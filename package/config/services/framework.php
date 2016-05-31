<?php

return [
    'bundle.console_resolver' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Bundle\\Resolver\\ConsoleResolver'
    ],

    'bundle.http_resolver' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Bundle\\Resolver\\HttpResolver'
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
            $driver->setOptions(['path' => __DIR__ . '/../../tmp/cache/']);
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

    'cookie' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Session\\Cookie'
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
        'arguments' => ['container']
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
        'class' => 'Perfumer\\Component\\Translator\\Core',
        'arguments' => ['#cache', [
            'locale' => 'ru'
        ]]
    ],

    'twig' => [
        'shared' => true,
        'class' => 'Twig_Environment',
        'arguments' => ['#twig.filesystem_loader', [
            'cache' => __DIR__ . '/../../tmp/twig/'
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

    'validation' => [
        'class' => 'Perfumer\\Component\\Validation\\Core',
        'arguments' => ['#translator']
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
