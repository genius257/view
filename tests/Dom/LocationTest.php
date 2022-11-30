<?php

namespace Tests\Dom;

use Genius257\View\Dom\Location;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase {
    public function testLineGetter() {
        $location = new Location(1, 2, 3);

        $this->assertEquals(1, $location->getLine());
    }

    public function testColumnGetter() {
        $location = new Location(1, 2, 3);

        $this->assertEquals(2, $location->getColumn());
    }

    public function testOffsetGetter() {
        $location = new Location(1, 2, 3);

        $this->assertEquals(3, $location->getOffset());
    }
}
