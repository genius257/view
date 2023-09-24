<?php

namespace Tests\Dom;

use Genius257\View\Dom\Location;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    /**
     * @covers \Genius257\View\Dom\Location::getLine
     * @covers \Genius257\View\Dom\Location::__construct
     */
    public function testLineGetter()
    {
        $location = new Location(1, 2, 3);

        $this->assertEquals(1, $location->getLine());
    }

    /**
     * @covers \Genius257\View\Dom\Location::getColumn
     * @covers \Genius257\View\Dom\Location::__construct
     */
    public function testColumnGetter()
    {
        $location = new Location(1, 2, 3);

        $this->assertEquals(2, $location->getColumn());
    }

    /**
     * @covers \Genius257\View\Dom\Location::getOffset
     * @covers \Genius257\View\Dom\Location::__construct
     */
    public function testOffsetGetter()
    {
        $location = new Location(1, 2, 3);

        $this->assertEquals(3, $location->getOffset());
    }
}
