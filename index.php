<?php
require 'settings/global.php';
require 'vendor/autoload.php';

$router = new App\Router;
include $router->getController();