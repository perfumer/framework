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
        @unlink($this->cache_dir . '/css/' . $css);
        @copy($this->source_dir . '/css/' . $css, $this->cache_dir . '/css/' . $css);

        if (!in_array($css, $this->css))
            $this->css[] = $css;

        return $this;
    }

    public function addJS($js)
    {
        @unlink($this->cache_dir . '/js/' . $js);
        @copy($this->source_dir . '/js/' . $js, $this->cache_dir . '/js/' . $js);

        if (!in_array($js, $this->js))
            $this->js[] = $js;

        return $this;
    }
}