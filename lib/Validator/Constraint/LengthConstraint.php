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

    public function getMessage()
    {
        if ($this->exact !== null)
            return ['validator.length.exact', [':exact' => $this->exact]];

        if ($this->min !== null && $this->max !== null)
            return ['validator.length.minmax', [':min' => $this->min, ':max' => $this->max]];

        if ($this->min !== null)
            return ['validator.length.min', [':min' => $this->min]];

        if ($this->max !== null)
            return ['validator.length.max', [':max' => $this->max]];
    }
}