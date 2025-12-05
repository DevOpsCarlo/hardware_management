<?php
$pageTitle = 'Assets Filter';

// Get filter type from URL parameter
$filterType = $_GET['type'] ?? 'all';
$assets = [];
$filterTitle = 'Assets';

switch ($filterType) {
    case 'total':
        // Display all inventory items expanded by quantity
        $filterTitle = 'Total Inventory';
        $assets = fetchInventoryExpanded($pdo);
        break;

    case 'assigned':
        // Display all assigned assets (Employee, Branch, and Department)
        $filterTitle = 'Assigned Assets';
        $query = "
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.ip_address,
        a.status,
        a.conditions,
        a.assigned_to_branch,
        a.assigned_to,
        a.assigned_department_id,
        b.branch_name,
        i.manufacturer,
        i.model,
        i.photo,
        c.name as category_name,
        e.employee_name as assigned_employee_name,
        e.id as assigned_employee_id,
        d.department_name,
        d.id as department_id,
        a.created_at
      FROM asset a
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN employee e ON a.assigned_to = e.id
      LEFT JOIN branch b ON a.assigned_to_branch = b.id
      LEFT JOIN departments d ON a.assigned_department_id = d.id
      WHERE a.status IN ('Employee Assigned', 'Branch Assigned', 'Department Assigned')
      ORDER BY a.asset_number ASC
    ";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching assigned assets: " . $e->getMessage());
            $assets = [];
        }
        break;

    case 'available':
        // Display only available assets
        $filterTitle = 'Available Assets';
        $query = "
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.ip_address,
        a.status,
        a.conditions,
        a.assigned_to_branch,
        a.assigned_to,
        a.assigned_department_id,
        b.branch_name,
        i.manufacturer,
        i.model,
        i.photo,
        c.name as category_name,
        e.employee_name as assigned_employee_name,
        e.id as assigned_employee_id,
        d.department_name,
        d.id as department_id,
        a.created_at
      FROM asset a
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN employee e ON a.assigned_to = e.id
      LEFT JOIN branch b ON a.assigned_to_branch = b.id
      LEFT JOIN departments d ON a.assigned_department_id = d.id
      WHERE a.status = 'Available'
      ORDER BY a.asset_number ASC
    ";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching available assets: " . $e->getMessage());
            $assets = [];
        }
        break;

    case 'maintenance':
        // Display assets under maintenance
        $filterTitle = 'Assets Under Maintenance';
        $query = "
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.ip_address,
        a.status,
        a.conditions,
        a.assigned_to_branch,
        a.assigned_to,
        a.assigned_department_id,
        b.branch_name,
        i.manufacturer,
        i.model,
        i.photo,
        c.name as category_name,
        e.employee_name as assigned_employee_name,
        e.id as assigned_employee_id,
        d.department_name,
        d.id as department_id,
        a.created_at
      FROM asset a
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN employee e ON a.assigned_to = e.id
      LEFT JOIN branch b ON a.assigned_to_branch = b.id
      LEFT JOIN departments d ON a.assigned_department_id = d.id
      WHERE a.status = 'Under Maintenance'
      ORDER BY a.asset_number ASC
    ";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching maintenance assets: " . $e->getMessage());
            $assets = [];
        }
        break;

    case 'defective':
        // Display defective/uncommitted assets
        $filterTitle = 'Defective Assets';
        $query = "
      SELECT 
        a.id as asset_id,
        a.asset_number,
        a.serial_number,
        a.ip_address,
        a.status,
        a.conditions,
        a.assigned_to_branch,
        a.assigned_to,
        a.assigned_department_id,
        b.branch_name,
        i.manufacturer,
        i.model,
        i.photo,
        c.name as category_name,
        e.employee_name as assigned_employee_name,
        e.id as assigned_employee_id,
        d.department_name,
        d.id as department_id,
        a.created_at
      FROM asset a
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN employee e ON a.assigned_to = e.id
      LEFT JOIN branch b ON a.assigned_to_branch = b.id
      LEFT JOIN departments d ON a.assigned_department_id = d.id
      WHERE a.status = 'Uncommitted'
      ORDER BY a.asset_number ASC
    ";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching defective assets: " . $e->getMessage());
            $assets = [];
        }
        break;

    default:
        $filterTitle = 'All Assets';
        $assets = fetchAssetsWithInventoryAndCategory($pdo);
}
// 
// dd($assets);
require("views/dashboard-filter.views.php");
