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
  '/manage-hardware/add-inventory' => 'controllers/add-inventory.php',
  '/hardwares' => 'controllers/hardwares.php',
  '/logout' => 'controllers/logout.php',
  '/assigned-asset' => 'controllers/assigned-asset.php',
  '/manage-hardware/add-category' => 'controllers/add-category.php',
  '/manage-hardware/add-asset' => 'controllers/add-asset.php',
  '/manage-hardware/add-asset/view-asset' => 'controllers/view-asset.php',
  '/manage-hardware/assign-asset' => 'controllers/assign-asset.php',
  '/user' => 'controllers/user.php',
  '/branch' => 'controllers/branch.php',


];

// Handle dynamic branch routes (e.g., /branch/1, /branch/2, etc.)
// if (preg_match('/^\/branch\/(\d+)$/', $uri, $matches)) {
//   $_GET['id'] = $matches[1]; // Set the branch ID in $_GET
//   require('controllers/branch-detail.php');
//   exit();
// }

if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)$/', $uri, $matches)) {
  $_GET['branch_name'] = urldecode($matches[1]);
  require('controllers/branch-detail.php');
  exit();
}

if (array_key_exists($uri, $routes)) {
  require($routes[$uri]);
} else {
  http_response_code(404);

  require("views/404.php");

  die();
}
