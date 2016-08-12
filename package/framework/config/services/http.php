<?php

return [
    'bundle_resolver' => [
        'alias' => 'bundle.http_resolver'
    ],

    'framework.request' => [
        'class' => 'Perfumer\\Framework\\Proxy\\Request',
        'arguments' => ['$0', '$1', '$2', '$3', [
            'prefix' => 'Perfumer\\Package\\Framework\\Controller',
            'suffix' => 'Controller'
        ]]
    ]
];
