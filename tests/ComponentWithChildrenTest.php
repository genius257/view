<?php

namespace Tests;

use Genius257\View\Component;
use Genius257\View\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;
use Genius257\View\Stringable;
use Genius257\View\View;
use PHPHtmlParser\Dom\Node\TextNode;
use PHPUnit\Framework\TestCase;
use Genius257\View\ComponentWithChildren;

class ComponentWithChildrenTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function createComponentWithChildren()
    {
        $component = new class () extends ComponentWithChildren
        {
            public function render()
            {
                return "test";
            }
        };

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

    /**
     * @covers \Genius257\View\ComponentWithChildren::childToString
     */
    public function testChildToStringWithHtmlNode()
    {
        $htmlNode = new \PHPHtmlParser\Dom\Node\HtmlNode('div');
        $htmlNode->addChild(new TextNode('HtmlNode test'));
        $this->assertEquals('<div>HtmlNode test</div>', ComponentWithChildren::childToString($htmlNode));
    }

    /**
     * @covers \Genius257\View\ComponentWithChildren::childToString
     */
    public function testChildToStringWithComponent()
    {
        $component = new class () extends Component {
            public $trim = false;

            public function render()
            {
                echo 'component';
                return ' test';
            }
        };
        $this->assertEquals('component test', ComponentWithChildren::childToString($component));
    }

    /**
     * @covers \Genius257\View\ComponentWithChildren::childToString
     */
    public function testChildToStringWithStringable()
    {
        $stringable = new class () implements Stringable
        {
            public function __toString(): string
            {
                return 'stringable test';
            }
        };
        $this->assertEquals('stringable test', ComponentWithChildren::childToString($stringable));
    }

    /**
     * @covers \Genius257\View\ComponentWithChildren::childToString
     */
    public function testChildToStringWithString()
    {
        $string = "string test";
        $this->assertEquals($string, ComponentWithChildren::childToString($string));
    }

    /**
     * @covers \Genius257\View\ComponentWithChildren::renderChildren
     */
    public function testRenderChildren()
    {
        $component = $this->createComponentWithChildren();

        $this->assertEquals("<a></a><b></b>test<c><d></d><e></e></c>", $component->renderChildren());
    }

    /**
     * Test that items that are not itanceof HtmlNode are filtered out
     * and RootNode instances are excluded, but RootNode children gets extracted.
     *
     * @covers \Genius257\View\ComponentWithChildren::getHTMLNodeChildren
     */
    public function testGetHTMLNodeChildren()
    {
        $component = $this->createComponentWithChildren();

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
}
