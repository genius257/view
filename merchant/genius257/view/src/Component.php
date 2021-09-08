<?php

namespace Genius257\View;

/**
 * @method $this setChildren(array $value)
 */
abstract class Component {
    /**
     * If true, the render output will be stripped of whitespace chars from the beginning and end of a string.
     * @var bool
     */
    protected $trim = true;

    protected $properties = [
        'children' => null,
    ];

    public static function make() {
        return new static();
    }

    public function getProperties() {
        return $this->properties;
    }

    public function getProperty(string $property) {
        return $this->getProperties()[$property] ?? null;
    }

    public function hasProperty(string $property) {
        return array_key_exists($property, $this->properties);
    }

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

    public function renderChildren() : string {
        return implode('', array_map(function ($child) {
            return strval($child);
        }, $this->properties['children'] ?? []));
    }

    public function render() : string {
        ob_start();
        $this->_render();
        $result = ob_get_contents();
        ob_end_clean();
        if ($this->trim) {
            $result = trim($result);
        }
        return $result;
    }

    /**
     * Render component content.
     * @return Stringable
     */
    abstract protected function _render();

    public function __toString() : string {
        return $this->render();
    }
}