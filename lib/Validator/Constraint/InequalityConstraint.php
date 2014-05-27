<?php

namespace Perfumer\Validator\Constraint;

use Perfumer\Validator\Exception\ConstraintException;

class InequalityConstraint extends AbstractConstraint
{
    protected $more;
    protected $less;
    protected $min;
    protected $max;

    public function __construct(array $options)
    {
        if (isset($options['more']))
            $this->more = (int) $options['more'];

        if (isset($options['less']))
            $this->less = (int) $options['less'];

        if (isset($options['min']))
            $this->min = (int) $options['min'];

        if (isset($options['max']))
            $this->max = (int) $options['max'];

        if ($this->more === null && $this->less === null && $this->min === null && $this->max === null)
            throw new ConstraintException('You did not specified neither "more/less", nor "min/max" parameters for InequalityConstraint.');

        if ($this->more !== null && $this->less !== null)
        {
            $this->setMessage('validator.inequality.more_less')->addPlaceholders([':more' => $this->more, ':less' => $this->less]);
        }
        elseif ($this->more !== null && $this->max !== null)
        {
            $this->setMessage('validator.inequality.more_max')->addPlaceholders([':more' => $this->more, ':max' => $this->max]);
        }
        elseif ($this->min !== null && $this->less !== null)
        {
            $this->setMessage('validator.inequality.min_less')->addPlaceholders([':min' => $this->min, ':less' => $this->less]);
        }
        elseif ($this->min !== null && $this->max !== null)
        {
            $this->setMessage('validator.inequality.min_max')->addPlaceholders([':min' => $this->min, ':max' => $this->max]);
        }
        elseif ($this->more !== null)
        {
            $this->setMessage('validator.inequality.more')->addPlaceholders([':more' => $this->more]);
        }
        elseif ($this->less !== null)
        {
            $this->setMessage('validator.inequality.less')->addPlaceholders([':less' => $this->less]);
        }
        elseif ($this->min !== null)
        {
            $this->setMessage('validator.inequality.min')->addPlaceholders([':min' => $this->min]);
        }
        elseif ($this->max !== null)
        {
            $this->setMessage('validator.inequality.max')->addPlaceholders([':max' => $this->max]);
        }
    }

    public function validate($value)
    {
        $status = true;

        if ($this->more !== null && $value <= $this->more)
            $status = false;

        if ($this->less !== null && $value >= $this->less)
            $status = false;

        if ($this->min !== null && $value < $this->min)
            $status = false;

        if ($this->max !== null && $value > $this->max)
            $status = false;

        return $status;
    }
}