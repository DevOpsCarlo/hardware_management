<?php
$pageTitle = 'Assign Asset';

// ============================================
// ASSIGN ASSET TO BRANCH
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_branch']) && !empty($_POST['asset_ids']) && !empty($_POST['branch_id'])) {
  $branchId = $_POST['branch_id'];
  $assetIds = $_POST['asset_ids'];
  $assignedBy = $_SESSION['user_id'] ?? null;
  try {
    $pdo->beginTransaction();

    $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to_branch = ?, status = 'Branch Assigned', updated_at = NOW() WHERE id = ?");
    $historyStmt = $pdo->prepare("INSERT INTO asset_branch_assignment_history (asset_id, branch_id, status, assigned_by) VALUES (?, ?, 'ASSIGNED', ?)");

    $assignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      $closeBranchStmt = $pdo->prepare("UPDATE asset_branch_assignment_history SET status = 'UNASSIGNED', unassigned_at = NOW() WHERE asset_id = ? AND status = 'ASSIGNED'");
      $closeBranchStmt->execute([$assetId]);

      if ($updateAssetStmt->execute([$branchId, $assetId])) {
        if ($historyStmt->execute([$assetId, $branchId, $assignedBy])) {
          $assignedCount++;
        } else {
          $errors[] = "Failed to log branch assignment for asset ID: $assetId";
        }
      } else {
        $errors[] = "Failed to assign asset ID: $assetId to branch";
      }
    }

    if (empty($errors)) {
      $pdo->commit();
      $_SESSION['success_message'] = "$assignedCount asset(s) successfully assigned to branch.";
    } else {
      $pdo->rollBack();
      $_SESSION['error_message'] = "Some assignments failed: " . implode(', ', $errors);
    }
  } catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error assigning assets to branch: " . $e->getMessage();
  }

  header("Location: /manage-hardware/assign-asset");
  exit;
}

// ============================================
// ASSIGN ASSET TO EMPLOYEE (with file upload)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_employee'])) {
  $assetIds = $_POST['asset_ids'] ?? [];
  $employeeId = intval($_POST['employee_id'] ?? 0);
  $departmentId = intval($_POST['department_id'] ?? 0);
  $assignedBy = $_SESSION['user_id'] ?? null;
  $uploadedFilePath = null;
  $branchId = $branch['id'] ?? null;

  if (empty($assetIds) || $employeeId <= 0 || $departmentId <= 0) {
    $_SESSION['error_message'] = "Invalid input. Please select assets, employee, and department.";
    header("Location: /branch/" . urlencode($branchName));
    exit;
  }

  try {
    $pdo->beginTransaction();

    // Handle file upload
    if (isset($_FILES['agreement_file']) && $_FILES['agreement_file']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = 'uploads/asset-agreements/';

      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      $fileName = $_FILES['agreement_file']['name'];
      $fileTmpPath = $_FILES['agreement_file']['tmp_name'];
      $fileSize = $_FILES['agreement_file']['size'];
      $fileType = $_FILES['agreement_file']['type'];

      $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
      $allowedExtensions = ['pdf', 'doc', 'docx'];
      $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

      if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only PDF and DOC files are allowed.");
      }

      if ($fileSize > 10 * 1024 * 1024) {
        throw new Exception("File size exceeds 10MB limit.");
      }

      $uniqueFileName = 'agreement_' . time() . '_' . uniqid() . '.' . $fileExtension;
      $uploadedFilePath = $uploadDir . $uniqueFileName;

      if (!move_uploaded_file($fileTmpPath, $uploadedFilePath)) {
        throw new Exception("Failed to upload file.");
      }
    }

    $updateAssetStmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to = ?, 
          assigned_employee_id = ?,
          assigned_department_id = ?,
          employee_assigned_at = NOW(),
          agreement_file_path = ?,
          status = 'Employee Assigned',
          updated_at = NOW() 
      WHERE id = ?
    ");

    // FIXED: Complete INSERT statement - table name and structure must match your database
    $employeeHistoryStmt = $pdo->prepare("
      INSERT INTO asset_employee_assignments 
      (asset_id, employee_id, department_id, assigned_at, is_active) 
      VALUES (?, ?, ?, NOW(), TRUE)
    ");

    $assignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      $assetId = intval($assetId);

      try {
        // Mark previous department assignment as inactive
        $closeDeptStmt = $pdo->prepare("
          UPDATE asset_department_assignments 
          SET is_active = FALSE, returned_at = NOW() 
          WHERE asset_id = ? AND is_active = TRUE
        ");
        $closeDeptStmt->execute([$assetId]);

        // Mark previous employee assignment as inactive if exists
        $closeEmployeeStmt = $pdo->prepare("
          UPDATE asset_employee_assignments 
          SET is_active = FALSE, returned_at = NOW() 
          WHERE asset_id = ? AND is_active = TRUE
        ");
        $closeEmployeeStmt->execute([$assetId]);

        // Update asset with employee assignment
        if ($updateAssetStmt->execute([$employeeId, $employeeId, $departmentId, $uploadedFilePath, $assetId])) {
          // Insert into asset_employee_assignments
          if ($employeeHistoryStmt->execute([$assetId, $employeeId, $departmentId])) {
            $assignedCount++;
          } else {
            $errors[] = "Failed to log employee assignment for asset ID: $assetId";
          }
        } else {
          $errors[] = "Failed to update asset ID: $assetId";
        }
      } catch (Exception $e) {
        $errors[] = "Error processing asset ID $assetId: " . $e->getMessage();
      }
    }

    if (empty($errors)) {
      // Update department summary
      updateDepartmentAssetSummary($pdo, $departmentId);
      $pdo->commit();
      $_SESSION['success_message'] = ['text' => "$assignedCount asset(s) successfully assigned to employee.", 'action' => 'assigned'];
    } else {
      $pdo->rollBack();
      if ($uploadedFilePath && file_exists($uploadedFilePath)) {
        unlink($uploadedFilePath);
      }
      $_SESSION['error_message'] = "Some assignments failed: " . implode(', ', $errors);
    }
  } catch (PDOException $e) {
    $pdo->rollBack();
    if ($uploadedFilePath && file_exists($uploadedFilePath)) {
      unlink($uploadedFilePath);
    }
    error_log("Employee assignment error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error assigning assets to employee: " . $e->getMessage();
  } catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = $e->getMessage();
  }

  header("Location: /branch/" . urlencode($branchName));
  exit;
}

