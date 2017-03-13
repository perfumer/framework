<?php

namespace Perfumer\Framework\View\TemplateProvider;

interface ProviderInterface
{
    /**
     * @param string $template
     * @return string
     */
    public function dispatch($template);
}
