<?php

namespace Tests\Dom;

use Genius257\View\Dom\Location;
use Genius257\View\Dom\Node\HtmlNode;
use PHPUnit\Framework\TestCase;

class HtmlNodeTest extends TestCase
{
    public function testRawTag()
    {
        $tag = 'DIV';
        $node = new HtmlNode($tag, $tag, null);

        $this->assertEquals($tag, $node->rawTag());
    }

    public function testLocationGetter()
    {
        $tag = 'DIV';
        $location = new Location(1, 2, 3);
        $node = new HtmlNode($tag, $tag, $location);

        $this->assertSame($location, $node->getLocation());
    }
}
