<?php

namespace Perfumer\Twig\Extension;

use Perfumer\Assets;

class AssetsExtension extends \Twig_Extension
{
    protected $assets;

    public function __construct(Assets $assets)
    {
        $this->assets = $assets;
    }

    public function getName()
    {
        return 'assets_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('add_css', [$this, 'add_css']),
            new \Twig_SimpleFunction('add_js', [$this, 'add_js']),
            new \Twig_SimpleFunction('add_vendor_css', [$this, 'add_vendor_css']),
            new \Twig_SimpleFunction('add_vendor_js', [$this, 'add_vendor_js']),
            new \Twig_SimpleFunction('get_css', [$this, 'get_css']),
            new \Twig_SimpleFunction('get_js', [$this, 'get_js'])
        ];
    }

    public function add_css($css)
    {
        $this->assets->addCss($css);
    }

    public function add_js($js)
    {
        $this->assets->addJs($js);
    }

    public function add_vendor_css($css)
    {
        $this->assets->addVendorCss($css);
    }

    public function add_vendor_js($js)
    {
        $this->assets->addVendorJs($js);
    }

    public function get_css()
    {
        return $this->assets->getCss();
    }

    public function get_js()
    {
        return $this->assets->getJs();
    }
}