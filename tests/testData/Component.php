<?php

namespace Tests\testData;
use Genius257\View\Component as ViewComponent;

class Component extends ViewComponent {
    protected function _render() {
        return "<component></component>";
    }
}
