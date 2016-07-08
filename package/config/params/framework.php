<?php

return [
    'bundle.http_resolver' => [
        'bundles' => []
    ],

    'bundle.console_resolver' => [
        'bundles' => []
    ],

    'propel' => [
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