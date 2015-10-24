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

    public function __construct($templating, Bundler $bundler)
    {
        $this->templating = $templating;
        $this->bundler = $bundler;
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
}