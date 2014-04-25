<?php

namespace Perfumer;

class Assets
{
    protected $vendor_path;
    protected $source_path;
    protected $web_path;

    protected $vendor_css = [];
    protected $vendor_js = [];
    protected $css = [];
    protected $js = [];

    public function __construct(array $params)
    {
        $this->vendor_path = '/' . trim($params['vendor_path'], '/') . '/';
        $this->source_path = '/' . trim($params['source_path'], '/') . '/';
        $this->web_path = '/' . trim($params['web_path'], '/') . '/';
    }

    public function getCss()
    {
        $array = [];

        foreach ($this->vendor_css as $css)
            $array[] = '/vendor/' . $css . '.css';

        foreach ($this->css as $css)
            $array[] = '/css/' . $css . '.css';

        return $array;
    }

    public function getJs()
    {
        $array = [];

        foreach ($this->vendor_js as $js)
            $array[] = '/vendor/' . $js . '.js';

        foreach ($this->js as $js)
            $array[] = '/js/' . $js . '.js';

        return $array;
    }

    public function addCss($css)
    {
        if (!in_array($css, $this->css))
            $this->css[] = $css;

        $file = 'css/' . $css . '.css';

        @unlink($this->web_path . $file);

        $this->copyFile($file, $this->source_path, $this->web_path);

        return $this;
    }

    public function addJs($js)
    {
        if (!in_array($js, $this->js))
            $this->js[] = $js;

        $file = 'js/' . $js . '.js';

        @unlink($this->web_path . $file);

        $this->copyFile($file, $this->source_path, $this->web_path);

        return $this;
    }

    public function addVendorCss($css)
    {
        if (!in_array($css, $this->vendor_css))
            $this->vendor_css[] = $css;

        $file = $css . '.css';

        @unlink($this->vendor_path . $file);

        $this->copyFile($file, $this->vendor_path, $this->web_path . 'vendor/');

        return $this;
    }

    public function addVendorJs($js)
    {
        if (!in_array($js, $this->vendor_js))
            $this->vendor_js[] = $js;

        $file = $js . '.js';

        @unlink($this->vendor_path . $file);

        $this->copyFile($file, $this->vendor_path, $this->web_path . 'vendor/');

        return $this;
    }

    protected function copyFile($file, $source_dir, $target_dir)
    {
        $reversed_file = strrev($file);
        $slash_pos = strpos($reversed_file, '/');

        if ($slash_pos !== false)
        {
            $reversed_dir = substr($reversed_file, $slash_pos);
            $dir = strrev($reversed_dir);

            @mkdir($target_dir . $dir, 0777, true);
        }

        copy($source_dir . $file, $target_dir . $file);
    }
}