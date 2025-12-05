<?php
$branchName = $_GET['branch_name'] ?? '';
$pageTitle = "$branchName Department";


// ============================================
// ADD DEPARTMENT
// ============================================
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

                // // Create summary record for the new department
                // $departmentId = $pdo->lastInsertId();
                // $stmt = $pdo->prepare("INSERT INTO department_asset_summary (department_id, total_assets, assigned_to_employees, available_at_department) VALUES (?, 0, 0, 0)");
                // $stmt->execute([$departmentId]);

                $_SESSION['success_message'] = ['text' => "$departmentName department added successfully!", 'action' => 'added'];
            }

            header("Location: /branch/" . urlencode($branchName) . '/department');
            exit;
        }
    }
}

// ============================================
// DEPARTMENT DELETION
// ============================================
if (isset($_POST['delete_department_id'])) {
    $departmentId = intval($_POST['delete_department_id']);

    $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
    $stmt->execute([$departmentId]);
    $departmentName = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$departmentId]);

    $_SESSION['success_message'] = ['text' => "$departmentName department deleted successfully!", 'action' => 'deleted'];
    header("Location: /branch/" . urlencode($branchName) . 'department');
    exit;
}
$branch = fetchBranchByName($pdo, $branchName);
// $departments = fetchDepartmentsByBranch($pdo, $branch['id']);
$deparmentStats = fetchDepartmentsWithAssetStats($pdo, $branch['id']);
// dd($deparmentStats);
// $departmentSummary = getDepartmentAssetSummary($pdo, $branch['id']);
// dd($departmentSummary);
require("views/department.views.php");
