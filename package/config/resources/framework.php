<?php

return [
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
    ]
];