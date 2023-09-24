<?php

namespace Genius257\View;

use PHPHtmlParser\Dom;
//use PHPHtmlParser\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\HtmlNode;
use PHPHtmlParser\Options;
use Genius257\View\Dom\Parser;
use Exception;

class View
{
    /**
     * View file path.
     *
     * @var string
     */
    protected $view;

    /**
     * Resolved view file path.
     *
     * @var string;
     */
    protected $resolvedView;

    /**
     * View file content.
     *
     * @var string
     */
    protected $viewContent;

    /**
     * Render method output cache.
     *
     * @var string|null
     */
    protected $viewCache;

    /**
     * Initialize a new View class instance.
     *
     * @param string $viewFilePath view file path
     */
    public function __construct(string $viewFilePath)
    {
        $resolvedView = stream_resolve_include_path($viewFilePath);

        if ($resolvedView === false) {
            throw new Exception("View file not found: {$viewFilePath}");
        }

        $this->view = $viewFilePath;
        $this->resolvedView = $resolvedView;
        $this->viewContent = $this->requireToVar($this->resolvedView);
    }

    /**
     * Parse view html content.
     *
     * @param string $html view html content
     *
     * @return Dom
     */
    public function parse(string $html): Dom
    {
        $dom = new Dom(new Parser());

        //TODO: add support for user provided options.
        $options = new Options();
        $options->setCleanupInput(false);
        $options->setRemoveDoubleSpace(false);
        $options->setPreserveLineBreaks(true);

        $dom->setOptions(
            // this is set as the global option level.
            $options
        );

        $dom->loadStr($html);
        foreach ($dom->getChildren() as $child) {
            $newChild = $this->processNode($child);
            if ($newChild !== $child) {
                $dom->root->replaceChild(
                    $child->id(),
                    $newChild
                );
            }
        }

        return $dom;
    }

    /**
     * Process DOM node.
     *
     * @param mixed $node
     *
     * @return \PHPHtmlParser\Dom\Node\HtmlNode|HtmlNode
     */
    protected function processNode($node)
    {
        if (!($node instanceof HtmlNode)) {
            return $node;
        }
        try {
            foreach ($node->getChildren() as $child) {
                $newChild = $this->processNode($child);
                if ($newChild !== $child) {
                    $node->replaceChild(
                        $child->id(),
                        $newChild
                    );
                }
            }

            $className = $node->rawTag();

            try {
                if (!class_exists($className)) {
                    return $node;
                }
            } catch (\Throwable $throwable) {
                $exception = new ProcessNodeException(
                    $throwable,
                    realpath($this->resolvedView),
                    $className ?? $node->tag->name(),
                    is_null($node ?? null) ? null : $node->getLocation()
                );
                throw $exception;
            }

            $class = new $className();

            foreach ($node->getAttributes() as $attributeKey => $attributeValue) {
                $class->{$attributeKey} = $attributeValue;
            }

            $html = strval($class);

            try {
                $resolvedView = $this->resolvedView;
                $rc = new \ReflectionClass($class);
                $this->resolvedView = $rc->getFileName();
                $node = $this->parse($html);
            } finally {
                $this->resolvedView = $resolvedView;
            }
        } catch (\Throwable $throwable) {
            $exception = new ProcessNodeException(
                $throwable,
                realpath($this->resolvedView),
                $className ?? $node->tag->name(),
                is_null($node ?? null) ? null : $node->getLocation()
            );
            throw $exception;
        }

        return $node->root;
    }

    /**
     * Get processed view content as a string.
     *
     * @return string
     */
    public function render()
    {
        return $this->viewCache = $this->viewCache ?? $this->parse($this->viewContent)->outerHtml;
    }

    /**
     * Reset cache and get processed view content as a string.
     *
     * @return string
     */
    public function forceRender()
    {
        $this->viewCache = null;
        return $this->render();
    }

    /**
     * Get the output from a php evaluated file.
     *
     * @param string $file file path
     *
     * @return string
     */
    public function requireToVar($file)
    {
        ob_start();
        require($file);
        return (string) ob_get_clean();
    }

    /**
     * Renders the component and returns the resulting content.
     */
    public static function renderComponent(Component $component): string
    {
        ob_start();

        $level = ob_get_level();

        $returnContent = $component->render();
        $objectBufferContent = ob_get_contents();

        if ($objectBufferContent === false) {
            throw new Exception("ob_get_contents() failed in Component::_render");
        }

        if ($level <> ob_get_level()) {
            throw new Exception("output buffer nesting level mismatch. Expected: " . $level . ", got: " . ob_get_level());
        }

        ob_end_clean();

        if ($returnContent === null) {
            $returnContent = "";
        } elseif ($returnContent instanceof Stringable) {
            $returnContent = $returnContent->__toString();
        }

        if ($component->trim) {
            $returnContent = trim($returnContent);
            $objectBufferContent = trim($objectBufferContent);
        }
        return $objectBufferContent . $returnContent;
    }
}
