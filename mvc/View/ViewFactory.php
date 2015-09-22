<?php

namespace Perfumer\MVC\View;

use Perfumer\MVC\Bundler\Bundler;

class ViewFactory
{
    protected $templating;

    /**
     * @var Bundler
     */
    protected $bundler;

    protected $options = [];

    public function __construct($templating, Bundler $bundler, $options = [])
    {
        $this->templating = $templating;
        $this->bundler = $bundler;

        $default_options = [
            'extension' => 'php'
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getInstance()
    {
        return new View($this);
    }

    /**
     * @return Bundler
     */
    public function getBundler()
    {
        return $this->bundler;
    }

    public function getTemplating()
    {
        return $this->templating;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
}