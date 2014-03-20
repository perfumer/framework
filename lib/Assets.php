<?php

namespace Perfumer;

class Assets
{
    protected $vendor_path;
    protected $css_path;
    protected $js_path;

    protected $vendor_css = [];
    protected $vendor_js = [];
    protected $css = [];
    protected $js = [];

    public function __construct(array $params)
    {
        $this->vendor_path = '/' . trim($params['vendor_path'], '/');
        $this->css_path = '/' . trim($params['css_path'], '/');
        $this->js_path = '/' . trim($params['js_path'], '/');
    }

    public function getCss()
    {
        $array = [];

        foreach ($this->vendor_css as $css)
            $array[] = $this->vendor_path . '/' . $css . '.css';

        foreach ($this->css as $css)
            $array[] = $this->css_path . '/' . $css . '.css';

        return $array;
    }

    public function getJs()
    {
        $array = [];

        foreach ($this->vendor_js as $js)
            $array[] = $this->vendor_path . '/' . $js . '.js';

        foreach ($this->js as $js)
            $array[] = $this->js_path . '/' . $js . '.js';

        return $array;
    }

    public function addCss($css)
    {
        if (!in_array($css, $this->css))
            $this->css[] = $css;

        return $this;
    }

    public function addJs($js)
    {
        if (!in_array($js, $this->js))
            $this->js[] = $js;

        return $this;
    }

    public function addVendorCss($css)
    {
        if (!in_array($css, $this->vendor_css))
            $this->vendor_css[] = $css;

        return $this;
    }

    public function addVendorJs($js)
    {
        if (!in_array($js, $this->vendor_js))
            $this->vendor_js[] = $js;

        return $this;
    }
}