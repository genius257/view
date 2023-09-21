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

    public function createComponentWithChildren()
    {
        $component = $this->createComponent();

        $nodeA = new HtmlNode("a");
        $nodeB = new \PHPHtmlParser\Dom\Node\HtmlNode("b");
        $rootNode = new RootNode('c');
        $nodeD = new HtmlNode('d');
        $nodeE = new HtmlNode('e');

        $rootNode->addChild($nodeD);
        $rootNode->addChild($nodeE);

        $component->children = [
            $nodeA,
            $nodeB,
            "test",
            $rootNode,
        ];

        return $component;
    }

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

    public function testMake()
    {
        $component = $this->createComponent();

        $this->assertInstanceOf(get_class($component), $component::make());

        $this->expectError();
        $this->expectErrorMessage("Cannot instantiate abstract class Genius257\View\Component");

        Component::make();
    }

    public function testGetProperties()
    {
        $component = $this->createComponent();

        $this->assertEquals(
            ['style' => 'display:none;', 'src' => '/image.png', 'children' => []],
            $component->getProperties()
        );
    }

    public function testGetProperty()
    {
        $component = $this->createComponent();

        $this->assertEquals('display:none;', $component->style);
        $this->assertEquals('/image.png', $component->src);
    }

    /**
     * Test that items that are not itanceof HtmlNode are filtered out
     * and RootNode instances are excluded, but RootNode children gets extracted.
     * @return void
     */
    public function testGetHTMLNodeChildren()
    {
        $component = $this->createComponent();

        $nodeA = new HtmlNode("a");
        $nodeB = new \PHPHtmlParser\Dom\Node\HtmlNode("b");
        $rootNode = new RootNode('c');
        $nodeD = new HtmlNode('d');
        $nodeE = new HtmlNode('e');

        $rootNode->addChild($nodeD);
        $rootNode->addChild($nodeE);

        $component->children = [
            $nodeA,
            $nodeB,
            "test",
            $rootNode,
        ];

        $children = $component->getHTMLNodeChildren();

        $this->assertCount(4, $children);
        $this->assertEquals([$nodeA, $nodeB, $nodeD, $nodeE], $children);
    }

    public function testRenderChildren()
    {
        $component = $this->createComponentWithChildren();

        $this->assertEquals("<a></a><b></b>test<c><d></d><e></e></c>", $component->renderChildren());
    }

    public function testRender()
    {
        $component = $this->createComponentWithChildren();

        $this->assertEquals("xyz", View::renderComponent($component));
    }

    public function testRenderWarningWithTwoOutputSources()
    {
        $this->markTestSkipped('TODO: implement');
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches(
            '/^component .*::render produced content to the output buffer AND returned a non null value$/'
        );

        $component = new class extends Component {
            public function render()
            {
                echo "xyz";
                return "xyz";
            }
        };

        $component->render();
    }
}
