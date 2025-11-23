<?php

// ============================================
// FETCH ALL BRANCHES
// ============================================
function fetchAllBranches($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT id, branch_name FROM branch ORDER BY branch_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching branches: " . $e->getMessage());
        return [];
    }
}

// ============================================
// FETCH ASSETS WITH BRANCH AND EMPLOYEE INFO
// ============================================
function fetchAssetsWithBranchAndEmployee($pdo)
{
    try {
        $stmt = $pdo->prepare("
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.manufacturer,
        a.model,
        a.photo,
        a.status,
        a.conditions,
        a.assigned_to_branch,
        a.assigned_to,
        a.inventory_id,
        c.category_name,
        b.branch_name,
        e.employee_name as assigned_employee_name,
        inv.inventory_name
      FROM asset a
      LEFT JOIN category c ON a.category_id = c.id
      LEFT JOIN branch b ON a.assigned_to_branch = b.id
      LEFT JOIN users e ON a.assigned_to = e.id
      LEFT JOIN inventory inv ON a.inventory_id = inv.id
      ORDER BY a.id DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching assets: " . $e->getMessage());
        return [];
    }
}

// ============================================
// GET BRANCH ASSET SUMMARY
// ============================================
function getBranchAssetSummary($pdo, $branchId)
{
    try {
        $stmt = $pdo->prepare("
      SELECT 
        COUNT(CASE WHEN status = 'Employee Assigned' THEN 1 END) as assigned_to_employees,
        COUNT(CASE WHEN status = 'Branch Assigned' THEN 1 END) as unassigned_in_branch,
        COUNT(CASE WHEN status = 'Under Maintenance' THEN 1 END) as in_repair,
        COUNT(CASE WHEN status = 'Defective' THEN 1 END) as defective,
        COUNT(id) as total_assets
      FROM asset
      WHERE assigned_to_branch = ?
    ");
        $stmt->execute([$branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting branch summary: " . $e->getMessage());
        return null;
    }
}

// ============================================
// GET ALL BRANCHES WITH SUMMARY
// ============================================
function getAllBranchesWithSummary($pdo)
{
    try {
        $stmt = $pdo->prepare("
      SELECT 
        b.id,
        b.branch_name,
        COUNT(CASE WHEN a.status = 'Employee Assigned' THEN 1 END) as assigned_to_employees,
        COUNT(CASE WHEN a.status = 'Branch Assigned' THEN 1 END) as unassigned_in_branch,
        COUNT(CASE WHEN a.status = 'Under Maintenance' THEN 1 END) as in_repair,
        COUNT(CASE WHEN a.status = 'Defective' THEN 1 END) as defective,
        COUNT(a.id) as total_assets
      FROM branch b
      LEFT JOIN asset a ON a.assigned_to_branch = b.id
      GROUP BY b.id, b.branch_name
      ORDER BY b.branch_name ASC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting all branches with summary: " . $e->getMessage());
        return [];
    }
}

// ============================================
// GET ASSETS AVAILABLE FOR EMPLOYEE ASSIGNMENT IN BRANCH
// ============================================
function getAssetsAvailableForEmployeeInBranch($pdo, $branchId)
{
    try {
        $stmt = $pdo->prepare("
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.manufacturer,
        a.model,
        a.photo,
        c.category_name
      FROM asset a
      LEFT JOIN category c ON a.category_id = c.id
      WHERE a.assigned_to_branch = ?
      AND a.status = 'Branch Assigned'
      AND a.assigned_to IS NULL
      ORDER BY a.asset_number ASC
    ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching available assets: " . $e->getMessage());
        return [];
    }
}

// ============================================
// GET EMPLOYEES BY BRANCH
// ============================================
function getEmployeesByBranch($pdo, $branchId)
{
    try {
        $stmt = $pdo->prepare("
      SELECT id, employee_name, branch_id
      FROM users
      WHERE branch_id = ?
      AND status = 'Active'
      ORDER BY employee_name ASC
    ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching employees by branch: " . $e->getMessage());
        return [];
    }
}

// ============================================
// GET ASSET ASSIGNMENT HISTORY
// ============================================
function getAssetAssignmentHistory($pdo, $assetId)
{
    try {
        $stmt = $pdo->prepare("
      SELECT 
        abah.id,
        abah.asset_id,
        abah.branch_id,
        abah.assigned_at,
        abah.unassigned_at,
        abah.status,
        b.branch_name,
        u.employee_name as assigned_by_name
      FROM asset_branch_assignment_history abah
      LEFT JOIN branch b ON abah.branch_id = b.id
      LEFT JOIN users u ON abah.assigned_by = u.id
      WHERE abah.asset_id = ?
      ORDER BY abah.assigned_at DESC
    ");
        $stmt->execute([$assetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching assignment history: " . $e->getMessage());
        return [];
    }
}

// ============================================
// CLOSE BRANCH ASSIGNMENT HISTORY
// ============================================
function closeBranchAssignmentHistory($pdo, $assetId)
{
    try {
        $stmt = $pdo->prepare("
      UPDATE asset_branch_assignment_history 
      SET status = 'UNASSIGNED', unassigned_at = NOW() 
      WHERE asset_id = ? AND status = 'ASSIGNED'
    ");
        return $stmt->execute([$assetId]);
    } catch (PDOException $e) {
        error_log("Error closing branch assignment history: " . $e->getMessage());
        return false;
    }
}

// ============================================
// LOG BRANCH ASSIGNMENT
// ============================================
function logBranchAssignment($pdo, $assetId, $branchId, $status, $assignedBy)
{
    try {
        $stmt = $pdo->prepare("
      INSERT INTO asset_branch_assignment_history 
      (asset_id, branch_id, status, assigned_by) 
      VALUES (?, ?, ?, ?)
    ");
        return $stmt->execute([$assetId, $branchId, $status, $assignedBy]);
    } catch (PDOException $e) {
        error_log("Error logging branch assignment: " . $e->getMessage());
        return false;
    }
}
