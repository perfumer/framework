<?php
namespace Perfumer\Component\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class UniqueException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Must be unique value'
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Must not be unique value'
        ]
    ];
}