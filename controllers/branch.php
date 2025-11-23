<?php
$pageTitle = 'Toprank Branches';

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_branch_id'])) {
  $branchIdToDelete = $_POST['delete_branch_id'];

  if ($branchIdToDelete > 0) {
    $stmt = $pdo->prepare("DELETE FROM branch WHERE id = ?");
    $stmt->execute([$branchIdToDelete]);

    if ($stmt->rowCount() > 0) {
      $_SESSION['success_message'] = ['text' => 'Branch deleted successfully!', 'action' => 'deleted'];
    } else {
      $_SESSION['error_message'] = 'Failed to delete branch.';
    }
  } else {
    $_SESSION['error_message'] = 'Invalid branch ID.';
  }

  header("Location: /branch");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $branchName = htmlspecialchars(trim($_POST['inputBranch'] ?? ""));
  $branchId = intval($_POST['branchId'] ?? 0);

  if ($branchName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM branch WHERE LOWER(branch_name) = LOWER(?) AND id != ?");
    $stmt->execute([$branchName, $branchId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $_SESSION['error_message'] = 'Branch name already exists.';
      header("Location: /branch");
      exit;
    } else {
      if ($branchId > 0) {
        $stmt = $pdo->prepare("UPDATE branch SET branch_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$branchName, $branchId]);
        $_SESSION['success_message'] = ['text' => "$branchName branch updated successfully!", 'action' => 'updated'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO branch (branch_name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([$branchName]);
        $_SESSION['success_message'] = ['text' => "$branchName branch added successfully!", 'action' => 'added'];
      }

      header("Location: /branch");
      exit;
    }
  }
}
// FETCH BRANCHES WITH ASSET STATISTICS
$branches = fetchBranchesWithAssetStats($pdo);


// dd($branches);
require("views/branch.views.php");
