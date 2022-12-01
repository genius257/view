<?php

namespace Genius257\View\Dom\Node;

/**
 * This class only exists to check if a HtmlNode is a root node or not.
 * This could have been fixed with a property on the AbstractNode class,
 * but that requires too much code, if not done on the original code base.
 */
class RootNode extends HtmlNode
{

}
