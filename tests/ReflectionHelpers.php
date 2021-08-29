<?php
namespace dbx12\yii2MockDatabase\tests;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Trait ReflectionHelpers
 *
 * Adds methods for invoking inaccessible methods as well as getting+setting inaccessible properties.
 */
trait ReflectionHelpers
{
    /**
     * Invokes a inaccessible method
     *
     * @param object|string $objectOrClassFqn Either an object (for non-static calls) or a FQN of a class (static calls)
     * @param string        $method           Name of the method to call
     * @param array         $args             Arguments to the called method
     * @param bool          $revoke           whether to make method inaccessible after execution
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod($objectOrClassFqn, string $method, array $args = [], bool $revoke = true): mixed
    {
        $info       = $this->processObjectOrClassFqn($objectOrClassFqn);
        $reflection = $info['reflection'];
        $object     = $info['object'];
        $method     = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }
        return $result;
    }

    /**
     * Sets an inaccessible object property to a designated value
     *
     * @param object|string $objectOrClassFqn Either an object (for non-static calls) or a FQN of a class (static calls)
     * @param string        $propertyName
     * @param               $value
     * @param bool          $revoke           whether to make property inaccessible after setting
     * @throws \ReflectionException
     */
    public function setInaccessibleProperty($objectOrClassFqn, string $propertyName, $value, $revoke = true): void
    {
        $info       = $this->processObjectOrClassFqn($objectOrClassFqn);
        $reflection = $info['reflection'];
        $object     = $info['object'];
        while (!$reflection->hasProperty($propertyName)) {
            $reflection = $reflection->getParentClass();
            if (!$reflection) {
                throw new ReflectionException(
                    "Failed to find the property $propertyName in the class or any parent classes"
                );
            }
        }
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        if ($object !== null) {
            // non-static property
            $property->setValue($object, $value);
        } else {
            // static property
            $property->setValue($value);
        }
        if ($revoke) {
            $property->setAccessible(false);
        }
    }

    /**
     * Gets an inaccessible object property
     *
     * @param object|string $objectOrClassFqn Either an object (for non-static calls) or a FQN of a class (static calls)
     * @param               $propertyName
     * @param bool          $revoke           whether to make property inaccessible after getting
     * @return mixed
     * @throws \ReflectionException
     */
    public function getInaccessibleProperty($objectOrClassFqn, $propertyName, bool $revoke = true): mixed
    {
        $info       = $this->processObjectOrClassFqn($objectOrClassFqn);
        $reflection = $info['reflection'];
        $object     = $info['object'];
        while (!$reflection->hasProperty($propertyName)) {
            $reflection = $reflection->getParentClass();
        }
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        if ($revoke) {
            $property->setAccessible(false);
        }
        return $result;
    }


    /**
     * Processes a variable which either holds an object or a FQN for a class. Creates an instance of ReflectionClass
     * reflecting the given object or class.
     *
     * @param object|string $objectOrClassFqn Either an object (for non-static calls) or a FQN of a class (static calls)
     * @return array ['class' => Class FQN, 'object' => Object (or null), 'reflection' => instance of ReflectionClass]
     * @throws \ReflectionException
     */
    protected function processObjectOrClassFqn($objectOrClassFqn): array
    {
        $returnValue = [
            'class'      => null,
            'object'     => null,
            'reflection' => null,
        ];
        if (is_object($objectOrClassFqn)) {
            $returnValue['class']  = get_class($objectOrClassFqn);
            $returnValue['object'] = $objectOrClassFqn;
        } elseif (is_string($objectOrClassFqn)) {
            $returnValue['class']  = $objectOrClassFqn;
            $returnValue['object'] = null;
        } else {
            throw new InvalidArgumentException(
                '$objectOrClass must be an object or FQN of a class, got ' . gettype($objectOrClassFqn)
            );
        }
        $returnValue['reflection'] = new ReflectionClass($returnValue['class']);
        return $returnValue;
    }
}
