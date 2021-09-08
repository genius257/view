<?php

namespace App\Components;

class Div extends \Genius257\View\Component {

    public function _render() {
        ?>
        <div class="DIV"><?=$this->renderChildren()?></div>
        <?php
    }
}