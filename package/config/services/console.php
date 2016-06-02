<?php

return [
    'bundle_resolver' => [
        'alias' => 'bundle.console_resolver'
    ],

    'framework.router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Router\\ConsoleRouter'
    ],

    'framework.request' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Request',
        'arguments' => ['$0', '$1', '$2', '$3', [
            'prefix' => 'Perfumer\\Package\\Command',
            'suffix' => 'Command'
        ]]
    ]
];
