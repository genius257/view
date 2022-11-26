<?php

namespace Genius257\View\Dom\Node;

use Genius257\View\Dom\Location;
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
     * The position at the beginning of the tag
     *
     * @var Location|null
     */
    protected $location;

    /**
     * @param Tag|string  $tag
     * @param string|null $rawTag
     * @param Location|null $location
     */
    public function __construct($tag, $rawTag = null, $location = null)
    {
        $this->rawTag = $rawTag ?? ($tag instanceof Tag ? $tag->name() : $tag);
        $this->location = $location;
        parent::__construct($tag);
    }

    public function rawTag() {
        return $this->rawTag;
    }

    /**
     * @return Location|null
     */
    public function getLocation()
    {
        return $this->location;
    }
}
