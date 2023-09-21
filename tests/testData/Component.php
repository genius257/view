<?php

namespace Tests\testData;

use Genius257\View\Component as ViewComponent;

class Component extends ViewComponent
{
    protected $properties = [
        'children' => null,
        'data-extra' => null,
    ];

    public function render()
    {
        return "<component></component>";
    }
}
