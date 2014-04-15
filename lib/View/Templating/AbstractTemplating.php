<?php

namespace Perfumer\View\Templating;

abstract class AbstractTemplating
{
    abstract public function render($template, $vars);
}