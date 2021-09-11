<?php
require_once(__DIR__.'/../../vendor/autoload.php');

header("Content-Type: text/plain");

$view = new \Genius257\View\View(__DIR__.'/../app/View/test');

?>
<html>
    <head>
        <title>test</title>
    </head>
    <body>
        <p>
            inline component rendering <?=\App\Components\Image::make()->setSrc("https://via.placeholder.com/350x150")?>
        </p>
        <?=$view->render()?>
    </body>
</html>
