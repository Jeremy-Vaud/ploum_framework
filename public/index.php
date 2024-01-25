<?php
require_once __DIR__ . '/../vendor/autoload.php';

App\DotEnv::load();

if ($_ENV["FORCE_HTTPS"]) {
    if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
        exit;
    }
}

$router = new App\Router;
include $router->getController();
