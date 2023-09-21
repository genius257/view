<?php

namespace Genius257\View;

/**
 * @method $this setChildren(array $value)
 */
abstract class Component
{
    /**
     * If true, the render output will be stripped of whitespace chars from the beginning and end of a string.
     *
     * @var boolean
     */
    public $trim = true;

    final public function __construct()
    {
    }

    /**
     * Create a new instance of the component.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * Get the available properties and their values.
     *
     * @return array<string, mixed>
     */
    public function getProperties()
    {
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];

        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }

        return $result;
    }

    /**
     * Render component content.
     * @return Stringable|string|void
     */
    abstract public function render();

    public function __toString(): string
    {
        return View::renderComponent($this);
    }
}
