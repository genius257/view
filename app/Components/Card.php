<?php

namespace App\Components;

class Card extends \Genius257\View\Component {
    protected $properties = [
        'img' => null,
        'text' => null,
    ];

    protected function _render() {
        ?>
        <div style="width:100px;border: 1px solid #000;"><div style="width:50px;height:50px;border-radius:50px;background-color:black;background-image:url(<?=$this->properties['img']?>);"></div><?=$this->properties['text']?></div>
        <App\Components\Div>test</App\Components\Div>
        <?php
    }
}