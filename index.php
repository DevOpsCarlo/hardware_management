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
  '/manage-hardware/assign-asset/asset-details' => 'controllers/asset-details.php',
  '/employee' => 'controllers/employee.php',
  '/branch' => 'controllers/branch.php',
  '/branch/branch-asset' => 'controllers/branch.php',


];

// 1. Match: /branch/{branch_name}/{department_name}
// if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)\/([a-zA-Z0-9\-_\.%\+\s]+)$/', $uri, $matches)) {
//   // dd($matches);
//   $_GET['branch_name'] = urldecode($matches[1]);
//   $_GET['department_name'] = urldecode($matches[2]);
//   require('controllers/department-detail.php');
//   exit();
// }

// 1. Match: /branch/{branch_name}/{department_name}
if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)\/([a-zA-Z0-9\-_\.%\+\s]+)$/', $uri, $matches)) {
  // dd($matches);
  $_GET['branch_name'] = urldecode($matches[1]);
  require('controllers/department.php');
  exit();
}

// 2. Match: /branch/{branch_name}
if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)$/', $uri, $matches)) {
  $_GET['branch_name'] = urldecode($matches[1]);
  require('controllers/branch-detail.php');
  exit();
}
// 3.  Match: /branch/{branch_name}/assets?filter={filter_type}
// if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)\/assets$/', $uri, $matches)) {
//   $_GET['branch_name'] = urldecode($matches[1]);
//   // filter parameter is already in $_GET from the query string
//   require('controllers/branch-filtered-assets.php');
//   exit();
// }



if (preg_match('/^\/branch\/([a-zA-Z0-9\-_\.%\+\s]+)$/', $uri, $matches)) {
  $_GET['branch_asset'] = urldecode($matches[1]);
  require('controllers/branch-asset.php');
  exit();
}

if (preg_match('/^\/manage-hardware\/assign-asset\/asset-details\/([0-9]+)$/', $uri, $matches)) {
  error_log("MATCHED asset-details route! Asset ID: " . $matches[1]);
  $_GET['id'] = $matches[1];
  error_log("Set _GET['id'] to: " . $_GET['id']);
  require('controllers/asset-details.php');
  exit();
}

if (array_key_exists($uri, $routes)) {
  require($routes[$uri]);
} else {
  http_response_code(404);

  require("views/404.php");

  die();
}
