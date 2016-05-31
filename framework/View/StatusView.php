<?php

namespace Perfumer\Framework\View;

class StatusView extends SerializeView
{
    /**
     * StatusView constructor.
     * @param mixed $serializer
     */
    public function __construct($serializer = null)
    {
        parent::__construct($serializer);

        $this->addVars([
            'status' => true,
            'message' => '',
            'content' => null
        ]);

        $this->addGroup('errors');
    }
}
