<?php
namespace Perfumer\Component\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ModelException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Model does not exist'
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Model exists'
        ]
    ];
}