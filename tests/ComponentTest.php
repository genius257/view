<?php

namespace Tests;

use ErrorException;
use Genius257\View\Component;
use Genius257\View\ComponentWithChildren;
use Genius257\View\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;
use Genius257\View\View;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function createComponent()
    {
        return new class () extends ComponentWithChildren
        {
            public $style = 'display:none;';
            public $src = '/image.png';

            public function render()
            {
                return "xyz";
            }
        };
    }

    /**
     * @covers \Genius257\View\Component
     */
    public function testTrim()
    {
        $component = new class () extends Component {
            public $trim = false;

            public function render()
            {
                return "  a  b  c  \t\n";
            }
        };

        $this->assertEquals("  a  b  c  \t\n", View::renderComponent($component));

        $component->trim = true;

        $this->assertEquals("a  b  c", View::renderComponent($component));
    }

    /**
     * @covers \Genius257\View\Component::make
     */
    public function testMake()
    {
        $component = $this->createComponent();

        $this->assertInstanceOf(get_class($component), $component::make());
    }

    /**
     * @covers \Genius257\View\Component::getProperties
     */
    public function testGetProperties()
    {
        $component = $this->createComponent();

        $this->assertEquals(
            [
                'style' => 'display:none;',
                'src' => '/image.png',
                'children' => [],
                'trim' => true,
            ],
            $component->getProperties()
        );
    }

    /**
     * @covers \Genius257\View\Component::render
     */
    public function testRender()
    {
        $component = $this->createComponent();

        $this->assertEquals("xyz", View::renderComponent($component));
    }

    /**
     * @covers \Genius257\View\Component::render
     */
    public function testRenderWithTwoOutputSources()
    {
        $component = new class extends Component {
            public function render()
            {
                echo "xyz";
                return "xyz";
            }
        };

        $this->assertEquals("xyzxyz", View::renderComponent($component));
    }

    /**
     * @covers \Genius257\View\Component::__toString
     */
    public function testToString()
    {
        $component = $this->createComponent();

        $this->assertEquals("xyz", $component->__toString());
    }
}
