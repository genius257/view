<?php

namespace Tests\testData;
use Genius257\View\Component as ViewComponent;

class Component extends ViewComponent {
    protected $properties = [
        'children' => null,
        'data-extra' => null,
    ];

    protected function _render() {
        return "<component></component>";
    }
}
