<?php

namespace Tests\Dom;

use Genius257\View\Dom\Location;
use Genius257\View\Dom\Node\HtmlNode;
use PHPUnit\Framework\TestCase;

class HtmlNodeTest extends TestCase
{
    /**
     * @covers \Genius257\View\Dom\Node\HtmlNode::rawTag
     * @covers \Genius257\View\Dom\Node\HtmlNode::__construct
     */
    public function testRawTag()
    {
        $tag = 'DIV';
        $node = new HtmlNode($tag, $tag, null);
        $this->assertEquals($tag, $node->rawTag());

        $tag = new \PHPHtmlParser\Dom\Tag('tag-name');
        $node = new HtmlNode($tag, null, null);
        $this->assertEquals('tag-name', $node->rawTag());

        $node = new HtmlNode($tag, 'raw-tag-name', null);
        $this->assertEquals('raw-tag-name', $node->rawTag());
    }

    /**
     * @covers \Genius257\View\Dom\Node\HtmlNode::getLocation
     * @covers \Genius257\View\Dom\Node\HtmlNode::__construct
     */
    public function testLocationGetter()
    {
        $tag = 'DIV';
        $location = new Location(1, 2, 3);
        $node = new HtmlNode($tag, $tag, $location);

        $this->assertSame($location, $node->getLocation());
    }
}
