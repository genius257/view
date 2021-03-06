<?php

namespace Genius257\View;

use PHPHtmlParser\Dom;
//use PHPHtmlParser\Dom\Node\HtmlNode;
use Genius257\View\Dom\Node\HtmlNode;
use PHPHtmlParser\Options;
use Genius257\View\Dom\Parser;

//TODO: handle exceptions, to show better trace (HTML position) if it fails.

class View {
    protected $view;
    protected $viewContent;
    protected $viewCache;

    public function __construct(string $view) {
        $this->view = $view;
        $this->viewContent = $this->requireToVar(stream_resolve_include_path($this->view) ? $this->view : $this->view.'.php');
    }

    public function parse(string $html) : Dom {
        $dom = new Dom(new Parser());

        //TODO: add support for user provided options.
        $options = new Options();
        $options->setCleanupInput(false);

        $dom->setOptions(
            // this is set as the global option level.
            $options
        );

        $dom->loadStr($html);
        foreach($dom->getChildren() as $child) {
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

    protected function processNode($node) {
        if (!($node instanceof HtmlNode)) {
            return $node;
        }
        foreach($node->getChildren() as $child) {
            $newChild = $this->processNode($child);
            if ($newChild !== $child) {
                $node->replaceChild(
                    $child->id(),
                    $newChild
                );
            }
        }

        $className = $node->rawTag();

        if (!class_exists($className)) {
            return $node;
        }

        $class = new $className();

        foreach ($node->getAttributes() as $attributeKey => $attributeValue) {
            $setter = "set".ucfirst($attributeKey);
            $class->$setter($attributeValue);
        }

        if (count($node->getChildren()) > 0) {
            if ($class->hasProperty('children') || method_exists($class, 'setChildren')) {
                $class->setChildren($node->getChildren());
            } else {
                trigger_error("children are passed to $className but are not supported by the component", E_USER_WARNING);//TODO: verbose level
            }
        }

        $html = strval($class);

        $node = $this->parse($html);

        return $node->root;
    }

    public function render() {
        return $this->viewCache = $this->viewCache ?? $this->parse($this->viewContent)->outerHtml;
    }

    public function forceRender() {
        $this->viewCache = null;
        return $this->render();
    }

    public function requireToVar($file) {
        ob_start();
        require($file);
        return ob_get_clean();
    }
}