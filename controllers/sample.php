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
            // Close any existing branch assignment for this asset
            $closeBranchStmt = $pdo->prepare("UPDATE asset_branch_assignment_history SET status = 'UNASSIGNED', unassigned_at = NOW() WHERE asset_id = ? AND status = 'ASSIGNED'");
            $closeBranchStmt->execute([$assetId]);

            // Update the asset with branch assignment
            if ($updateAssetStmt->execute([$branchId, $assetId])) {
                // Log the new branch assignment in history
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
// ASSIGN ASSET TO EMPLOYEE (within branch)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_employee']) && !empty($_POST['asset_ids']) && !empty($_POST['employee_id'])) {
    $employeeId = $_POST['employee_id'];
    $assetIds = $_POST['asset_ids'];
    $assignedBy = $_SESSION['user_id'] ?? null;

    try {
        $pdo->beginTransaction();

        // Get branch ID from the asset
        $getBranchStmt = $pdo->prepare("SELECT assigned_to_branch FROM asset WHERE id = ?");
        $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to = ?, status = 'Employee Assigned', updated_at = NOW() WHERE id = ?");
        $historyStmt = $pdo->prepare("INSERT INTO assignment_history (asset_id, employee_id, branch_id, action_type, assigned_by, assigned_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $assignedCount = 0;
        $errors = [];
        foreach ($assetIds as $assetId) {
            // Get branch ID for this asset
            $getBranchStmt->execute([$assetId]);
            $assetData = $getBranchStmt->fetch(PDO::FETCH_ASSOC);

            if (!$assetData || !$assetData['assigned_to_branch']) {
                $errors[] = "Asset ID $assetId is not assigned to a branch";
                continue;
            }

            $branchId = $assetData['assigned_to_branch'];

            // Close any existing employee assignment for this asset
            $closeEmployeeStmt = $pdo->prepare("UPDATE assignment_history SET end_date = NOW() WHERE asset_id = ? AND end_date IS NULL");
            $closeEmployeeStmt->execute([$assetId]);

            // Update the asset with employee assignment
            if ($updateAssetStmt->execute([$employeeId, $assetId])) {
                // Log the new employee assignment in history
                if ($historyStmt->execute([$assetId, $employeeId, $branchId, $assignedBy])) {
                    $assignedCount++;
                } else {
                    $errors[] = "Failed to log assignment for asset ID: $assetId";
                }
            } else {
                $errors[] = "Failed to assign asset ID: $assetId to employee";
            }
        }

        if (empty($errors)) {
            $pdo->commit();
            $_SESSION['success_message'] = "$assignedCount asset(s) successfully assigned to employee.";
        } else {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Some assignments failed: " . implode(', ', $errors);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error assigning assets to employee: " . $e->getMessage();
    }

    header("Location: /manage-hardware/assign-asset");
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
                // Update asset to remove employee assignment
                if ($updateAssetStmt->execute([$assetId])) {
                    // Close the employee assignment history
                    $closeHistoryStmt = $pdo->prepare("UPDATE assignment_history SET end_date = NOW() WHERE asset_id = ? AND end_date IS NULL");
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

        $updateAssetStmt = $pdo->prepare("UPDATE asset SET assigned_to_branch = NULL, assigned_to = NULL, status = 'Unassigned', updated_at = NOW() WHERE id = ?");

        $unassignedCount = 0;
        $errors = [];

        foreach ($assetIds as $assetId) {
            // Close branch assignment history
            $closeBranchStmt = $pdo->prepare("UPDATE asset_branch_assignment_history SET status = 'UNASSIGNED', unassigned_at = NOW() WHERE asset_id = ? AND status = 'ASSIGNED'");
            $closeBranchStmt->execute([$assetId]);

            // Close employee assignment history if exists
            $closeEmployeeStmt = $pdo->prepare("UPDATE asset_assignment_history SET end_date = NOW() WHERE asset_id = ? AND end_date IS NULL");
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

// Fetch all branches
$branches = fetchBranches($pdo);
// You'll need to create this function

require("views/assign-asset.views.php");
