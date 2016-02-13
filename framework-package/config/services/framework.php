<?php

return [
    // Storage engines
    'storage.database' => [
        'shared' => true,
        'class' => 'Perfumer\\Component\\Container\\Storage\\DatabaseStorage'
    ],

    // Requesting
    'proxy' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\Proxy\\Proxy',
        'arguments' => ['container']
    ],

    // Framework bundle default routers
    'framework.internal_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\InternalRouter\\DirectoryRouter',
        'arguments' => [[
            'prefix' => 'Perfumer\\FrameworkPackage\\Controller',
            'suffix' => 'Controller'
        ]]
    ],

    'framework.view_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\View\\Router\\TwigRouter',
        'arguments' => ['#twig', 'templates_dir' => __DIR__ . '/../../view', 'framework']
    ],
];
