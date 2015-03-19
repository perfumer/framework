<?php

namespace Perfumer\MVC\InternalRouter;

use Perfumer\MVC\Proxy\Request;

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

    public function dispatch($url, $action, $args = [])
    {
        $path = explode('/', $url);

        $controller = $this->options['prefix'] . '\\' . implode('\\', array_map('ucfirst', $path)) . $this->options['suffix'];

        $request = new Request();

        $request->setUrl($url)->setController($controller)->setAction($action)->setArgs($args);

        return $request;
    }
}