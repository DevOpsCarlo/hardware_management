<?php

// Get branch name from URL parameter
$branchName = $_GET['branch_name'] ?? '';
$pageTitle = "{$branchName} Branch";

if (empty($branchName)) {
  header("Location: /branch");
  exit;
}

// ============================================
// ASSIGN ASSET TO DEPARTMENT
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_department'])) {
  $assetIds = $_POST['asset_ids'] ?? [];
  $departmentId = intval($_POST['department_id'] ?? 0);
  $assignedBy = $_SESSION['user_id'] ?? null;

  if (!empty($assetIds) && $departmentId > 0) {
    try {
      $pdo->beginTransaction();

      $updateAssetStmt = $pdo->prepare("
        UPDATE asset 
        SET assigned_department_id = ?, 
            department_assigned_at = NOW(),
            status = 'Department Assigned',
            updated_at = NOW() 
        WHERE id = ?
      ");

      $historyStmt = $pdo->prepare("
        INSERT INTO asset_department_assignments (asset_id, department_id, assigned_at, is_active)
        VALUES (?, ?, NOW(), TRUE)
      ");

      $assignedCount = 0;
      $errors = [];

      foreach ($assetIds as $assetId) {
        $assetId = intval($assetId);

        // Mark any previous department assignment as inactive
        $closeDeptStmt = $pdo->prepare("
          UPDATE asset_department_assignments 
          SET is_active = FALSE, returned_at = NOW() 
          WHERE asset_id = ? AND is_active = TRUE
        ");
        $closeDeptStmt->execute([$assetId]);

        if ($updateAssetStmt->execute([$departmentId, $assetId])) {
          if ($historyStmt->execute([$assetId, $departmentId])) {
            $assignedCount++;
          } else {
            $errors[] = "Failed to log department assignment for asset ID: $assetId";
          }
        } else {
          $errors[] = "Failed to assign asset ID: $assetId to department";
        }
      }

      if (empty($errors)) {
        updateDepartmentAssetSummary($pdo, $departmentId);
        $pdo->commit();
        $_SESSION['success_message'] = ['text' => "$assignedCount asset(s) successfully assigned to department.", 'action' => 'assigned'];
      } else {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Some assignments failed: " . implode(', ', $errors);
      }
    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Department assignment error: " . $e->getMessage());
      $_SESSION['error_message'] = "Error assigning assets to department: " . $e->getMessage();
    }
  }

  header("Location: /branch/" . urlencode($branchName));
  exit;
}

// ============================================
// ASSIGN ASSET TO EMPLOYEE (from department)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_employee'])) {
  $assetIds = $_POST['asset_ids'] ?? [];
  $employeeId = intval($_POST['employee_id'] ?? 0);
  $departmentId = intval($_POST['department_id'] ?? 0);
  $assignedBy = $_SESSION['user_id'] ?? null;
  $uploadedFilePath = null;

  if (empty($assetIds) || $employeeId <= 0) {
    $_SESSION['error_message'] = "Invalid asset IDs or employee ID.";
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

    // Update asset table with correct columns
    $updateAssetStmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to = ?, 
          status = 'Employee Assigned',
          updated_at = NOW()
      WHERE id = ?
    ");

    // Insert into assignment_history with correct columns
    $employeeHistoryStmt = $pdo->prepare("
      INSERT INTO assignment_history 
      (asset_id, employee_id, branch_id, action_type, assigned_by, assigned_date, file_path)
      VALUES (?, ?, ?, 'assigned', ?, NOW(), ?)
    ");

    $assignedCount = 0;
    $errors = [];

    foreach ($assetIds as $assetId) {
      $assetId = intval($assetId);

      if ($updateAssetStmt->execute([$employeeId, $assetId])) {
        // Insert assignment history with all required parameters
        if ($employeeHistoryStmt->execute([$assetId, $employeeId, $departmentId, $assignedBy, $uploadedFilePath])) {
          $assignedCount++;
        } else {
          $errors[] = "Failed to log assignment for asset ID: $assetId";
        }
      } else {
        $errors[] = "Failed to assign asset ID: $assetId to employee";
      }
    }

    if (empty($errors)) {
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
// MOVE TO BRANCH LEVEL (from department)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_from_department'])) {
  $assetIds = $_POST['asset_ids'] ?? [];

  if (!empty($assetIds)) {
    try {
      $pdo->beginTransaction();

      foreach ($assetIds as $assetId) {
        $assetId = intval($assetId);

        // Get department ID for later update
        $stmt = $pdo->prepare("SELECT assigned_department_id FROM asset WHERE id = ?");
        $stmt->execute([$assetId]);
        $departmentId = $stmt->fetchColumn();

        // Mark department assignment as inactive
        $stmt = $pdo->prepare("
          UPDATE asset_department_assignments 
          SET is_active = FALSE, returned_at = NOW() 
          WHERE asset_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$assetId]);

        // Update asset table
        $stmt = $pdo->prepare("
          UPDATE asset 
          SET status = 'Branch Assigned', 
              assigned_department_id = NULL, 
              department_assigned_at = NULL,
              updated_at = NOW() 
          WHERE id = ?
        ");
        $stmt->execute([$assetId]);

        // Update department summary
        if ($departmentId) {
          updateDepartmentAssetSummary($pdo, $departmentId);
        }
      }

      $pdo->commit();
      $_SESSION['success_message'] = ['text' => count($assetIds) . ' asset(s) moved to branch level successfully!', 'action' => 'returned'];
    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Move to branch error: " . $e->getMessage());
      $_SESSION['error_message'] = "Error moving assets to branch level.";
    }
  }

  header("Location: /branch/" . urlencode($branchName));
  exit;
}

// ============================================
// RETURN TO DEPARTMENT (from employee)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_from_employee'])) {
  $assetIds = $_POST['asset_ids'] ?? [];

  if (!empty($assetIds)) {
    try {
      $pdo->beginTransaction();

      foreach ($assetIds as $assetId) {
        $assetId = intval($assetId);

        // Get current assignment info
        $stmt = $pdo->prepare("SELECT assigned_to FROM asset WHERE id = ?");
        $stmt->execute([$assetId]);
        $currentEmployeeId = $stmt->fetchColumn();

        // Mark the employee assignment as inactive in assignment_history
        if ($currentEmployeeId) {
          $stmt = $pdo->prepare("
            UPDATE assignment_history 
            SET unassigned_date = NOW(),
                action_type = 'unassigned'
            WHERE asset_id = ? AND employee_id = ? AND unassigned_date IS NULL
            LIMIT 1
          ");
          $stmt->execute([$assetId, $currentEmployeeId]);
        }

        // Update asset table - return to department level
        $stmt = $pdo->prepare("
          UPDATE asset 
          SET status = 'Department Assigned', 
              assigned_to = NULL,
              updated_at = NOW() 
          WHERE id = ?
        ");
        $stmt->execute([$assetId]);
      }

      $pdo->commit();
      $_SESSION['success_message'] = ['text' => count($assetIds) . ' asset(s) returned to department level successfully!', 'action' => 'returned'];
    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Return to department error: " . $e->getMessage());
      $_SESSION['error_message'] = "Error returning assets to department level: " . $e->getMessage();
    }
  }

  header("Location: /branch/" . urlencode($branchName));
  exit;
}

// Handle department deletion
if (isset($_POST['delete_department_id'])) {
  $departmentId = intval($_POST['delete_department_id']);

  $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
  $stmt->execute([$departmentId]);
  $departmentName = $stmt->fetchColumn();

  $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
  $stmt->execute([$departmentId]);

  $_SESSION['success_message'] = ['text' => "$departmentName department deleted successfully!", 'action' => 'deleted'];
  header("Location: /branch/" . urlencode($branchName));
  exit;
}

$branchName = urldecode($branchName);

// Handle department form submission
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $departmentName = htmlspecialchars(trim($_POST['inputDepartment'] ?? ""));
  $departmentHead = htmlspecialchars(trim($_POST['inputDepartmentHead'] ?? ""));
  $departmentId = intval($_POST['departmentId'] ?? 0);
  $branchName = htmlspecialchars(trim($_POST['branchName'] ?? ""));

  if ($departmentName && !empty($branchName)) {
    $stmt = $pdo->prepare("SELECT id FROM branch WHERE branch_name = ?");
    $stmt->execute([$branchName]);
    $branchId = $stmt->fetchColumn();

    if (!$branchId) {
      header("Location: /branch");
      exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE LOWER(department_name) = LOWER(?) AND branch_id = ? AND id != ?");
    $stmt->execute([$departmentName, $branchId, $departmentId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $_SESSION['department_error'] = 'Department name already exists in this branch.';
      header("Location: /branch/" . urlencode($branchName));
      exit;
    } else {
      if ($departmentId > 0) {
        if ($departmentHead === "") {
          $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$departmentName, $departmentId]);
        } else {
          $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, department_head = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$departmentName, $departmentHead, $departmentId]);
        }
        $_SESSION['success_message'] = ['text' => "$departmentName department updated successfully!", 'action' => 'updated'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO departments (branch_id, department_name, department_head, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([$branchId, $departmentName, $departmentHead]);

        // Create summary record for the new department
        $departmentId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO department_asset_summary (department_id, total_assets, assigned_to_employees, available_at_department) VALUES (?, 0, 0, 0)");
        $stmt->execute([$departmentId]);

        $_SESSION['success_message'] = ['text' => "$departmentName department added successfully!", 'action' => 'added'];
      }

      header("Location: /branch/" . urlencode($branchName));
      exit;
    }
  }
}

$branch = fetchBranchByName($pdo, $branchName);

if (!$branch) {
  header("Location: /branch");
  exit;
}

$departments = fetchDepartmentsByBranch($pdo, $branch['id']);
$assets = getBranchCurrentAssets($pdo, $branch['id']);
$branchSummary = getBranchAssetSummary($pdo, $branch['id']);

require("views/branch-details.views.php");
