<?php
session_start();

require("db.php");

require("functions.php");


$uri = parse_url($_SERVER["REQUEST_URI"])["path"];

$routes = [
  '/' => 'controllers/index.php',
  '/dashboard' => 'controllers/dashboard.php',
  '/add-category' => 'controllers/add-category.php',
  '/manage-hardware' => 'controllers/manage-hardware.php',
  '/hardwares' => 'controllers/hardwares.php',
  '/logout' => 'controllers/logout.php',

];

if (array_key_exists($uri, $routes)) {
  require($routes[$uri]);
} else {
  http_response_code(404);

  require("views/404.php");

  die();
}
