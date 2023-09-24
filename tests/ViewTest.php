<?php

namespace Tests;

use Genius257\View\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function createView()
    {
        return new View(__DIR__ . '/testData/view.php');
    }

    /*
    public function testFromFileWithAbsolutePath()
    {
        View::fromFile(__DIR__.'/view/basic.php');
    }

    public function testFromFileWithRelativePath()
    {
        View::fromFile('../view/basic.php');
    }
    */

    /**
     * @covers \Genius257\View\View::__construct
     */
    public function testConstruct()
    {
        $view = $this->createView();

        // This test only checks that the constructor works, not that it does anything.
        $this->assertEquals(1, 1);
    }

    /**
     * @covers \Genius257\View\View::__construct
     */
    public function testConstructWithInvalidFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('View file not found: ' . __DIR__ . '/view/does/not/exist.php');

        new View(__DIR__ . '/view/does/not/exist.php');
    }

    /**
     * @covers \Genius257\View\View::parse
     */
    public function testParse()
    {
        $view = $this->createView();
        $dom = $view->parse(
            '<a href="link.to/somewhere"><Tests\testData\Component>link text</Tests\testData\Component></a>'
        );

        $this->assertEquals('<a href="link.to/somewhere"><component></component></a>', $dom->__toString());
    }

    /**
     * @covers \Genius257\View\View::render
     */
    public function testRender()
    {
        $view = $this->createView();
        $this->assertEquals(
            "<!DOCTYPE html>\n<html>\n    <head></head>\n    <body>\n        text <span>content</span>\n        <br />\n        <component></component>\n    </body>\n</html>\n",
            $view->render()
        );
    }

    /**
     * Test that the render method uses the render cache.
     *
     * This method checks that a view will use the viewCache
     * on subsequent render calls, ignoring the viewContent
     * after first render.
     *
     * @covers \Genius257\View\View::render
     */
    public function testRenderWithCache()
    {
        $view = $this->createView();

        $expected = $view->render();

        $reflectionClass = new \ReflectionClass($view);
        $reflectionProperty = $reflectionClass->getProperty('viewContent');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($view, '123');

        $this->assertEquals($expected, $view->render());
    }

    /**
     * @covers \Genius257\View\View::forceRender
     */
    public function testForceRender()
    {
        $view = $this->createView();

        $expected = $view->forceRender();

        $this->assertEquals($expected, $view->render());

        $reflectionClass = new \ReflectionClass($view);
        $reflectionProperty = $reflectionClass->getProperty('viewContent');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($view, '123');

        // The cache is expected to be used here
        $this->assertEquals($expected, $view->render());

        $this->assertEquals('123', $view->forceRender());

        // The cache is expected to have been replaced based on the new viewContent content.
        $this->assertEquals('123', $view->render());
    }

    /**
     * @covers \Genius257\View\View::requireToVar
     */
    public function testRequireToVar()
    {
        $view = $this->createView();
        $data = $view->requireToVar(__DIR__ . '/testData/view.php');
        $this->assertEquals(
            "<!DOCTYPE html>\n<html>\n    <head></head>\n    <body>\n        text <span>content</span>\n        <br />\n        <Tests\\testData\\Component data-extra=\"value\" />\n    </body>\n</html>\n",
            $data
        );
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponentWithTrim()
    {
        $component = new class () extends \Genius257\View\Component {
            public $trim = true;

            public function render()
            {
                echo ' some ';
                return ' content ';
            }
        };

        $this->assertEquals("somecontent", View::renderComponent($component));

        $component->trim = false;
        $this->assertEquals(" some  content ", View::renderComponent($component));
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponent()
    {
        $component = new class () extends \Genius257\View\Component {

            public function render()
            {
                echo 'abc';
                return 'def';
            }
        };

        $this->assertEquals(
            "abcdef",
            View::renderComponent($component)
        );
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponentWithClosedObjectBuffer()
    {
        $output_buffer_level = ob_get_level();

        $component = new class () extends \Genius257\View\Component {
            public function render()
            {
                // phpunit and other may add additional ob levels
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ob_get_contents() failed in Component::_render');

        try {
            View::renderComponent($component);
        } finally {
            // restore ob levels
            while (ob_get_level() < $output_buffer_level) {
                ob_start();
            }
        }
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponentWithMismatchedObjectBufferLevel()
    {
        $component = new class () extends \Genius257\View\Component {
            public function render()
            {
                ob_end_clean();
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('output buffer nesting level mismatch. Expected: 2, got: 1');

        View::renderComponent($component);
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponentWithoutReturnFromRender()
    {
        $component = new class () extends \Genius257\View\Component {
            public function render()
            {
            }
        };

        $this->assertEquals(
            "",
            View::renderComponent($component)
        );
    }

    /**
     * @covers \Genius257\View\View::renderComponent
     */
    public function testRenderComponentWithStringableReturnFromRender()
    {
        $component = new class () extends \Genius257\View\Component {
            public function render()
            {
                return new class () implements \Genius257\View\Stringable {
                    public function __toString()
                    {
                        return 'abc';
                    }
                };
            }
        };

        $this->assertEquals(
            "abc",
            View::renderComponent($component)
        );
    }
}
