<?php

$article1 = new Model\Article;
$article1->set("titre", "Article 1");
$article2 = new Model\Article;
$article2->set("titre", "Article 2");
$view = new App\View([
    "tag" => "Accueil",
]);
$view->setVariables(["example" => "Example", "articles" => [$article1, $article2]]);
$view->addTemplate("accueil.php");
$view->render();