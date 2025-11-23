<?php

// Get branch name from URL parameter
$branchName = $_GET['branch_name'] ?? '';
$filter = $_GET['filter'] ?? 'all';

if (empty($branchName)) {
    header("Location: /branch");
    exit;
}

// URL decode the branch name
$branchName = urldecode($branchName);

// Fetch branch details
$branch = fetchBranchByName($pdo, $branchName);

if (!$branch) {
    header("Location: /branch");
    exit;
}

// Fetch all assets for this branch
$allAssets = getBranchCurrentAssets($pdo, $branch['id']);

// Filter assets based on filter parameter
$filteredAssets = [];
$pageTitle = "";

switch ($filter) {
    case 'assigned':
        $pageTitle = "Assigned Assets";
        $filteredAssets = array_filter($allAssets, function ($asset) {
            return $asset['status'] === 'Employee Assigned';
        });
        break;

    case 'unassigned':
        $pageTitle = "Unassigned Assets";
        $filteredAssets = array_filter($allAssets, function ($asset) {
            return in_array($asset['status'], ['Branch Assigned', 'Department Assigned', 'Available']);
        });
        break;

    case 'repair':
        $pageTitle = "In Repair";
        $filteredAssets = array_filter($allAssets, function ($asset) {
            return $asset['status'] === 'Under Maintenance';
        });
        break;

    case 'defective':
        $pageTitle = "Defective Assets";
        $filteredAssets = array_filter($allAssets, function ($asset) {
            return $asset['status'] === 'Defective';
        });
        break;

    default:
        $pageTitle = "All Assets";
        $filteredAssets = $allAssets;
}

// Re-index array after filtering
$assets = array_values($filteredAssets);

// Get branch asset summary for statistics
$branchSummary = getBranchAssetSummary($pdo, $branch['id']);

require("views/branch-filtered-assets.views.php");
