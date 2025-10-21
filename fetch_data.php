<?php
//Fetch data
function fetchInventoryWithCategory($pdo)
{
  $stmt = $pdo->prepare("
        SELECT 
            inventory.id AS inventory_id,
            inventory.manufacturer,
            inventory.model,
            inventory.photo,
            inventory.quantity,
            inventory.category_id,
            inventory.purchase_date,
            inventory.created_at,
            inventory.updated_at,
            categories.name AS category_name
        FROM inventory
        INNER JOIN categories ON inventory.category_id = categories.id
        ORDER BY inventory.manufacturer ASC
    ");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAssetsWithInventoryAndCategory($pdo)
{
  $stmt = $pdo->prepare("
        SELECT 
            asset.id AS asset_id,
            asset.asset_number,
            asset.serial_number,
            asset.ip_address,
            asset.conditions,
            asset.status,
            asset.related_laptop_id,
            asset.created_at AS asset_created_at,
            asset.updated_at AS asset_updated_at,

            inventory.id AS inventory_id,
            inventory.manufacturer,
            inventory.model,
            inventory.photo,
            inventory.quantity,
            inventory.purchase_date,
            inventory.warranty_years,
            inventory.created_at AS inventory_created_at,
            inventory.updated_at AS inventory_updated_at,

            categories.id AS category_id,
            categories.name AS category_name,
            categories.category_code
        FROM asset
        INNER JOIN inventory ON asset.inventory_id = inventory.id
        INNER JOIN categories ON inventory.category_id = categories.id
        ORDER BY inventory.manufacturer ASC, asset.asset_number ASC
    ");

  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAssetsWithInventoryAndCategoryAndEmployee($pdo)
{
  $query = "
        SELECT 
            a.id as asset_id,
            a.asset_number,
            a.serial_number,
            a.ip_address,
            a.status,
            a.conditions,
            a.assigned_to,
            i.id as inventory_id,
            i.manufacturer,
            i.model,
            i.photo,
            i.category_id,
            c.name AS category_name,
            e.employee_name as assigned_employee_name,
            e.id as assigned_employee_id
        FROM asset a
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN employee e ON a.assigned_to = e.id
        ORDER BY i.manufacturer, i.model, a.asset_number
    ";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchRelatedChargerByLaptopAssetId($pdo, $laptopAssetId)
{
  $stmt = $pdo->prepare("
        SELECT * FROM asset
        WHERE related_laptop_id = :laptopAssetId
        LIMIT 1
    ");
  $stmt->execute(['laptopAssetId' => $laptopAssetId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}




function fetchLaptopChargerId($pdo)
{
  $chargerCategoryName = 'Laptop Charger';
  $stmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
  $stmt->execute([$chargerCategoryName]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchLatestAssetForInventory($pdo, $inventoryId)
{
  $stmt = $pdo->prepare("
        SELECT * FROM asset
        WHERE inventory_id = :inventoryId
        ORDER BY created_at DESC
        LIMIT 1
    ");
  $stmt->execute(['inventoryId' => $inventoryId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}


function fetchBranches($pdo)
{
  $stmt = $pdo->prepare("SELECT id, branch_name, branch_manager, created_at, updated_at FROM branch");
  $stmt->execute();

  // Fetch the results as an associative array
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchEmployee($pdo)
{
  $stmt = $pdo->prepare("SELECT * FROM employee");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch branch details by name
function fetchBranchByName($pdo, $branchName)
{
  $stmt = $pdo->prepare("SELECT * FROM branch WHERE branch_name = ?");
  $stmt->execute([$branchName]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch departments for this branch
function fetchDepartmentsByBranch($pdo, $branchId)
{
  $stmt = $pdo->prepare("SELECT * FROM departments WHERE branch_id = ? ORDER BY department_name");
  $stmt->execute([$branchId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchEmployeeActive($pdo, $status = 'Active')
{
  $stmt = $pdo->prepare("SELECT * FROM employee WHERE option_status = :status ");
  $stmt->bindParam(':status', $status, PDO::PARAM_STR);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBranchByName($pdo, $branchName)
{
  $stmt = $pdo->prepare("SELECT * FROM branch WHERE branch_name = ?");
  $stmt->execute([$branchName]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getDepartmentByName($pdo, $branchId, $departmentName)
{
  $stmt = $pdo->prepare("SELECT * FROM departments WHERE branch_id = ? AND department_name = ?");
  $stmt->execute([$branchId, $departmentName]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEmployeesByDepartment($pdo, $departmentId)
{
  $stmt = $pdo->prepare("SELECT * FROM employee WHERE department_id = ?");
  $stmt->execute([$departmentId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// // Function to get asset assignment history
// function getAssetAssignmentHistory($pdo, $assetId)
// {
//   $query = "
//         SELECT 
//             aah.id,
//             aah.asset_id,
//             aah.action_type,
//             aah.assigned_date,
//             aah.unassigned_date,
//             aah.notes,
//             e.employee_name as assigned_employee_name,
//             e.employee_id as assigned_employee_id,
//             pe.employee_name as previous_employee_name,
//             pe.employee_id as previous_employee_id,
//             ab.name as assigned_by_name,
//             a.asset_number,
//             DATEDIFF(
//                 COALESCE(aah.unassigned_date, NOW()), 
//                 aah.assigned_date
//             ) as days_assigned
//         FROM asset_assignment_history aah
//         LEFT JOIN employee e ON aah.employee_id = e.id
//         LEFT JOIN employee pe ON aah.previous_employee_id = pe.id
//         LEFT JOIN users ab ON aah.assigned_by = ab.id
//         LEFT JOIN assets a ON aah.asset_id = a.id
//         WHERE aah.asset_id = ?
//         ORDER BY aah.assigned_date DESC
//     ";

//   $stmt = $pdo->prepare($query);
//   $stmt->execute([$assetId]);
//   return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

// Function to get all assets currently assigned to an employee
function getEmployeeCurrentAssets($pdo, $employeeId)
{
  $query = "
        SELECT 
            a.id as asset_id,
            a.asset_number,
            a.serial_number,
            a.status,
            a.conditions,
            i.manufacturer,
            i.model,
            c.category_name,
            aah.assigned_date
        FROM assets a
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN category c ON i.category_id = c.id
        LEFT JOIN asset_assignment_history aah ON a.id = aah.asset_id 
            AND aah.employee_id = ? 
            AND aah.unassigned_date IS NULL
        WHERE a.assigned_to = ?
        ORDER BY aah.assigned_date DESC
    ";

  $stmt = $pdo->prepare($query);
  $stmt->execute([$employeeId, $employeeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get employee's full asset history
function getEmployeeAssetHistory($pdo, $employeeId)
{
  $query = "
        SELECT 
            aah.id,
            aah.asset_id,
            aah.action_type,
            aah.assigned_date,
            aah.unassigned_date,
            aah.notes,
            a.asset_number,
            i.manufacturer,
            i.model,
            c.category_name,
            DATEDIFF(
                COALESCE(aah.unassigned_date, NOW()), 
                aah.assigned_date
            ) as days_assigned
        FROM asset_assignment_history aah
        LEFT JOIN assets a ON aah.asset_id = a.id
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN category c ON i.category_id = c.id
        WHERE aah.employee_id = ?
        ORDER BY aah.assigned_date DESC
    ";

  $stmt = $pdo->prepare($query);
  $stmt->execute([$employeeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get asset assignment statistics
// function getAssetAssignmentStats($pdo, $assetId)
// {
//   $query = "
//         SELECT 
//             COUNT(DISTINCT employee_id) as total_employees_assigned,
//             COUNT(*) as total_assignments,
//             AVG(DATEDIFF(
//                 COALESCE(unassigned_date, NOW()), 
//                 assigned_date
//             )) as avg_assignment_days,
//             MIN(assigned_date) as first_assignment,
//             MAX(assigned_date) as latest_assignment
//         FROM asset_assignment_history 
//         WHERE asset_id = ? AND action_type = 'assigned'
//     ";

//   $stmt = $pdo->prepare($query);
//   $stmt->execute([$assetId]);
//   return $stmt->fetch(PDO::FETCH_ASSOC);
// }

// Function to get assets that have been assigned to multiple employees
function getFrequentlyTransferredAssets($pdo, $minTransfers = 3)
{
  $query = "
        SELECT 
            a.id,
            a.asset_number,
            i.manufacturer,
            i.model,
            c.category_name,
            COUNT(aah.id) as transfer_count,
            GROUP_CONCAT(
                CONCAT(e.employee_name, ' (', 
                DATE_FORMAT(aah.assigned_date, '%Y-%m-%d'), 
                ' to ', 
                COALESCE(DATE_FORMAT(aah.unassigned_date, '%Y-%m-%d'), 'Current'), 
                ')')
                ORDER BY aah.assigned_date DESC
                SEPARATOR '; '
            ) as assignment_history
        FROM assets a
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN category c ON i.category_id = c.id
        LEFT JOIN asset_assignment_history aah ON a.id = aah.asset_id AND aah.action_type = 'assigned'
        LEFT JOIN employee e ON aah.employee_id = e.id
        GROUP BY a.id
        HAVING transfer_count >= ?
        ORDER BY transfer_count DESC
    ";

  $stmt = $pdo->prepare($query);
  $stmt->execute([$minTransfers]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Mapping 
function generateAssetNumber($categoryId, $categoryName)
{
  $categoryMappings = [
    'laptop' => ['code' => '01', 'suffix' => ''],
    'tv' => ['code' => '02', 'suffix' => ''],
    'desktop' => ['code' => '03', 'suffix' => ''],
    'monitor' => ['code' => '04', 'suffix' => ''],
    'printer' => ['code' => '05', 'suffix' => ''],
    'scanner' => ['code' => '07', 'suffix' => ''],
    'paper shredder' => ['code' => '08', 'suffix' => ''],
    'webcam' => ['code' => '09', 'suffix' => ''],
    'ipad' => ['code' => '10', 'suffix' => ''],
    'projector' => ['code' => '11', 'suffix' => ''],
    'speaker' => ['code' => '12', 'suffix' => ''],
    'amplifier' => ['code' => '13', 'suffix' => ''],
    'microphone' => ['code' => '14', 'suffix' => ''],
    'mixer' => ['code' => '15', 'suffix' => ''],
    'laptop mouse' => ['code' => '01', 'suffix' => 'M'],
    'laptop charger' => ['code' => '01', 'suffix' => 'C'],
    'headset' => ['code' => '01', 'suffix' => 'H'],
    'bracket' => ['code' => '02', 'suffix' => 'BK'],
    'desktop monitor' => ['code' => '03', 'suffix' => 'MO'],
    'desktop mouse' => ['code' => '03', 'suffix' => 'M'],
    'system unit' => ['code' => '03', 'suffix' => 'SU'],
  ];

  $categoryKey = strtolower(trim($categoryName));
  if (!isset($categoryMappings[$categoryKey])) {
    throw new Exception("Unknown category: " . $categoryName);
  }

  $mapping = $categoryMappings[$categoryKey];
  $categoryCode = $mapping['code'];
  $suffix = $mapping['suffix'];

  global $pdo;

  if ($suffix === '') {
    $stmt = $pdo->prepare("
            SELECT asset_number 
            FROM asset 
            WHERE asset_number REGEXP ?
            ORDER BY CAST(SUBSTRING(asset_number, LENGTH(CONCAT('TRA-', ?, '-')) + 1) AS UNSIGNED) DESC
            LIMIT 1
        ");
    $regex = "^TRA-{$categoryCode}-[0-9]{4}$";
    $stmt->execute([$regex, $categoryCode]);
    $lastAssetNumber = $stmt->fetchColumn();
    $nextNumber = $lastAssetNumber
      ? str_pad(((int)substr($lastAssetNumber, 8)) + 1, 4, '0', STR_PAD_LEFT)
      : '0001';
    return "TRA-{$categoryCode}-{$nextNumber}";
  } else {
    $stmt = $pdo->prepare("
            SELECT asset_number 
            FROM asset 
            WHERE asset_number REGEXP ?
            ORDER BY CAST(SUBSTRING(asset_number, 9, 4) AS UNSIGNED) DESC
            LIMIT 1
        ");
    $regex = "^TRA-{$categoryCode}-[0-9]{4}{$suffix}$";
    $stmt->execute([$regex]);
    $lastAccessoryAsset = $stmt->fetchColumn();
    $nextNumber = $lastAccessoryAsset
      ? str_pad(((int)substr($lastAccessoryAsset, 8, 4)) + 1, 4, '0', STR_PAD_LEFT)
      : '0001';
    return "TRA-{$categoryCode}-{$nextNumber}{$suffix}";
  }
}


// Assign asset deitals 

function fetchAssetDetailsById($pdo, $assetId)
{
  $query = "
        SELECT 
            a.id as asset_id,
            a.asset_number,
            a.serial_number,
            a.ip_address,
            a.status,
            a.conditions,
            a.assigned_to,
            a.related_laptop_id,
            a.created_at as asset_created_at,
            a.updated_at as asset_updated_at,
            
            i.id as inventory_id,
            i.manufacturer,
            i.model,
            i.photo,
            i.quantity,
            i.purchase_date,
            i.warranty_years,
            i.created_at as inventory_created_at,
            i.updated_at as inventory_updated_at,
            
            c.id as category_id,
            c.name AS category_name,
            c.category_code,
            
            e.employee_name as assigned_employee_name,
            e.id as assigned_employee_id,
            e.employee_id as assigned_employee_code,
            
            d.department_name as assigned_employee_department,
            d.id as department_id,
            b.branch_name as assigned_employee_branch,
            b.id as branch_id
            
        FROM asset a
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN employee e ON a.assigned_to = e.id
        LEFT JOIN department_employee de ON e.id = de.employee_id
        LEFT JOIN departments d ON de.department_id = d.id
        LEFT JOIN branch b ON d.branch_id = b.id
        WHERE a.id = ?
    ";

  $stmt = $pdo->prepare($query);
  $stmt->execute([$assetId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Function to log assignment history
function logAssignmentHistory($pdo, $assetId, $employeeId, $actionType, $assignedBy = null, $notes = null)
{
  try {
    $stmt = $pdo->prepare("
            INSERT INTO assignment_history (asset_id, employee_id, action_type, assigned_by, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
    return $stmt->execute([$assetId, $employeeId, $actionType, $assignedBy, $notes]);
  } catch (PDOException $e) {
    error_log("Error logging assignment history: " . $e->getMessage());
    return false;
  }
}

// Function to close previous assignment history (when unassigning)
function closeAssignmentHistory($pdo, $assetId)
{
  try {
    $stmt = $pdo->prepare("
            UPDATE assignment_history 
            SET unassigned_date = CURRENT_TIMESTAMP 
            WHERE asset_id = ? AND unassigned_date IS NULL AND action_type = 'ASSIGNED'
        ");
    return $stmt->execute([$assetId]);
  } catch (PDOException $e) {
    error_log("Error closing assignment history: " . $e->getMessage());
    return false;
  }
}

/**
 * Get assignment history for a specific asset with enhanced employee details
 */
function getAssetAssignmentHistory($pdo, $assetId)
{
  try {
    $stmt = $pdo->prepare("
            SELECT 
               ah.*,
                e.employee_name as assigned_employee_name,
                e.employee_id as assigned_employee_code,
                u.username as assigned_by_name,
                a.asset_number,
                a.serial_number,
                (SELECT GROUP_CONCAT(d.department_name SEPARATOR ', ')
                 FROM department_employee de 
                 JOIN departments d ON de.department_id = d.id 
                 WHERE de.employee_id = ah.employee_id) as employee_department,
                (SELECT b.branch_name 
                 FROM department_employee de 
                 JOIN departments d ON de.department_id = d.id 
                 JOIN branch b ON d.branch_id = b.id 
                 WHERE de.employee_id = ah.employee_id 
                 LIMIT 1) as assigned_employee_branch,
                CASE 
                    WHEN ah.unassigned_date IS NOT NULL THEN
                        TIMESTAMPDIFF(DAY, ah.assigned_date, ah.unassigned_date)
                    ELSE
                        TIMESTAMPDIFF(DAY, ah.assigned_date, NOW())
                END as days_assigned
            FROM assignment_history ah
            LEFT JOIN employee e ON ah.employee_id = e.id
            LEFT JOIN users u ON ah.assigned_by = u.id
            LEFT JOIN asset a ON ah.asset_id = a.id
            WHERE ah.asset_id = ?
            ORDER BY ah.assigned_date DESC
        ");

    $stmt->execute([$assetId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the query and result count
    error_log("Asset ID: " . $assetId . ", Records found: " . count($result));

    return $result;
  } catch (PDOException $e) {
    error_log("Error fetching asset assignment history: " . $e->getMessage());
    error_log("SQL Query error details: " . $e->getCode());
    return [];
  }
}

/**
 * Get assignment history for a specific employee
 */

function getEmployeeAssignmentHistory($pdo, $employeeId)
{
  try {
    $stmt = $pdo->prepare("
            SELECT 
                ah.*,
                e.employee_name,
                a.asset_number,
                a.serial_number,
                a.manufacturer,
                a.model,
                c.category_name,
                u.username as assigned_by_username,
                CASE 
                    WHEN ah.unassigned_date IS NOT NULL THEN
                        TIMESTAMPDIFF(DAY, ah.assigned_date, ah.unassigned_date)
                    ELSE
                        TIMESTAMPDIFF(DAY, ah.assigned_date, NOW())
                END as days_assigned
            FROM assignment_history ah
            JOIN asset a ON ah.asset_id = a.id
            JOIN employees e ON ah.employee_id = e.id
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON ah.assigned_by = u.id
            WHERE ah.employee_id = ? AND ah.action_type = 'ASSIGNED'
            ORDER BY ah.assigned_date DESC
        ");
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching employee assignment history: " . $e->getMessage());
    return [];
  }
}

/**
 * Get current active assignments for an asset
 */
function getCurrentAssetAssignment($pdo, $assetId)
{
  try {
    $stmt = $pdo->prepare("
            SELECT 
                ah.*,
                e.employee_name,
                e.employee_id as employee_number,
                e.email as employee_email,
                e.position as employee_position,
                d.department_name,
                b.branch_name
            FROM assignment_history ah
            JOIN employees e ON ah.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN branches b ON e.branch_id = b.id
            WHERE ah.asset_id = ? 
                AND ah.action_type = 'ASSIGNED' 
                AND ah.unassigned_date IS NULL
            ORDER BY ah.assigned_date DESC
            LIMIT 1
        ");
    $stmt->execute([$assetId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching current asset assignment: " . $e->getMessage());
    return false;
  }
}

/**
 * Get all assignments with duration calculation (Enhanced)
 */
function getAssignmentHistoryWithDuration($pdo, $assetId = null, $employeeId = null)
{
  $whereCondition = "WHERE ah.action_type = 'ASSIGNED'";
  $params = [];

  if ($assetId) {
    $whereCondition .= " AND ah.asset_id = ?";
    $params[] = $assetId;
  } elseif ($employeeId) {
    $whereCondition .= " AND ah.employee_id = ?";
    $params[] = $employeeId;
  }

  try {
    $stmt = $pdo->prepare("
            SELECT 
                ah.*,
                e.employee_name,
                e.employee_id as employee_number,
                e.email as employee_email,
                e.position as employee_position,
                d.department_name as employee_department,
                b.branch_name as employee_branch,
                a.asset_number,
                a.serial_number,
                a.manufacturer,
                a.model,
                u.username as assigned_by_username,
                CASE 
                    WHEN ah.unassigned_date IS NOT NULL THEN
                        TIMESTAMPDIFF(DAY, ah.assigned_date, ah.unassigned_date)
                    ELSE
                        TIMESTAMPDIFF(DAY, ah.assigned_date, NOW())
                END as assignment_duration_days,
                CASE 
                    WHEN ah.unassigned_date IS NULL AND ah.action_type = 'ASSIGNED' THEN 'Active'
                    ELSE 'Completed'
                END as assignment_status
            FROM assignment_history ah
            JOIN asset a ON ah.asset_id = a.id
            JOIN employees e ON ah.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN branches b ON e.branch_id = b.id
            LEFT JOIN users u ON ah.assigned_by = u.id
            $whereCondition
            ORDER BY ah.assigned_date DESC
        ");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching assignment history with duration: " . $e->getMessage());
    return [];
  }
}

/**
 * Get assignment statistics for a specific asset
 */
function getAssetAssignmentStats($pdo, $assetId)
{
  try {
    $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_assignments,
                COUNT(DISTINCT employee_id) as total_employees_assigned,
                AVG(CASE WHEN unassigned_date IS NOT NULL THEN 
                    TIMESTAMPDIFF(DAY, assigned_date, unassigned_date) 
                    ELSE TIMESTAMPDIFF(DAY, assigned_date, NOW()) END) as avg_assignment_days,
                MIN(assigned_date) as first_assignment,
                MAX(assigned_date) as last_assignment
            FROM assignment_history
            WHERE asset_id = ? AND action_type = 'ASSIGNED'
        ");
    $stmt->execute([$assetId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return null if no assignments found
    return $result['total_assignments'] > 0 ? $result : null;
  } catch (PDOException $e) {
    error_log("Error fetching asset assignment statistics: " . $e->getMessage());
    return null;
  }
}

/**
 * Get assignment statistics for reporting (Overall system stats)
 */
function getAssignmentStatistics($pdo)
{
  try {
    $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_assignments,
                COUNT(CASE WHEN unassigned_date IS NULL AND action_type = 'ASSIGNED' THEN 1 END) as active_assignments,
                COUNT(CASE WHEN unassigned_date IS NOT NULL THEN 1 END) as completed_assignments,
                AVG(CASE WHEN unassigned_date IS NOT NULL THEN 
                    TIMESTAMPDIFF(DAY, assigned_date, unassigned_date) END) as avg_assignment_duration_days,
                COUNT(DISTINCT asset_id) as total_assets_assigned,
                COUNT(DISTINCT employee_id) as total_employees_assigned
            FROM assignment_history
            WHERE action_type = 'ASSIGNED'
        ");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching assignment statistics: " . $e->getMessage());
    return [];
  }
}




/**
 * Get employee summary for quick overview
 * Perfect for dashboard or summary tables
 */
function fetchEmployeeAssetSummary($pdo)
{
  $query = "
        SELECT 
            e.id as employee_id,
            e.employee_id as employee_code,
            e.employee_name,
            e.option_status as status,
            b.branch_name,
            d.department_name,
            
            -- Asset Summary
            COALESCE(asset_summary.total_assets, 0) as total_assets,
            COALESCE(asset_summary.active_assets, 0) as active_assets,
            COALESCE(asset_summary.asset_list, 'No assets assigned') as employee_assets,
            
            -- Last Assignment
            asset_summary.last_assignment_date
            
        FROM employee e
        LEFT JOIN department_employee de ON e.id = de.employee_id
        LEFT JOIN departments d ON de.department_id = d.id
        LEFT JOIN branch b ON d.branch_id = b.id
        LEFT JOIN (
            SELECT 
                a.assigned_to as employee_id,
                COUNT(*) as total_assets,
                COUNT(CASE WHEN a.status = 'Active' THEN 1 END) as active_assets,
                GROUP_CONCAT(
                    CONCAT(a.asset_number, ' (', c.name, ')')
                    ORDER BY a.asset_number ASC 
                    SEPARATOR ', '
                ) as asset_list,
                MAX(a.updated_at) as last_assignment_date
            FROM asset a
            LEFT JOIN inventory i ON a.inventory_id = i.id
            LEFT JOIN categories c ON i.category_id = c.id
            WHERE a.assigned_to IS NOT NULL
            GROUP BY a.assigned_to
        ) asset_summary ON e.id = asset_summary.employee_id
        
        ORDER BY e.employee_name ASC
    ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching employee asset summary: " . $e->getMessage());
    return [];
  }
}
