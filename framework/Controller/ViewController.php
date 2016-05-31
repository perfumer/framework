<?php

namespace Perfumer\Framework\Controller;

class ViewController extends AbstractController
{
    /**
     * @var bool
     */
    protected $_rendering = true;

    protected function after()
    {
        if ($this->getRendering()) {
            $content = $this->getView()->render();

            $this->getResponse()->setContent($content);
        }

        parent::after();
    }

    /**
     * @return bool
     */
    protected function getRendering()
    {
        return $this->_rendering;
    }

    /**
     * @param bool $rendering
     */
    protected function setRendering($rendering)
    {
        $this->_rendering = (bool) $rendering;
    }
}
