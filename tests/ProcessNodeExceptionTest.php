<?php

namespace Tests;

use Genius257\View\Dom\Location;
use Genius257\View\ProcessNodeException;
use PHPUnit\Framework\TestCase;

class ProcessNodeExceptionTest extends TestCase
{
    public function testTraceWithLocation()
    {
        $exception = new \Exception();
        $location = new Location(123, 456, 789);
        $processNodeException = new ProcessNodeException($exception, 'viewFile', 'className', $location);

        $expected = $exception->getTrace();
        $expected[0]['file'] = 'viewFile';
        $expected[0]['line'] = 123;
        $expected[0]['function'] = 'render';
        $expected[0]['class'] = 'className';
        $expected[0]['type'] = '::';

        $this->assertEquals($expected, $processNodeException->getTrace());
    }

    public function testTraceWithoutLocation()
    {
        $exception = new \Exception();
        $processNodeException = new ProcessNodeException($exception, 'viewFile', 'className', null);

        $expected = $exception->getTrace();
        $expected[0]['file'] = 'viewFile';
        $expected[0]['line'] = 0;
        $expected[0]['function'] = 'render';
        $expected[0]['class'] = 'className';
        $expected[0]['type'] = '::';

        $this->assertEquals($expected, $processNodeException->getTrace());
    }
}
