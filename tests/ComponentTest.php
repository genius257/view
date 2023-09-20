<?php

namespace Tests;

use ErrorException;
use Genius257\View\Component;
use Genius257\View\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    public function createComponent()
    {
        return new class () extends Component
        {
            protected $properties = [
                'children' => [],
                'style' => 'display:none;',
                'src' => '/image.png',
            ];

            public function _render()
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

        $component->setChildren([
            $nodeA,
            $nodeB,
            "test",
            $rootNode,
        ]);

        return $component;
    }

    public function testTrim()
    {
        $component = new class () extends Component {
            protected $trim = false;

            public function setTrim(bool $trim): bool
            {
                return $this->trim = $trim;
            }

            public function _render()
            {
                return "  a  b  c  \t\n";
            }
        };

        $this->assertEquals("  a  b  c  \t\n", $component->render());

        $component->setTrim(true);

        $this->assertEquals("a  b  c", $component->render());
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

        $this->assertEquals(['style' => 'display:none;', 'src' => '/image.png', 'children' => []], $component->getProperties());
    }

    public function testGetProperty()
    {
        $component = $this->createComponent();

        $this->assertEquals('display:none;', $component->getProperty('style'));
        $this->assertEquals('/image.png', $component->getProperty('src'));

        // missing properties, should by default return null
        $this->assertEquals(null, $component->getProperty('missing'));
    }

    public function testHasProperty()
    {
        $component = $this->createComponent();

        $this->assertTrue($component->hasProperty('style'));
        $this->assertTrue($component->hasProperty('src'));
        $this->assertFalse($component->hasProperty('missing'));
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

        $component->setChildren([
            $nodeA,
            $nodeB,
            "test",
            $rootNode,
        ]);

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

        $this->assertEquals("xyz", $component->render());
    }

    public function testRenderWarningWithTwoOutputSources()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches('/^component .*::_render produced content to the output buffer AND returned a non null value$/');

        $component = new class extends Component {
            public function _render()
            {
                echo "xyz";
                return "xyz";
            }
        };

        $component->render();
    }

    public function testMagicSetterMethods()
    {
        $component = $this->createComponent();

        $setterReturn = $component->setChildren(["child"]);

        // Assert that the default setter magic method logic returns $this, allowing call chaining.
        $this->assertEquals($component, $setterReturn);

        // Assert that the value given to the magic setter method, was applied as the actual new property value.
        $this->assertEquals(["child"], $component->getProperty('children'));
    }

    public function testGuardsMagicSetterMethods()
    {
        $component = $this->createComponent();

        $component->setSrc("test");

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Component property "missing" is not defined on "%s"', get_class($component)));

        $component->setMissing();
    }

    public function testGuardMagicCallUndefinedMethod()
    {
        $component = $this->createComponent();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Call to undefined method "missing"');

        $component->missing();
    }
}
