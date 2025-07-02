<?php
session_start();

require("db.php");

require("functions.php");
require("fetch_data.php");
$uri = parse_url($_SERVER["REQUEST_URI"])["path"];

$apiRoutes = [
  '/get-next-asset-number.php' => 'get-next-asset-number.php',
];

// Check if this is an API request
if (array_key_exists($uri, $apiRoutes)) {
  // For API routes, we need to handle them immediately without any output
  require($apiRoutes[$uri]);
  exit(); // Important: exit after API response
}

$routes = [
  '/' => 'controllers/index.php',
  '/dashboard' => 'controllers/dashboard.php',
  '/add-category' => 'controllers/add-category.php',
  '/manage-hardware' => 'controllers/manage-hardware.php',
  '/hardwares' => 'controllers/hardwares.php',
  '/logout' => 'controllers/logout.php',
  '/assigned-asset' => 'controllers/assigned-asset.php',
  '/manage-hardware/add-asset' => 'controllers/add-asset.php',
  //  '/get-next-asset-number' => 'controllers/get-next-asset-number.php', 


];

if (array_key_exists($uri, $routes)) {
  require($routes[$uri]);
} else {
  http_response_code(404);

  require("views/404.php");

  die();
}
