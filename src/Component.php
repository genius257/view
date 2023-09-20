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
        return $this->properties;
    }

    /**
     * Render component content.
     * @return Stringable|string|void
     */
    abstract public function render();

    public function __toString(): string
    {
        $rendered = View::renderComponent($this);

        if ($rendered instanceof Stringable) {
            return $rendered->__toString();
        }

        return $rendered;
    }
}
