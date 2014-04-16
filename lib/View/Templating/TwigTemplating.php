<?php

namespace Perfumer\View\Templating;

class TwigTemplating extends AbstractTemplating
{
    protected $templating;

    public function __construct(\Twig_Environment $templating)
    {
        $this->templating = $templating;
    }

    public function render($template, $vars)
    {
        return $this->templating->render($template, $vars);
    }
}