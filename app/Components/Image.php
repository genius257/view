<?php

namespace App\Components;

/**
 * @method $this setSrc($value)
 */
class Image extends \Genius257\View\Component {
    protected $properties = [
        'src' => null,
    ];

    protected function _render() {
        ?>
        <img src="<?=$this->properties['src']?>" />
        <?php
    }
}