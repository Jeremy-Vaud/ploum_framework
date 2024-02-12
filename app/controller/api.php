<?php

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $api = new App\Api();
    $api->run();
} else {
    $view = new App\View([
        "tag" => "404",
    ]);
    $view->addTemplate("404.php");
    $view->render();
}
