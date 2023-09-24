<?php

namespace Tests\Dom;

use Genius257\View\Dom\Node\RootNode;
use Genius257\View\Dom\Parser;
use PHPHtmlParser\Content;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @covers \Genius257\View\Dom\Parser::getLocation
     */
    public function testGetLocation()
    {
        $parser = new Parser();

        $content = new Content("a\nb\nc");

        $location = $parser->getLocation($content);
        $this->assertSame(0, $location->getOffset());
        $this->assertSame(1, $location->getLine());
        $this->assertSame(0, $location->getColumn());

        $content->copyUntil('b');
        $content->fastForward(1);

        $location = $parser->getLocation($content);
        $this->assertSame(3, $location->getOffset());
        $this->assertSame(2, $location->getLine());
        $this->assertSame(1, $location->getColumn());
    }

    /**
     * @covers \Genius257\View\Dom\Parser::parse
     */
    public function testParse()
    {
        $parser = new Parser();

        $html = '<div><span>test</div></span><>';

        $parsed = $parser->parse(new Options(), new Content($html), 10);

        $this->assertInstanceOf(RootNode::class, $parsed);

        $this->assertSame(
            '<div><span>test</span></div>',
            $parsed->outerHtml()
        );
    }
}
