# Components

## Usage

To use components, simply create a new class that extends from `Genius257\View\Component` and implement the `render` method.

### Attributes

Attributes for a view component are set via public properties.

All components have a reserved property named `"trim"`. It is a boolean value, indicating if the render post process should trim the output.

### Render

The component render output is given via the `render` method.

The method can give output via:
* A return statement.
* Writing to the output buffer, for example via the echo function.

If both a return and content in the output buffer is given, the final render will be the a concatinated string, with output buffer first, return output second.

#### Stringable
casting this class to string, will result in the same as making the View class render the component.

#### Nested render
The component MAY return or output html with another component tag.

That component tag will also be processed in the same view render call, until all component tags are processed.

### Component tags

Component tags are html elements, where the HTML tag are the entire class reference string (Full namespace included)

Component tags MAY have children, if they extend from the ComponentWithChildren class.

example:

```HTML
<My\Application\Menu>
    <span>nested content</span> here
</My\Application\Menu>

<br />

<My\Application\Message />
```
