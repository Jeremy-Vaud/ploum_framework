<?php

$view = new App\View([
    "tag" => "404",
]);

$view->addTemplate("404.php");
$view->render();