// ============================================
// UNASSIGN ASSET FROM EMPLOYEE (back to branch)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_from_employee']) && !empty($_POST['asset_ids'])) {
  $assetIds = $_POST['asset_ids'];
  $unassignedBy = $_SESSION['user_id'] ?? null;

  try {
    $pdo->beginTransaction();

    $getAssignmentStmt = $pdo->prepare("SELECT assigned_to_branch FROM asset WHERE id = ? AND assigned_to IS NOT NULL");
    $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to = NULL, status = 'Branch Assigned', updated_at = NOW() WHERE id = ?");

    $unassignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      $getAssignmentStmt->execute([$assetId]);
      $assetData = $getAssignmentStmt->fetch(PDO::FETCH_ASSOC);

      if ($assetData && $assetData['assigned_to_branch']) {
        if ($updateAssetStmt->execute([$assetId])) {
          $closeHistoryStmt = $pdo->prepare("UPDATE assignment_history SET unassigned_date = NOW() WHERE asset_id = ? AND unassigned_date IS NULL");
          if ($closeHistoryStmt->execute([$assetId])) {
            $unassignedCount++;
          } else {
            $errors[] = "Failed to update history for asset ID: $assetId";
          }
        } else {
          $errors[] = "Failed to unassign asset ID: $assetId";
        }
      } else {
        $errors[] = "Asset ID $assetId is not assigned to an employee or branch";
      }
    }

    if (empty($errors)) {
      $pdo->commit();
      $_SESSION['success_message'] = "$unassignedCount asset(s) returned to branch pool.";
    } else {
      $pdo->rollBack();
      $_SESSION['error_message'] = "Some unassignments failed: " . implode(', ', $errors);
    }
  } catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error returning assets: " . $e->getMessage();
  }

  header("Location: /manage-hardware/assign-asset");
  exit;
}

// ============================================
// UNASSIGN ASSET FROM BRANCH (back to pool)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_from_branch']) && !empty($_POST['asset_ids'])) {
  $assetIds = $_POST['asset_ids'];
  $unassignedBy = $_SESSION['user_id'] ?? null;

  try {
    $pdo->beginTransaction();

    $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to_branch = NULL, assigned_to = NULL, status = 'Available', updated_at = NOW() WHERE id = ?");

    $unassignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      $closeBranchStmt = $pdo->prepare("UPDATE asset_branch_assignment_history SET status = 'UNASSIGNED', unassigned_at = NOW() WHERE asset_id = ? AND status = 'ASSIGNED'");
      $closeBranchStmt->execute([$assetId]);

      $closeEmployeeStmt = $pdo->prepare("UPDATE assignment_history SET unassigned_date = NOW() WHERE asset_id = ? AND unassigned_date IS NULL");
      $closeEmployeeStmt->execute([$assetId]);

      if ($updateAssetStmt->execute([$assetId])) {
        $unassignedCount++;
      } else {
        $errors[] = "Failed to unassign asset ID: $assetId";
      }
    }

    if (empty($errors)) {
      $pdo->commit();
      $_SESSION['success_message'] = "$unassignedCount asset(s) returned to unassigned pool.";
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

// ============================================
// FETCH DATA FOR VIEW
// ============================================
$assets = fetchAssetsWithInventoryAndCategoryAndEmployee($pdo);
$assetsByInventory = [];
foreach ($assets as $asset) {
  $invId = $asset['inventory_id'];
  if (!isset($assetsByInventory[$invId])) {
    $assetsByInventory[$invId] = [];
  }
  $assetsByInventory[$invId][] = $asset;
}

$branches = fetchBranches($pdo);

require("views/assign-asset.views.php");
