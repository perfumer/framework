<?php

namespace Perfumer\MVC\View;

use Perfumer\MVC\View\Router\RouterInterface;

class ViewFactory
{
    protected $templating;

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $options = [];

    public function __construct($templating, RouterInterface $router, $options = [])
    {
        $this->templating = $templating;
        $this->router = $router;

        $default_options = [
            'extension' => 'php'
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getInstance()
    {
        return new View($this->templating, $this->router, $this->options);
    }
}