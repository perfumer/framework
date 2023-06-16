<?php

return [
    'dir' => [
        'log_file' => __DIR__ . '/../../../../../tmp/logs/example.log',
        'twig_cache' => __DIR__ . '/../../../../../tmp/twig/',
        'file_cache' => __DIR__ . '/../../../../../tmp/cache/'
    ],

    'gateway' => [
        'debug' => false
    ],

    'memcache' => [
        'host' => '127.0.0.1',
        'port' => 11211
    ],

    'propel' => [
        'bin' => 'vendor/bin/propel',
        'project' => 'example',
        'database' => 'pgsql',
        'dsn' => 'pgsql:host=localhost;dbname=example',
        'db_user' => 'user',
        'db_password' => 'password',
        'platform' => 'pgsql',
        'config_dir' => '',
        'schema_dir' => '',
        'model_dir' => '',
        'migration_dir' => '',
        'migration_table' => 'propel_migration',
    ],

    'proxy' => [
        'debug' => false,
        'fake' => false,
    ],

    'translator' => [
        'locale' => 'ru_RU'
    ],

    'twig' => [
        'debug' => false
    ],

    // Symfony VarDumper component is required to be installed to work this parameters
    'var_dumper' => [
        'remote' => false, // if "true" VarDumper will send dumps to the server below
        'server' => 'tcp://127.0.0.1:9912',
    ],
];
