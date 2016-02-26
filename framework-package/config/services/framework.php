<?php

return [
    // Storage engines
    'storage.database' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Container\\Storage\\DatabaseStorage'
    ],

    // Requesting
    'bundle.http_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\BundleRouter\\HttpRouter'
    ],

    'bundle.console_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\BundleRouter\\ConsoleRouter'
    ],

    'proxy' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Proxy\\Proxy',
        'arguments' => ['container']
    ],

    'profiler' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Proxy\\Profiler'
    ],

    // Twig
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

    'twig.framework_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\FrameworkExtension',
        'arguments' => ['container']
    ],

    'twig.http_router_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\HttpRouterExtension',
        'arguments' => ['container']
    ],

    'twig.console_router_extension' => [
        'class' => 'Perfumer\\Framework\\View\\TwigExtension\\ConsoleRouterExtension',
        'arguments' => ['container']
    ],

    // Session
    'session' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Session\\Pool',
        'arguments' => ['#cache.memcache']
    ],

    'cookie' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Session\\Cookie'
    ],

    // Cache
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

    'cache.ephemeral' => [
        'shared' => true,
        'class' => 'Stash\\Pool',
        'arguments' => ['#cache.ephemeral_driver']
    ],

    'cache.ephemeral_driver' => [
        'shared' => true,
        'class' => 'Stash\\Driver\\Ephemeral'
    ],

    // Translator
    'translator' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Translator\\Core',
        'arguments' => ['#cache', [
            'locale' => 'ru'
        ]]
    ],

    // Validation
    'validation' => [
        'class' => 'Perfumer\\Component\\Validation\\Core',
        'arguments' => ['#translator']
    ],

    // Framework bundle default routers
    'framework.request' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Request',
        'arguments' => ['$0', '$1', '$2', '$3', [
            'prefix' => 'Perfumer\\FrameworkPackage\\Controller',
            'suffix' => 'Controller'
        ]]
    ],

    'framework.view' => [
        'class' => 'Perfumer\\Framework\\View\\TemplateView',
        'arguments' => ['#twig', '#framework.view.template_provider']
    ],

    'framework.view.template_provider' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\View\\TemplateProvider\\TwigFilesystemProvider',
        'arguments' => ['#twig.filesystem_loader', __DIR__ . '/../../view', 'framework']
    ]
];
