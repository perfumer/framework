<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Draft extends Attribute
{
}