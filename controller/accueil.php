<?php

$view = new App\View([
    "tag" => "Accueil",
]);

$view->addTemplate("view/accueil.php");
$view->render();