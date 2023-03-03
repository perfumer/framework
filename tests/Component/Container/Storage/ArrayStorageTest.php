<?php

namespace Tests\Component\Container\Storage;

use Perfumer\Component\Container\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;

class ArrayStorageTest extends TestCase
{
    public function testGetParamReturnsValueForExistingResourceAndName()
    {
        // Arrange
        $storage = new ArrayStorage();
        $resource = 'example';
        $name = 'greeting';
        $value = 'Hello, world!';
        $storage->addResources([$resource => [$name => $value]]);

        // Act
        $result = $storage->getParam($resource, $name);

        // Assert
        $this->assertEquals($value, $result);
    }

    public function testGetParamReturnsDefaultValueForNonexistentResource()
    {
        // Arrange
        $storage = new ArrayStorage();
        $resource = 'example';
        $name = 'greeting';
        $value = 'Hello, world!';

        // Act
        $result = $storage->getParam($resource, $name, $value);

        // Assert
        $this->assertEquals($value, $result);
    }

    public function testGetParamReturnsDefaultValueForNonexistentNameInExistingResource()
    {
        // Arrange
        $storage = new ArrayStorage();
        $resource = 'example';
        $name = 'greeting';
        $value = 'Hello, world!';
        $storage->addResources([$resource => []]);

        // Act
        $result = $storage->getParam($resource, $name, $value);

        // Assert
        $this->assertEquals($value, $result);
    }

    public function testGetResourceReturnsArrayForExistingName()
    {
        // Arrange
        $storage = new ArrayStorage();
        $name = 'example';
        $value = ['greeting' => 'Hello, world!'];
        $storage->addResources([$name => $value]);

        // Act
        $result = $storage->getResource($name);

        // Assert
        $this->assertEquals($value, $result);
    }

    public function testGetResourceReturnsEmptyArrayForNonexistentName()
    {
        // Arrange
        $storage = new ArrayStorage();
        $name = 'example';

        // Act
        $result = $storage->getResource($name);

        // Assert
        $this->assertEquals([], $result);
    }

    public function testSaveParamThrowsException()
    {
        // Arrange
        $storage = new ArrayStorage();
        $this->expectException(\Perfumer\Component\Container\Exception\ContainerException::class);

        // Act
        $storage->saveParam('example', 'greeting', 'Hello, world!');
    }

    public function testAddResourcesMergesExistingValuesWithNewValues()
    {
        // Arrange
        $storage = new ArrayStorage();
        $name = 'example';
        $values1 = ['greeting' => 'Hello, world!'];
        $values2 = ['greeting' => 'Bonjour!'];
        $expected = ['greeting' => 'Bonjour!'];
        $storage->addResources([$name => $values1]);

        // Act
        $storage->addResources([$name => $values2]);
        $result = $storage->getResource($name);

        // Assert
        $this->assertEquals($expected, $result);
    }
}
