
<?php

// Get branch name from URL parameter
$branchName = $_GET['branch_name'] ?? '';
$pageTitle = "{$branchName} Branch";

if (empty($branchName)) {
  header("Location: /branch");
  exit;
}
// Handle department deletion
if (isset($_POST['delete_department_id'])) {
  $departmentId = intval($_POST['delete_department_id']);

  // Get department name for success message
  $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
  $stmt->execute([$departmentId]);
  $departmentName = $stmt->fetchColumn();

  // Delete department
  $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
  $stmt->execute([$departmentId]);

  $_SESSION['success_message'] = ['text' => "$departmentName department deleted successfully!", 'action' => 'deleted'];
  header("Location: /branch/" . urlencode($branchName));
  exit;
}

// URL decode the branch name in case it has special characters
$branchName = urldecode($branchName);

// Handle department form submission
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $departmentName = htmlspecialchars(trim($_POST['inputDepartment'] ?? ""));
  $departmentHead = htmlspecialchars(trim($_POST['inputDepartmentHead'] ?? ""));
  $departmentId = intval($_POST['departmentId'] ?? 0);
  $branchName = htmlspecialchars(trim($_POST['branchName'] ?? ""));

  if ($departmentName && !empty($branchName)) {
    // Get branch ID from branch name
    $stmt = $pdo->prepare("SELECT id FROM branch WHERE branch_name = ?");
    $stmt->execute([$branchName]);
    $branchId = $stmt->fetchColumn();

    if (!$branchId) {
      header("Location: /branch");
      exit;
    }

    // Check if department name already exists in this branch
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE LOWER(department_name) = LOWER(?) AND branch_id = ? AND id != ?");
    $stmt->execute([$departmentName, $branchId, $departmentId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $_SESSION['department_error'] = 'Department name already exists in this branch.';
      header("Location: /branch/" . urlencode($branchName));
      exit;
    } else {
      if ($departmentId > 0) {
        // Update existing department
        if ($departmentHead === "") {
          $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$departmentName, $departmentId]);
        } else {
          $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, department_head = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$departmentName, $departmentHead, $departmentId]);
        }
        $_SESSION['success_message'] = ['text' => "$departmentName department updated successfully!", 'action' => 'updated'];
      } else {
        // Insert new department
        $stmt = $pdo->prepare("INSERT INTO departments (branch_id, department_name, department_head, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([$branchId, $departmentName, $departmentHead]);
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

require("views/branch-details.views.php");
