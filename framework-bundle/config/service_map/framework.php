<?php

return [
    // Framework bundle default routers
    'framework.internal_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\InternalRouter\\DirectoryRouter',
        'arguments' => [[
            'prefix' => 'Perfumer\\FrameworkBundle\\Controller',
            'suffix' => 'Controller'
        ]]
    ],

    'framework.view_router' => [
        'shared' => true,
        'class' => 'Perfumer\\Framework\\View\\Router\\TwigRouter',
        'arguments' => ['#twig', 'templates_dir' => __DIR__ . '/../../view', 'framework']
    ],
];
