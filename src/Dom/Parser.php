<?php

namespace Genius257\View\Dom;

use PHPHtmlParser\Dom\Parser as PHPHtmlParserParser;
use PHPHtmlParser\Content;
use PHPHtmlParser\Dom\Tag;
use PHPHtmlParser\Dom\Node\AbstractNode;
use Genius257\View\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\RootNode;
use PHPHtmlParser\Dom\Node\TextNode;
use PHPHtmlParser\DTO\TagDTO;
use PHPHtmlParser\Enum\StringToken;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Options;

class Parser extends PHPHtmlParserParser
{
    /**
     * Get current parser cursor location.
     *
     * @param Content $content
     *
     * @return Location
     *
     * @throws \Exception If an unexpected issue with a regular expression occurs.
     */
    public function getLocation(Content $content)
    {
        $position = $content->getPosition();

        $reflectionClass = new \ReflectionClass($content);
        $reflectionProperty = $reflectionClass->getProperty('content');
        $reflectionProperty->setAccessible(true);

        /**
         * @var string
         */
        $stringContent = $reflectionProperty->getValue($content);

        if (preg_match_all('/\n/', substr($stringContent, 0, $position), $matches, PREG_OFFSET_CAPTURE) === false) {
            throw new \Exception("Unexpected regular expression failure");
        }

        return new Location(count($matches[0]) + 1, end($matches[0])[1] ?? 0, $position);
    }

    /**
     * @inheritdoc
     */
    public function parse(Options $options, Content $content, int $size): AbstractNode
    {
        // add the root node
        $root = new RootNode('root');
        $root->setHtmlSpecialCharsDecode($options->isHtmlSpecialCharsDecode());
        $activeNode = $root;
        while ($activeNode !== null) {
            if ($activeNode && $activeNode->tag->name() === 'script'
                && $options->isCleanupInput() !== true
            ) {
                $str = $content->copyUntil('</');
            } else {
                $str = $content->copyUntil('<');
            }
            if ($str == '') {
                $tagDTO = $this->parseTag($options, $content, $size);
                if (!$tagDTO->isStatus()) {
                    // we are done here
                    $activeNode = null;
                    continue;
                }

                // check if it was a closing tag
                if ($tagDTO->isClosing()) {
                    $foundOpeningTag = true;
                    $originalNode = $activeNode;
                    while ($activeNode->getTag()->name() != $tagDTO->getTag()) {
                        $activeNode = $activeNode->getParent();
                        if ($activeNode === null) {
                            // we could not find opening tag
                            $activeNode = $originalNode;
                            $foundOpeningTag = false;
                            break;
                        }
                    }
                    if ($foundOpeningTag) {
                        $activeNode = $activeNode->getParent();
                    }
                    continue;
                }

                if ($tagDTO->getNode() === null) {
                    continue;
                }

                /** @var \PHPHtmlParser\Dom\Node\HtmlNode|null $node */
                $node = $tagDTO->getNode();
                $activeNode->addChild($node);

                // check if node is self closing
                if (!$node->getTag()->isSelfClosing()) {
                    $activeNode = $node;
                }
            } elseif ($options->isWhitespaceTextNode() ||
                \trim($str) != ''
            ) {
                // we found text we care about
                $textNode = new TextNode($str, $options->isRemoveDoubleSpace());
                $textNode->setHtmlSpecialCharsDecode($options->isHtmlSpecialCharsDecode());
                $activeNode->addChild($textNode);
            }
        }

        return $root;
    }

