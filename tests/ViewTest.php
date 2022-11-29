<?php

namespace Tests;

use Genius257\View\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase {
    public function createView() {
        return new View(__DIR__.'/testData/view.php');
    }

    /*
    public function testFromFileWithAbsolutePath() {
        View::fromFile(__DIR__.'/view/basic.php');
    }

    public function testFromFileWithRelativePath() {
        View::fromFile('../view/basic.php');
    }
    */

    public function testParse() {
        $view = $this->createView();
        $dom = $view->parse('<a href="link.to/somewhere"><Tests\testData\Component>link text</Tests\testData\Component></a>');

        $this->assertEquals('<a href="link.to/somewhere"><component></component></a>', $dom->__toString());
    }

    public function testRender() {
        $view = $this->createView();
        $this->assertEquals("<!DOCTYPE html>\n<html>\n    <head></head>\n    <body>\n        text <span>content</span>\n        <br />\n        <component></component>\n    </body>\n</html>\n", $view->render());
    }

    /**
     * Test that the render method uses the render cache.
     *
     * This method checks that a view will use the viewCache
     * on subsequent render calls, ignoring the viewContent
     * after first render.
     */
    public function testRenderWithCache() {
        $view = $this->createView();

        $expected = $view->render();

        $reflectionClass = new \ReflectionClass($view);
        $reflectionProperty = $reflectionClass->getProperty('viewContent');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($view, '123');

        $this->assertEquals($expected, $view->render());
    }

    public function testForceRender() {
        $view = $this->createView();
        
        $expected = $view->forceRender();

        $this->assertEquals($expected, $view->render());

        $reflectionClass = new \ReflectionClass($view);
        $reflectionProperty = $reflectionClass->getProperty('viewContent');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($view, '123');

        // The cache is expected to be used here
        $this->assertEquals($expected, $view->render());

        $this->assertEquals('123', $view->forceRender());

        // The cache is expected to have been replaced based on the new viewContent content.
        $this->assertEquals('123', $view->render());
    }

    public function testRequireToVar() {
        $view = $this->createView();
        $data = $view->requireToVar(__DIR__.'/testData/view.php');
        $this->assertEquals("<!DOCTYPE html>\n<html>\n    <head></head>\n    <body>\n        text <span>content</span>\n        <br />\n        <Tests\\testData\\Component />\n    </body>\n</html>\n", $data);
    }
}
