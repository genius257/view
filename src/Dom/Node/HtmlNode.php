<?php

namespace Genius257\View\Dom\Node;

use PHPHtmlParser\Dom\Node\HtmlNode as PHPHtmlParserHtmlNode;
use PHPHtmlParser\Dom\Tag;

class HtmlNode extends PHPHtmlParserHtmlNode {
    /**
     * The raw tag name string
     *
     * @var string
     */
    protected $rawTag;

    /**
     * @param Tag|string  $tag
     * @param string|null $rawTag
     */
    public function __construct($tag, $rawTag = null)
    {
        $this->rawTag = $rawTag ?? ($tag instanceof Tag ? $tag->name() : $tag);
        parent::__construct($tag);
    }

    public function rawTag() {
        return $this->rawTag;
    }
}
