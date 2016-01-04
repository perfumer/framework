<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\Bundler\Bundler;

class TemplateView extends AbstractView
{
    protected $templating;

    /**
     * @var Bundler
     */
    protected $bundler;

    protected $bundle;
    protected $url;

    public function __construct($templating, Bundler $bundler)
    {
        $this->templating = $templating;
        $this->bundler = $bundler;
    }

    public function render($bundle = null, $url = null, $vars = [])
    {
        $bundle = $bundle ?: $this->bundle;
        $url = $url ?: $this->url;
        $vars = $vars ? array_merge($this->vars, $vars) : $this->vars;

        list($bundle, $url) = $this->bundler->overrideTemplate($bundle, $url);

        $template = $this->bundler->getService($bundle, 'view_router')->dispatch($url);

        return $this->templating->render($template, $vars);
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

    public function getTemplateBundle()
    {
        return $this->bundle;
    }

    public function getTemplateUrl()
    {
        return $this->url;
    }

    public function setTemplate($bundle, $url)
    {
        $this->bundle = $bundle;
        $this->url = $url;

        return $this;
    }

    public function setTemplateBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    public function setTemplateUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}