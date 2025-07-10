<?php
$pageTitle = 'Toprank Branches';

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_branch_id'])) {
  $branchIdToDelete = $_POST['delete_branch_id'];

  // Check if category ID is valid
  if ($branchIdToDelete > 0) {
    // DELETE CATEGORY
    $stmt = $pdo->prepare("DELETE FROM branch WHERE id = ?");
    $stmt->execute([$branchIdToDelete]);

    if ($stmt->rowCount() > 0) {
      // Successfully deleted
      $_SESSION['branch_deleted'] = "Branch deleted successfully!";
    } else {
      // Failed to delete
      $_SESSION['branch_error'] = "Failed to delete branch.";
    }
  } else {
    $_SESSION['branch_error'] = "Invalid branch ID.";
  }

  header("Location: /branch");
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Get POST data and sanitize
  $branchName = htmlspecialchars(trim($_POST['inputBranch'] ?? ""));
  $branchManager = htmlspecialchars(trim($_POST['inputBranchManager'] ?? ""));
  $branchId = intval($_POST['branchId'] ?? 0);

  if ($branchName) {
    // Check if the branch name already exists in the branch table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM branch WHERE LOWER(branch_name) = LOWER(?) AND id != ?");
    $stmt->execute([$branchName, $branchId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      // If branch name exists, set session error and redirect
      $_SESSION['branchname_error'] = 'Branch name already exists.';
      header("Location: /branch");
      exit;
    } else {
      if ($branchId > 0) {
        // If branchId is greater than 0, update the existing branch record
        if ($branchManager === "") {
          // If no branch manager is provided, exclude it from the update query
          $stmt = $pdo->prepare("UPDATE branch SET branch_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$branchName, $branchId]);
        } else {
          // If branch manager is provided, include it in the update query
          $stmt = $pdo->prepare("UPDATE branch SET branch_name = ?, branch_manager = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
          $stmt->execute([$branchName, $branchManager, $branchId]);
        }
        // Set success message
        $_SESSION['success_message'] = ['text' => "$branchName branch updated successfully!", 'action' => 'updated'];
      } else {
        // Insert a new branch record
        $stmt = $pdo->prepare("INSERT INTO branch (branch_name, branch_manager, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([$branchName, $branchManager]);
        // Set success message
        $_SESSION['success_message'] = ['text' => "$branchName branch added successfully!", 'action' => 'added'];
      }

      // Redirect to branch listing page after processing
      header("Location: /branch");
      exit;
    }
  }
}

$branches = fetchBranches($pdo);
// dd($branches);
require("views/branch.views.php");
