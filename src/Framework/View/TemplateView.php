<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\View\TemplateProvider\ProviderInterface;

class TemplateView extends AbstractView
{
    /**
     * @var mixed
     */
    protected $templating;

    /**
     * @var ProviderInterface
     */
    protected $template_provider;

    /**
     * @var string
     */
    protected $template;

    /**
     * TemplateView constructor.
     * @param mixed $templating
     * @param ProviderInterface $template_provider
     */
    public function __construct($templating, ProviderInterface $template_provider)
    {
        $this->templating = $templating;
        $this->template_provider = $template_provider;
    }

    /**
     * @param string|null $template
     * @param array $vars
     * @return mixed
     */
    public function render($template = null, $vars = [])
    {
        $template = $template ?: $this->template;
        $vars = $vars ? array_merge($this->vars, $vars) : $this->vars;

        $template = $this->template_provider->dispatch($template);

        return $this->templating->render($template, $vars);
    }

    /**
     * @return mixed
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * @param mixed $templating
     * @return $this
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;

        return $this;
    }

    /**
     * @return ProviderInterface
     */
    public function getTemplateProvider()
    {
        return $this->template_provider;
    }

    /**
     * @param ProviderInterface $template_provider
     * @return $this
     */
    public function setTemplateProvider(ProviderInterface $template_provider)
    {
        $this->template_provider = $template_provider;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }
}
