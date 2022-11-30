<?php

namespace Genius257\View;

use PHPHtmlParser\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;

/**
 * @method $this setChildren(array $value)
 */
abstract class Component {
    /**
     * Cache for getInitialProperties.
     *
     * A map of resolved initial class properties.
     */
    private static $propertiesCache = [];

    /**
     * Returns a merged array of properties defined on the parent class hierarchy.
     *
     * @return array parent classes properties merged.
     */
    private static function processInitialProperties() {
        $properties = [];

        $reflectionClass = new \ReflectionClass(static::class);
        while (is_subclass_of($reflectionClass->name, self::class) && $parent = $reflectionClass->getParentClass()) {
            $reflectionProperty = $parent->getProperty('properties');
            $reflectionProperty->setAccessible(true);
            $properties += $reflectionProperty->getValue($parent->newInstanceWithoutConstructor());
            $reflectionClass = $parent;
        }

        return self::$propertiesCache[static::class] = $properties;
    }

    private static function getInitialProperties() {
        if (isset(self::$propertiesCache[static::class])) {
            return self::$propertiesCache[static::class];
        }

        return static::processInitialProperties();
    }

    /**
     * If true, the render output will be stripped of whitespace chars from the beginning and end of a string.
     * @var bool
     */
    protected $trim = true;

    /**
     * The supported attributes on the component.
     * 
     * all keys in this list will have a setter method available
     * named set followed by the property name with first letter uppercase.
     *
     * @var array
     */
    protected $properties = [
        'children' => null,
    ];

    /** Creates a new View Component class instace */
    public function __construct() {
        $this->properties += self::getInitialProperties();
    }

    /**
     * Create a new instance of the component.
     * 
     * @return static
     */
    public static function make() {
        return new static();
    }

    /**
     * Get the available properties and their values.
     *
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * Get component property value, or null if non existent.
     *
     * @return mixed|null
     */
    public function getProperty(string $property) {
        return $this->getProperties()[$property] ?? null;
    }

    /**
     * Check if the component supports a property by name.
     *
     * @param string $property
     *
     * @return bool
     */
    public function hasProperty(string $property) {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Magic method for property setters support.
     *
     * @return $this
     */
    public function __call(string $method, $value) {
        if (!preg_match('/^set([A-Z].*)$/', $method, $matches)) {
            throw new \Exception("Call to undefined method \"$method\"");
        }

        $property = lcFirst($matches[1]);

        if (!array_key_exists($property, $this->properties)) {
            $className = get_class($this);
            throw new \Exception ("Component property \"$property\" is not defined on \"$className\"");
        }

        $this->properties[$property] = $value[0] ?? null;

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
    public function getHTMLNodeChildren() {
        $HTMLNodes = [];
        $children = $this->properties['children'] ?? [];
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
    public function renderChildren() : string {
        return implode('', array_map(function ($child) {
            return strval($child);
        }, $this->properties['children'] ?? []));
    }

    /**
     * Renders the component and returns the resulting string.
     */
    public function render() : string {
        ob_start();
        //TODO: support the _render also being able to return value, but throw warning if neither ob_get_contents or the return value are empty!
        $return = $this->_render();
        $result = ob_get_contents();
        ob_end_clean();
        if ($return !== null) {
            if ($result !== "" && $result !== false) {
                $className = get_class($this);
                trigger_error("component $className::_render produced content to the output buffer AND returned a non null value", E_USER_WARNING);
                $result.= $return;
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

    public function __toString() : string {
        return $this->render();
    }
}
