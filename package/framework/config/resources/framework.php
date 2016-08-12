<?php

return [
    'dir' => [
        'log_file' => __DIR__ . '/../../../../tmp/logs/example.log',
        'twig_cache' => __DIR__ . '/../../../../tmp/twig/',
        'file_cache' => __DIR__ . '/../../../../tmp/cache/'
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
    ],

    'proxy' => [
        'debug' => false
    ],

    'translator' => [
        'locale' => 'ru_RU'
    ],

    'twig' => [
        'debug' => false
    ]
];