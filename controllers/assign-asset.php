<?php
$pageTitle = 'Assign Asset';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id']) && !empty($_POST['asset_ids'])) {
  $employeeId = $_POST['employee_id'];
  $assetIds = $_POST['asset_ids'];
  $assignedBy = $_SESSION['user_id'] ?? null;

  try {
    $pdo->beginTransaction();

    $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to = ?, status = 'Assigned', updated_at = NOW() WHERE id = ?");

    $assignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      // First, close any existing assignment history for this asset
      closeAssignmentHistory($pdo, $assetId);

      // Update the asset
      if ($updateAssetStmt->execute([$employeeId, $assetId])) {
        // Log the new assignment in history
        if (logAssignmentHistory($pdo, $assetId, $employeeId, 'ASSIGNED', $assignedBy)) {
          $assignedCount++;
        } else {
          $errors[] = "Failed to log history for asset ID: $assetId";
        }
      } else {
        $errors[] = "Failed to update asset ID: $assetId";
      }
    }

    if (empty($errors)) {
      $pdo->commit();
      $_SESSION['success_message'] = "$assignedCount asset(s) successfully assigned with history logged.";
    } else {
      $pdo->rollBack();
      $_SESSION['error_message'] = "Some assignments failed: " . implode(', ', $errors);
    }
  } catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error assigning assets: " . $e->getMessage();
  }

  header("Location: /manage-hardware/assign-asset");
  exit;
}

// dd($_POST['asset_ids']);

// Handle asset assignment removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['asset_ids'])) {
  $assetIds = $_POST['asset_ids'];
  $unassignedBy = $_SESSION['user_id'] ?? null;

  try {
    $pdo->beginTransaction();

    // Get current assignment info before unassigning
    $getAssignmentStmt = $pdo->prepare("SELECT assigned_to FROM asset WHERE id = ? AND assigned_to IS NOT NULL");
    $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to = NULL, status = 'Available', updated_at = NOW() WHERE id = ?");

    $unassignedCount = 0;
    $errors = [];
    foreach ($assetIds as $assetId) {
      // Get current employee assignment
      $getAssignmentStmt->execute([$assetId]);
      $currentAssignment = $getAssignmentStmt->fetch(PDO::FETCH_ASSOC);

      if ($currentAssignment && $currentAssignment['assigned_to']) {
        $currentEmployeeId = $currentAssignment['assigned_to'];

        // Update the asset to unassigned
        if ($updateAssetStmt->execute([$assetId])) {
          // Only close the existing assignment history (no new insert needed)
          if (closeAssignmentHistory($pdo, $assetId)) {
            $unassignedCount++;
          } else {
            $errors[] = "Failed to update assignment history for asset ID: $assetId";
          }
        } else {
          $errors[] = "Failed to unassign asset ID: $assetId";
        }
      } else {
        $errors[] = "Asset ID $assetId is not currently assigned to anyone";
      }
    }
    if (empty($errors)) {
      $pdo->commit();
      $_SESSION['success_message'] = "$unassignedCount asset(s) successfully unassigned with history logged.";
    } else {
      $pdo->rollBack();
      $_SESSION['error_message'] = "Some unassignments failed: " . implode(', ', $errors);
    }
  } catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error unassigning assets: " . $e->getMessage();
  }

  header("Location: /manage-hardware/assign-asset");
  exit;
}
// Your existing code for fetching assets
$assets = fetchAssetsWithInventoryAndCategoryAndEmployee($pdo);
$assetsByInventory = [];
foreach ($assets as $asset) {
  $invId = $asset['inventory_id'];
  if (!isset($assetsByInventory[$invId])) {
    $assetsByInventory[$invId] = [];
  }
  $assetsByInventory[$invId][] = $asset;
}

require("views/assign-asset.views.php");
