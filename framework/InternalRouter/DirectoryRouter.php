<?php

namespace Perfumer\Framework\InternalRouter;

use Perfumer\Framework\Proxy\Request;

class DirectoryRouter implements RouterInterface
{
    protected $options = [];

    public function __construct($options = [])
    {
        $default_options = [
            'prefix' => 'App\\Controller',
            'suffix' => 'Controller'
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function dispatch($resource)
    {
        $path = explode('/', $resource);

        $controller = $this->options['prefix'] . '\\' . implode('\\', array_map('ucfirst', $path)) . $this->options['suffix'];

        $request = new Request();

        $request->setResource($resource)->setController($controller);

        return $request;
    }
}
