<?php

namespace Genius257\View;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Options;
use Genius257\View\Dom\Parser;

//TODO: handle exceptions, to show better trace (HTML position) if it fails.

class View {
    protected $view;
    protected $viewContent;
    protected $viewCache;

    public function __construct(string $view) {
        $this->view = $view;
        $this->viewContent = $this->requireToVar($this->view.'.php');
    }

    public function parse(string $html) : Dom {
        $dom = new Dom(new Parser());

        $options = new Options();
        $options->setCleanupInput(false);

        $dom->setOptions(
            // this is set as the global option level.
            $options
        );

        $dom->loadStr($html);
        //var_dump($dom->root->getChildren()[0]->tag->name());
        //var_dump($dom->root->getChildren()[0]->getChildren()[1]->tag);
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

        $className = $node->tag->name();

        if (!class_exists($className)) {
            return $node;
        }

        $class = new $className();

        //FIXME: 

        foreach ($node->getAttributes() as $attributeKey => $attributeValue) {
            $setter = "set".ucfirst($attributeKey);
            $class->$setter($attributeValue);
        }

        if (count($node->getChildren()) > 0 && $class->hasProperty('children')) {
            //TODO: should we throw warning if children are defined, but no children supported on the class?
            $class->setChildren($node->getChildren());
        }

        $html = strval($class);

        $node = $this->parse($html);

        return $node->root;
    }

    public function render() {
        return $this->viewCache = $this->viewCache ?? $this->parse($this->viewContent)->outerHtml;
    }

    public function forceRender() {
        $this->$viewCache = null;
        return $this->render();
    }

    public function requireToVar($file) {
        ob_start();
        require($file);
        return ob_get_clean();
    }
}