    /**
     * @inheritdoc
     *
     * @param Options $options
     * @param Content $content
     * @param int $size The max string length of the content
     */
    private function parseTag(Options $options, Content $content, int $size): TagDTO
    {
        if ($content->char() != '<') {
            // we are not at the beginning of a tag
            return TagDTO::makeFromPrimitives();
        }

        // check if this is a closing tag
        try {
            $content->fastForward(1);
        } catch (ContentLengthException $exception) {
            // we are at the end of the file
            return TagDTO::makeFromPrimitives();
        }
        if ($content->char() == '/') {
            return $this->makeEndTag($content, $options);
        }
        $rawTag = null;
        if ($content->char() == '?') {
            // special setting tag
            $tag = $content->fastForward(1)
                ->copyByToken(StringToken::SLASH(), true);
            $tag = (new Tag($tag))
                ->setOpening('<?')
                ->setClosing(' ?>')
                ->selfClosing();
        } elseif($content->string(3) == '!--') {
            // comment tag
            $tag = $content->fastForward(3)
                ->copyByToken(StringToken::CLOSECOMMENT(), false);
            $tag = (new Tag($tag))
                ->setOpening('<!--')
                ->setClosing('-->')
                ->selfClosing();
        } else {
            $location = $this->getLocation($content);
            $tag = ($content->copyByToken(StringToken::SLASH(), true));
            $rawTag = $tag;
            $tag = \strtolower($tag);
            if (\trim($tag) == '') {
                // no tag found, invalid < found
                return TagDTO::makeFromPrimitives();
            } elseif ($tag === '!doctype') {
                $tag = (new Tag($rawTag))
                    ->noTrailingSlash()
                    ->selfClosing();
            }
        }
        $node = new HtmlNode($tag, $rawTag, $location ?? null);
        $node->setHtmlSpecialCharsDecode($options->isHtmlSpecialCharsDecode());
        $this->setUpAttributes($content, $size, $node, $options, $tag);

        $content->skipByToken(StringToken::BLANK());
        if ($content->char() == '/') {
            // self closing tag
            $node->getTag()->selfClosing();
            $content->fastForward(1);
        } elseif (\in_array($node->getTag()->name(), $options->getSelfClosing(), true)) {
            // Should be a self closing tag, check if we are strict
            if ($options->isStrict()) {
                $character = $content->getPosition();
                throw new StrictException("Tag '" . $node->getTag()->name() . "' is not self closing! (character #$character)");
            }

            // We force self closing on this tag.
            $node->getTag()->selfClosing();

            // Should this tag use a trailing slash?
            if (\in_array($node->getTag()->name(), $options->getNoSlash(), true)) {
                $node->getTag()->noTrailingSlash();
            }
        }

        if ($content->canFastForward(1)) {
            $content->fastForward(1);
        }

        return TagDTO::makeFromPrimitives(true, false, $node);
    }

    /**
     * Set available attributes from current tag via content, to the node instance.
     *
     * @inheritdoc
     *
     * @param Content $content
     * @param int $size
     * @param HtmlNode $node
     * @param Options $options
     * @param string|Tag $tag
     *
     * @return void
     */
    private function setUpAttributes(Content $content, int $size, HtmlNode $node, Options $options, $tag): void
    {
        while (
            $content->char() != '>' &&
            $content->char() != '/'
        ) {
            $space = $content->skipByToken(StringToken::BLANK(), true);
            if (empty($space)) {
                try {
                    $content->fastForward(1);
                } catch (ContentLengthException $exception) {
                    // reached the end of the content
                    break;
                }
                continue;
            }

            $name = $content->copyByToken(StringToken::EQUAL(), true);
            if ($name == '/') {
                break;
            }

            if (empty($name)) {
                $content->skipByToken(StringToken::BLANK());
                continue;
            }

            $content->skipByToken(StringToken::BLANK());
            if ($content->char() == '=') {
                $content->fastForward(1)
                    ->skipByToken(StringToken::BLANK());
                switch ($content->char()) {
                    case '"':
                        $content->fastForward(1);
                        $string = $content->copyUntil('"', true);
                        do {
                            $moreString = $content->copyUntilUnless('"', '=>');
                            $string .= $moreString;
                        } while (\strlen($moreString) > 0 && $content->getPosition() < $size);
                        $content->fastForward(1);
                        $node->getTag()->setAttribute($name, $string);
                        break;
                    case "'":
                        $content->fastForward(1);
                        $string = $content->copyUntil("'", true);
                        do {
                            $moreString = $content->copyUntilUnless("'", '=>');
                            $string .= $moreString;
                        } while (\strlen($moreString) > 0 && $content->getPosition() < $size);
                        $content->fastForward(1);
                        $node->getTag()->setAttribute($name, $string, false);
                        break;
                    default:
                        $node->getTag()->setAttribute($name, $content->copyByToken(StringToken::ATTR(), true));
                        break;
                }
            } else {
                // no value attribute
                if ($options->isStrict()) {
                    // can't have this in strict html
                    $character = $content->getPosition();
                    throw new StrictException("Tag '$tag' has an attribute '$name' with out a value! (character #$character)");
                }
                $node->getTag()->setAttribute($name, null);
                if ($content->char() != '>') {
                    $content->rewind(1);
                }
            }
        }
    }

    private function makeEndTag(Content $content, Options $options): TagDTO
    {
        $tag = $content->fastForward(1)
            ->copyByToken(StringToken::SLASH(), true);
        // move to end of tag
        $content->copyUntil('>');
        $content->fastForward(1);

        // check if this closing tag counts
        $tag = \strtolower($tag);
        if (\in_array($tag, $options->getSelfClosing(), true)) {
            return TagDTO::makeFromPrimitives(true);
        }

        return TagDTO::makeFromPrimitives(true, true, null, \strtolower($tag));
    }
}
