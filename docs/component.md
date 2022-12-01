# Components

## Usage

To use components, simply create a new class that extends from `Genius257\View\Component` and implement the `_render` method.

### Attributes

Attributes for a view component are set via the protected property `$properties`.

It contains an associative array with the key being the attribute name and the value being the default value.

Components inherit attributes from parents, even when the `$properties` class property value is changed on the child class definition.

All components have a reserved property key named `"children"`. It contains an array of nested elements within the component html tag.

### Render

The component render output is given via the `_render` method.

The method can return one of:
  * a string
  * Nothing but write directly to PHP output, for example via the echo function.

But NOT both.

#### Stringable
casting this class to string, will result in the same as a `render` method call on the component

#### Nested render

The component MAY return or output html with another component tag.

That component tag will also be processed in the same view render call, until all component tags are processed.

### Component tags

Component tags are html elements, where the HTML tag are the entire class reference string (Full namespace included)

Component tags MAY have children, if supported by the component referenced.

example:

```HTML
<My\Application\Menu>
    <span>nested content</span> here
</My\Application\Menu>

<br />

<My\Application\Message />
```
