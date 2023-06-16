<?php

return [
    'cache' => [
        'alias' => 'cache.memcache'
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
        'arguments' => [['path' => '@dir/file_cache']]
    ],

    'cache.memcache' => [
        'shared' => true,
        'class' => 'Stash\\Pool',
        'arguments' => ['#cache.memcache_driver']
    ],

    'cache.memcache_driver' => [
        'shared' => true,
        'class' => 'Stash\\Driver\\Memcache',
        'arguments' => [[
            'servers' => ['@memcache/host', '@memcache/port']
        ]]
    ],

    'cookie' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Auth\\Cookie'
    ],

    'gateway' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Gateway\\CompositeGateway',
        'arguments' => ['#application', '#gateway.http', '#gateway.console']
    ],

    'gateway.console' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Gateway\\ConsoleGateway',
        'arguments' => [[
            'debug' => '@gateway/debug'
        ]]
    ],

    'gateway.http' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Gateway\\HttpGateway',
        'arguments' => [[
            'debug' => '@gateway/debug'
        ]]
    ],

    'generator.endpoint' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Endpoint\\EndpointGenerator',
    ],

    'logger' => [
        'shared' => true,
        'class' => 'Monolog\\Logger',
        'arguments' => ['Logger', ['#logger.file_handler']]
    ],

    'logger.file_handler' => [
        'shared' => true,
        'class' => 'Monolog\\Handler\\RotatingFileHandler',
        'arguments' => ['@dir/log_file', 10]
    ],

    'package.framework.console_request' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Request',
        'arguments' => ['$0', '$1', '$2', '$3', [
            'prefix' => 'Perfumer\\Package\\Framework\\Command',
            'suffix' => 'Command'
        ]]
    ],

    'package.framework.http_request' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Request',
        'arguments' => ['$0', '$1', '$2', '$3', [
            'prefix' => 'Perfumer\\Package\\Framework\\Controller',
            'suffix' => 'Controller'
        ]]
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
            $model_dir = $container->getParam('propel/model_dir');

            @include_once $model_dir . '/loadDatabase.php';

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
        'arguments' => [[
            'debug' => '@proxy/debug'
        ]],
        'after' => function(Perfumer\Component\Container\Container $container, \Perfumer\Framework\Proxy\Proxy $proxy) {
            $proxy->setContainer($container);
            $proxy->setGateway($container->get('gateway'));
        }
    ],

    'response' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Response'
    ],

    'router.console' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Router\\ConsoleRouter'
    ],

    'session' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Auth\\Session',
        'arguments' => ['#cache']
    ],

    'storage.database' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Container\\Storage\\DatabaseStorage'
    ],

    'translator' => [
        'shared' => true,
        'class' => 'Symfony\\Component\\Translation\\Translator',
        'arguments' => ['@translator/locale'],
        'after' => function (\Perfumer\Component\Container\Container $container, \Symfony\Component\Translation\Translator $translator) {
            $translator->addLoader('file', new \Symfony\Component\Translation\Loader\PhpFileLoader());

            $resources = $container->getResource('_translator');

            foreach ($resources as $resource) {
                $domain = isset($resource[2]) ? $resource[2] : null;

                $translator->addResource('file', $resource[0], $resource[1], $domain);
            }
        }
    ],

    'twig' => [
        'shared' => true,
        'class' => 'Twig_Environment',
        'arguments' => ['#twig.filesystem_loader', [
            'cache' => '@dir/twig_cache',
            'debug' => '@twig/debug'
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
