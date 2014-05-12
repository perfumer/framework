<?php

namespace Perfumer\Validator\Constraint;

use Perfumer\Validator\Exception\ConstraintException;

class LengthConstraint extends AbstractConstraint
{
    protected $exact;
    protected $min;
    protected $max;

    public function __construct(array $options)
    {
        if (isset($options['exact']))
            $this->exact = (int) $options['exact'];

        if (isset($options['min']))
            $this->min = (int) $options['min'];

        if (isset($options['max']))
            $this->max = (int) $options['max'];

        if ($this->exact === null && $this->min === null && $this->max === null)
            throw new ConstraintException('You did not specified neither "exact", nor "min" or "max" parameters for LengthConstraint.');

        if ($this->exact !== null)
        {
            $this->setMessage('validator.length.exact')->addPlaceholders([':exact' => $this->exact]);
        }
        elseif ($this->min !== null && $this->max !== null)
        {
            $this->setMessage('validator.length.minmax')->addPlaceholders([':min' => $this->min, ':max' => $this->max]);
        }
        elseif ($this->min !== null)
        {
            $this->setMessage('validator.length.min')->addPlaceholders([':min' => $this->min]);
        }
        elseif ($this->max !== null)
        {
            $this->setMessage('validator.length.max')->addPlaceholders([':max' => $this->max]);
        }
    }

    public function validate($value)
    {
        $length = mb_strlen($value, 'utf-8');

        $status = true;

        if ($this->exact !== null && $length !== $this->exact)
            $status = false;

        if ($this->min !== null && $length < $this->min)
            $status = false;

        if ($this->max !== null && $length > $this->max)
            $status = false;

        return $status;
    }
}