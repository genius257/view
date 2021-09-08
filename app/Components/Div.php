<?php

namespace App\Components;

class Div extends Component {

    public function _render() {
        ?>
        <div class="DIV"><?=$this->renderChildren()?></div>
        <?php
    }
}