<?php

namespace Perfumer\Component\Assets;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinPlusFilter;

class Core
{
    protected $source_path;
    protected $web_path;
    protected $combine;
    protected $minify;
    protected $version;

    protected $vendor_css = [];
    protected $vendor_js = [];
    protected $css = [];
    protected $js = [];

    public function __construct(array $params)
    {
        $this->source_path = '/' . trim($params['source_path'], '/');
        $this->web_path = '/' . trim($params['web_path'], '/');
        $this->combine = (bool) $params['combine'];
        $this->minify = (bool) $params['minify'];

        if (isset($params['version']))
            $this->version = $params['version'];
    }

    public function getCss()
    {
        $vendors = [];

        foreach ($this->vendor_css as $css)
            $vendors[] = '/vendor/' . $css . '.css';

        $stylesheets = [];

        foreach ($this->css as $css)
            $stylesheets[] = '/css/' . $css . '.css';

        if ($this->combine)
        {
            $combined_file_path = '/css/' . substr(md5(serialize($stylesheets)), 0, 10);

            if ($this->version)
                $combined_file_path .= '.' . $this->version;

            $combined_file_path .= '.css';

            $this->combineFiles($stylesheets, $combined_file_path, 'css');

            $stylesheets = [$combined_file_path];
        }
        else
        {
            $this->copyFiles($stylesheets, 'css');
        }

        return array_merge($vendors, $stylesheets);
    }

    public function getJs()
    {
        $vendors = [];

        foreach ($this->vendor_js as $js)
            $vendors[] = '/vendor/' . $js . '.js';

        $javascripts = [];

        foreach ($this->js as $js)
            $javascripts[] = '/js/' . $js . '.js';

        if ($this->combine)
        {
            $combined_file_path = '/js/' . substr(md5(serialize($javascripts)), 0, 10);

            if ($this->version)
                $combined_file_path .= '.' . $this->version;

            $combined_file_path .= '.js';

            $this->combineFiles($javascripts, $combined_file_path, 'js');

            $javascripts = [$combined_file_path];
        }
        else
        {
            $this->copyFiles($javascripts, 'js');
        }

        return array_merge($vendors, $javascripts);
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

    protected function copyFiles(array $files, $type)
    {
        $asset_manager = new AssetManager();

        foreach ($files as $i => $file)
        {
            $asset = new FileAsset($this->source_path . $file);
            $asset->setTargetPath($file);

            if ($this->minify)
            {
                switch ($type)
                {
                    case 'css':
                        $asset->ensureFilter(new CssMinFilter());
                        break;
                    case 'js':
                        $asset->ensureFilter(new JSMinPlusFilter());
                        break;
                }
            }

            $asset_manager->set('asset' . $i, $asset);
        }

        $writer = new AssetWriter($this->web_path);
        $writer->writeManagerAssets($asset_manager);
    }

    protected function combineFiles(array $files, $target_path, $type)
    {
        if (!file_exists($this->web_path . $target_path))
        {
            $asset_collection = new AssetCollection();

            foreach ($files as $file)
            {
                $asset = new FileAsset($this->source_path . $file);

                $asset_collection->add($asset);
            }

            // Set minify filters if this option activated
            if ($this->minify)
            {
                switch ($type)
                {
                    case 'css':
                        $asset_collection->ensureFilter(new CssMinFilter());
                        break;
                    case 'js':
                        $asset_collection->ensureFilter(new JSMinPlusFilter());
                        break;
                }
            }

            $content = $asset_collection->dump();

            file_put_contents($this->web_path . $target_path, $content);
        }
    }
}