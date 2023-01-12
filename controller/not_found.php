<?php

$view = new App\View([
    "tag" => "404",
]);

$view->addTemplate("view/404.php");
$view->render();