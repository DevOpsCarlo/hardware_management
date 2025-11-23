<?php
$pageTitle = 'Asset Details';

// Get asset ID from URL parameter
$assetId = $_GET['id'] ?? null;
if (!$assetId) {
  $_SESSION['error_message'] = "Asset ID is required.";
  header("Location: /manage-hardware/assign-asset");
  exit;
}

try {
  // Fetch detailed asset information
  $assetDetails = fetchAssetDetailsById($pdo, $assetId);

  if (!$assetDetails) {
    $_SESSION['error_message'] = "Asset not found.";
    header("Location: /manage-hardware/assign-asset");
    exit;
  }

  // Fetch UNIFIED assignment history (both branch and employee)
  $assignmentHistory = getUnifiedAssignmentHistorySimple($pdo, $assetId);
} catch (PDOException $e) {
  $_SESSION['error_message'] = "Error fetching asset details: " . $e->getMessage();
  header("Location: /manage-hardware/assign-asset");
  exit;
}

// dd($assignmentHistory);


require("views/asset-details.views.php");
