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
     * Get component property value, or null if non existent.
     *
     * @return mixed|null
     */
    public function getProperty(string $property)
    {
        return $this->getProperties()[$property] ?? null;
    }

    /**
     * Check if the component supports a property by name.
     *
     * @param string $property
     *
     * @return boolean
     */
    public function hasProperty(string $property)
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Magic method for property setters support.
     *
     * @param string             $method
     * @param array<int, mixed>  $arguments
     *
     * @return $this
     */
    public function __call(string $method, $arguments)
    {
        if (!preg_match('/^set([A-Z].*)$/', $method, $matches)) {
            throw new \Exception("Call to undefined method \"$method\"");
        }

        $property = lcFirst($matches[1]);

        if (!array_key_exists($property, $this->properties)) {
            $className = get_class($this);
            throw new \Exception("Component property \"$property\" is not defined on \"$className\"");
        }

        $this->properties[$property] = $arguments[0] ?? null;

        return $this;
    }

    /**
     * Get *REAL* HTMLNode children.
     *
     * *REAL*, meaning that child components would produce a root HTMLNode,
     * making changes to the child data appear as not working in some cases.
     *
     * @return HtmlNode[]
     */
    public function getHTMLNodeChildren()
    {
        $HTMLNodes = [];
        $children  = $this->properties['children'] ?? [];
        foreach ($children as $child) {
            if ($child instanceof RootNode) {
                foreach ($child->getChildren() as $child) {
                    if ($child instanceof HtmlNode) {
                        $HTMLNodes[] = $child;
                    }
                }
            } elseif ($child instanceof HtmlNode) {
                $HTMLNodes[] = $child;
            }
        }

        return $HTMLNodes;
    }

    /**
     * Renders each child and returns the concatenated strings.
     *
     * @return string
     */
    public function renderChildren(): string
    {
        return implode(
            '',
            array_map(
                function ($child) {
                    return strval($child);
                },
                $this->properties['children'] ?? []
            )
        );
    }

    /**
     * Renders the component and returns the resulting string.
     *
     * @return string|Stringable
     */
    public function render()
    {
        ob_start();
        //TODO: support the _render also being able to return value, but throw warning if neither ob_get_contents or the return value are empty!
        $return = $this->_render();
        $result = ob_get_contents();
        ob_end_clean();
        if ($return !== null) {
            if ($result !== "" && $result !== false) {
                $className = str_replace("\0", "", get_class($this));
                throw new \ErrorException("component $className::_render produced content to the output buffer AND returned a non null value", 0, E_WARNING);
            } else {
                $result = $return;
            }
        }
        if ($this->trim) {
            $result = trim($result);
        }
        return $result;
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
