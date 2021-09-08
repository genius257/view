<?php
require_once(__DIR__.'/../../vendor/autoload.php');

header("Content-Type: text/plain");

$view = new \Genius257\View\View(__DIR__.'/../app/View/home');

echo $view->render();
