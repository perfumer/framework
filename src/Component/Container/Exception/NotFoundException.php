<?php

namespace Perfumer\Component\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * ContainerException
 * Main exception class for Container.
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface {}
