<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\Controller\Exception\FragmentException;

class FragmentController extends CoreController
{
    protected $_fragments = [];
    protected $_fragment_serializer;

    protected function registerFragment($name, $default = null)
    {
        $this->_fragments[$name] = $default;
    }

    protected function getFragment($name)
    {
        if (!array_key_exists($name, $this->_fragments))
            throw new FragmentException('I are trying to retrieve value of the fragment "' . $name . '", which is not registered yet.');

        return $this->_fragments[$name];
    }

    protected function setFragment($name, $value)
    {
        if (!array_key_exists($name, $this->_fragments))
            throw new FragmentException('I are trying to set value of the fragment "' . $name . '", which is not registered yet.');

        $this->_fragments[$name] = $value;
    }

    protected function setFragmentAndExit($name, $value)
    {
        $this->setFragment($name, $value);

        throw new ExitActionException;
    }

    protected function addElementToFragment($name, $key, $value)
    {
        if (!array_key_exists($name, $this->_fragments))
            throw new FragmentException('I are trying to add an element to the fragment "' . $name . '", which is not registered yet.');

        if ($key === null)
        {
            $this->_fragments[$name][] = $value;
        }
        else
        {
            $this->_fragments[$name][$key] = $value;
        }
    }

    protected function addElementToFragmentAndExit($name, $key, $value)
    {
        $this->addElementToFragment($name, $key, $value);

        throw new ExitActionException;
    }

    protected function addElementsToFragment($name, array $values)
    {
        if (!array_key_exists($name, $this->_fragments))
            throw new FragmentException('I are trying to add elements to the fragment "' . $name . '", which is not registered yet.');

        $this->_fragments[$name] = array_merge($this->_fragments[$name], $values);
    }

    protected function addElementsToFragmentAndExit($name, array $values)
    {
        $this->addElementsToFragment($name, $values);

        throw new ExitActionException;
    }

    protected function hasElementsInFragment($name)
    {
        if (!array_key_exists($name, $this->_fragments))
            throw new FragmentException('I are trying to check existence of elements in the fragment "' . $name . '", which is not registered yet.');

        return count($this->_fragments[$name]) > 0;
    }

    protected function setFragmentSerializer($serializer)
    {
        $this->_fragment_serializer = $serializer;
    }

    protected function serializeFragments()
    {
        $serializer = $this->_fragment_serializer === null ? $this->getContainer()->p('proxy.fragment_serializer') : $this->_fragment_serializer;
        $data = '';

        if ($serializer === 'json')
        {
            $data = json_encode($this->_fragments);
        }
        elseif (is_callable($serializer))
        {
            $data = $serializer($this->_fragments);
        }

        return $data;
    }
}