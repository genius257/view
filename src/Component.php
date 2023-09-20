<?php

namespace Genius257\View;

use PHPHtmlParser\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;
use Stringable;

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
    protected $trim = true;

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
     * @return \Stringable|string|void
     */
    abstract protected function _render();

    public function __toString(): string
    {
        return $this->render();
    }
}
