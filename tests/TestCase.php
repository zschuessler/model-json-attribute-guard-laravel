<?php

namespace Zschuessler\ModelJsonAttributeGuard\Test;

use Illuminate\Foundation\Application;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Call protected or private method on object
     *
     * @param object $object
     * @param string $methodName
     * @param mixed  $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected static function callProtectedMethod($object, $methodName, $args)
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, (array) $args);
    }

    /**
     * Get protected or private property of an object
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected static function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
