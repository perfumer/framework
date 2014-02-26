<?php

namespace Perfumer;

class Assets
{
    protected $source_dir;
    protected $cache_dir;
    protected $web_path;

    protected $css = [];
    protected $js = [];

    public function __construct(array $params)
    {
        $this->source_dir = rtrim($params['source_dir'], '/');
        $this->cache_dir = rtrim($params['cache_dir'], '/');
        $this->web_path = rtrim($params['web_path'], '/');
    }

    public function getCSS()
    {
        $array = [];

        foreach ($this->css as $css)
            $array[] = $this->web_path . '/css/' . $css;

        return $array;
    }

    public function getJS()
    {
        $array = [];

        foreach ($this->js as $js)
            $array[] = $this->web_path . '/js/' . $js;

        return $array;
    }

    public function addCSS($css)
    {
        $target_file = $this->cache_dir . '/css/' . $css;

        @unlink($target_file);

        $target_dir = explode('/', $target_file);
        $target_dir = array_slice($target_dir, 0, count($target_dir) - 1);
        $target_dir = implode('/', $target_dir);
        @mkdir($target_dir, 0777, true);

        @copy($this->source_dir . '/css/' . $css, $target_file);

        if (!in_array($css, $this->css))
            $this->css[] = $css;

        return $this;
    }

    public function addJS($js)
    {
        $target_file = $this->cache_dir . '/js/' . $js;

        @unlink($target_file);

        $target_dir = explode('/', $target_file);
        $target_dir = array_slice($target_dir, 0, count($target_dir) - 1);
        $target_dir = implode('/', $target_dir);
        @mkdir($target_dir, 0777, true);

        @copy($this->source_dir . '/js/' . $js, $target_file);

        if (!in_array($js, $this->js))
            $this->js[] = $js;

        return $this;
    }
}