<?php

namespace Genius257\View;

use Genius257\View\Dom\Node\RootNode;
use PHPHtmlParser\Dom\Node\HtmlNode;

abstract class ComponentWithChildren extends Component
{
    /** @var array<int, HtmlNode|Component|Stringable|string> */
    public array $children = [];

    /**
     * @param HtmlNode|Component|Stringable|string $child
     */
    public function childToString($child): string
    {
        if ($child instanceof Stringable) {
            return $child->__toString();
        }

        if ($child instanceof HtmlNode) {
            return $child->__toString();
        }

        if ($child instanceof Component) {
            return View::renderComponent($child);
        }

        return strval($child);
    }

    /**
     * Renders each child and returns the concatenated strings.
     *
     * @return string
     */
    public function renderChildren(): string
    {
        return implode(
            '',
            array_map(
                function ($child) {
                    return strval($child);
                },
                $this->children
            )
        );
    }

    /**
     * Get *REAL* HTMLNode children.
     *
     * *REAL*, meaning that child components would produce a root HTMLNode,
     * making changes to the child data appear as not working in some cases.
     *
     * @return HtmlNode[]
     */
    public function getHTMLNodeChildren()
    {
        $HTMLNodes = [];
        foreach ($this->children as $child) {
            if ($child instanceof RootNode) {
                foreach ($child->getChildren() as $child) {
                    if ($child instanceof HtmlNode) {
                        $HTMLNodes[] = $child;
                    }
                }
            } elseif ($child instanceof HtmlNode) {
                $HTMLNodes[] = $child;
            }
        }

        return $HTMLNodes;
    }
}
