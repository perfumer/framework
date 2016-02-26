<?php

namespace Perfumer\Framework\View;

class SerializeView extends AbstractView
{
    /**
     * @var mixed
     */
    protected $serializer;

    /**
     * SerializeView constructor.
     * @param mixed $serializer
     */
    public function __construct($serializer = null)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function render()
    {
        if ($this->serializer === 'json') {
            $data = json_encode($this->vars);
        } elseif (is_callable($this->serializer)) {
            $data = $this->serializer($this->vars);
        } else {
            $data = serialize($this->vars);
        }

        return $data;
    }
}
