<?php
require_once(__DIR__.'/../../vendor/autoload.php');

header("Content-Type: text/plain");

/*
require_once(__DIR__.'/src/Parser.php');

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

function requireToVar($file){
    ob_start();
    require($file);
    return ob_get_clean();
}

//echo \App\Components\Div::class;

$a = requireToVar(__DIR__.'/app/View/test.php');


$dom = new Dom(new \Parser());

$options = new Options();
$options->setCleanupInput(false);

$dom->setOptions(
    // this is set as the global option level.
    $options
);

//$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$dom->loadStr($a);
//$a = $dom->find('a')[0];
//echo $a->text; // "click here"
//echo $dom->innerHtml;
var_dump($dom->root->getChildren()[0]->tag->name());
var_dump($dom->root->getChildren()[0]->getChildren()[1]->tag);

//$dom = new DOMDocument();
//$dom->LoadHTML($a, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING);
//var_dump($dom->firstChild->childNodes->item(2));
//echo $dom->saveXML();

//$xml = new SimpleXMLElement($a, LIBXML_NOWARNING | LIBXML_NOERROR);
*/

$view = new \Genius257\View\View(__DIR__.'/../app/View/test');

?>
<html>
    <head>
        <title>test</title>
    </head>
    <body>
        <p>
            this is a test <?=\App\Components\Image::make()->setSrc("https://via.placeholder.com/350x150")?>
        </p>
        <?=$view->render()?>
    </body>
</html>
