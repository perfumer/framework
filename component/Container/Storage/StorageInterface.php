<?php
namespace Perfumer\Component\Container\Storage;

interface StorageInterface
{
    public function setParam($group, $name, $value);

    public function getParamGroup($group);

    public function setParamGroup($group, array $values);

    public function addParamGroup($group, array $values);

    public function deleteParamGroup($group, array $keys = []);
}