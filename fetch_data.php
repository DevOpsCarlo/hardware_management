<?php
// ============================================
// Dashboard Page
// ============================================

/**
 * Get overall system asset statistics
 * Returns total counts for all asset statuses
 */
function getDashboardAssetStats($pdo)
{
  $query = "
    SELECT 
      COALESCE(inv.total_quantity, 0) as total_assets,
      COALESCE(ast.employee_assigned, 0) as employee_assigned,
      COALESCE(ast.branch_assigned, 0) as branch_assigned,
      COALESCE(ast.department_assigned, 0) as department_assigned,
      COALESCE(ast.available, 0) as available,
      COALESCE(ast.under_maintenance, 0) as under_maintenance,
      COALESCE(ast.uncommitted, 0) as uncommitted
    FROM (
      SELECT SUM(quantity) as total_quantity
      FROM inventory
    ) inv
    LEFT JOIN (
      SELECT 
        SUM(CASE WHEN status = 'Employee Assigned' THEN 1 ELSE 0 END) as employee_assigned,
        SUM(CASE WHEN status = 'Branch Assigned' THEN 1 ELSE 0 END) as branch_assigned,
        SUM(CASE WHEN status = 'Department Assigned' THEN 1 ELSE 0 END) as department_assigned,
        SUM(CASE WHEN status IN ('Available', 'Uncommitted') THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'Under Maintenance' THEN 1 ELSE 0 END) as under_maintenance,
        SUM(CASE WHEN status = 'Uncommitted' THEN 1 ELSE 0 END) as uncommitted
      FROM asset
    ) ast ON 1=1
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
      'total_assets' => (int)($result['total_assets'] ?? 0),
      'employee_assigned' => (int)($result['employee_assigned'] ?? 0),
      'branch_assigned' => (int)($result['branch_assigned'] ?? 0),
      'department_assigned' => (int)($result['department_assigned'] ?? 0),
      'available' => (int)($result['available'] ?? 0),
      'under_maintenance' => (int)($result['under_maintenance'] ?? 0),
      'uncommitted' => (int)($result['uncommitted'] ?? 0)
    ];
  } catch (PDOException $e) {
    error_log("Error fetching dashboard asset stats: " . $e->getMessage());
    return [
      'total_assets' => 0,
      'employee_assigned' => 0,
      'branch_assigned' => 0,
      'department_assigned' => 0,
      'available' => 0,
      'under_maintenance' => 0,
      'uncommitted' => 0
    ];
  }
}

/**
 * Get asset statistics by category (from inventory quantities + asset statuses)
 */
function getAssetStatsByCategory($pdo)
{
  $query = "
    SELECT 
      c.id as category_id,
      c.name as category_name,
      COALESCE(inv.total_quantity, 0) as total,
      COALESCE(ast.assigned, 0) as assigned,
      COALESCE(ast.available, 0) as available,
      COALESCE(ast.in_repair, 0) as in_repair
    FROM categories c
    LEFT JOIN (
      SELECT category_id, SUM(quantity) as total_quantity
      FROM inventory
      GROUP BY category_id
    ) inv ON c.id = inv.category_id
    LEFT JOIN (
      SELECT 
        i.category_id,
        SUM(CASE WHEN a.status = 'Employee Assigned' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN a.status IN ('Available', 'Department Assigned', 'Branch Assigned') THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN a.status = 'Under Maintenance' THEN 1 ELSE 0 END) as in_repair
      FROM asset a
      LEFT JOIN inventory i ON a.inventory_id = i.id
      GROUP BY i.category_id
    ) ast ON c.id = ast.category_id
    WHERE COALESCE(inv.total_quantity, 0) > 0 OR COALESCE(ast.assigned, 0) > 0 OR COALESCE(ast.available, 0) > 0 OR COALESCE(ast.in_repair, 0) > 0
    ORDER BY total DESC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching asset stats by category: " . $e->getMessage());
    return [];
  }
}


/**
 * Get recent asset assignments from all assignment tables
 */
function getRecentAssignments($pdo, $limit = 10)
{
  $allAssignments = [];

  // 1. EMPLOYEE ASSIGNMENTS
  try {
    $query = "
      SELECT 
        a.asset_number,
        COALESCE(i.manufacturer, 'N/A') as manufacturer,
        COALESCE(i.model, '') as model,
        COALESCE(c.name, 'N/A') as category_name,
        CONCAT(COALESCE(e.employee_name, 'Unknown'), ' (Employee)') as assigned_to,
        ah.assigned_date as last_update,
        CASE 
          WHEN ah.unassigned_date IS NOT NULL THEN 'Unassigned'
          ELSE 'Employee Assigned'
        END as status
      FROM assignment_history ah
      INNER JOIN asset a ON ah.asset_id = a.id
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN employee e ON ah.employee_id = e.id
      WHERE ah.action_type = 'ASSIGNED'
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("✓ Employee assignments retrieved: " . count($results));
    $allAssignments = array_merge($allAssignments, $results);
  } catch (PDOException $e) {
    error_log("✗ Employee query error: " . $e->getMessage());
  }

  // 2. BRANCH ASSIGNMENTS
  try {
    $query = "
      SELECT 
        a.asset_number,
        COALESCE(i.manufacturer, 'N/A') as manufacturer,
        COALESCE(i.model, '') as model,
        COALESCE(c.name, 'N/A') as category_name,
        CONCAT(COALESCE(b.branch_name, 'Unknown'), ' (Branch)') as assigned_to,
        abah.assigned_at as last_update,
        CASE 
          WHEN abah.status = 'UNASSIGNED' THEN 'Unassigned'
          ELSE 'Branch Assigned'
        END as status
      FROM asset_branch_assignment_history abah
      INNER JOIN asset a ON abah.asset_id = a.id
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN branch b ON abah.branch_id = b.id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("✓ Branch assignments retrieved: " . count($results));
    $allAssignments = array_merge($allAssignments, $results);
  } catch (PDOException $e) {
    error_log("✗ Branch query error: " . $e->getMessage());
  }

  // 3. DEPARTMENT ASSIGNMENTS
  try {
    $query = "
      SELECT 
        a.asset_number,
        COALESCE(i.manufacturer, 'N/A') as manufacturer,
        COALESCE(i.model, '') as model,
        COALESCE(c.name, 'N/A') as category_name,
        CONCAT(COALESCE(d.department_name, 'Unknown'), ' (Department)') as assigned_to,
        ada.assigned_at as last_update,
        CASE 
          WHEN ada.is_active = 1 THEN 'Department Assigned'
          ELSE 'Returned'
        END as status
      FROM asset_department_assignments ada
      INNER JOIN asset a ON ada.asset_id = a.id
      LEFT JOIN inventory i ON a.inventory_id = i.id
      LEFT JOIN categories c ON i.category_id = c.id
      LEFT JOIN departments d ON ada.department_id = d.id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("✓ Department assignments retrieved: " . count($results));
    $allAssignments = array_merge($allAssignments, $results);
  } catch (PDOException $e) {
    error_log("✗ Department query error: " . $e->getMessage());
  }

  error_log("Total combined assignments before sort: " . count($allAssignments));

  // Sort by last_update descending
  usort($allAssignments, function ($a, $b) {
    $timeA = strtotime($a['last_update'] ?? '1970-01-01');
    $timeB = strtotime($b['last_update'] ?? '1970-01-01');
    return $timeB - $timeA;
  });

  $final = array_slice($allAssignments, 0, $limit);
  error_log("Final result count: " . count($final));

  return $final;
}
// ============================================
// Dashboard Page ⬆️ 
// ============================================





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
            categories.name AS category_name
            
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
            a.assigned_to_branch,
            a.assigned_to,
            b.branch_name,
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
        LEFT JOIN branch b ON a.assigned_to_branch = b.id
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
  $stmt = $pdo->prepare("SELECT id, branch_name, created_at, updated_at FROM branch");
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

// function fetchAssetDetailsById($pdo, $assetId)
// {
//   $query = "
//         SELECT 
//             a.id as asset_id,
//             a.asset_number,
//             a.serial_number,
//             a.ip_address,
//             a.status,
//             a.conditions,
//             a.assigned_to,
//             a.related_laptop_id,
//             a.created_at as asset_created_at,
//             a.updated_at as asset_updated_at,

//             i.id as inventory_id,
//             i.manufacturer,
//             i.model,
//             i.photo,
//             i.quantity,
//             i.purchase_date,
//             i.warranty_years,
//             i.created_at as inventory_created_at,
//             i.updated_at as inventory_updated_at,

//             c.id as category_id,
//             c.name AS category_name,

//             e.employee_name as assigned_employee_name,
//             e.id as assigned_employee_id,
//             e.employee_id as assigned_employee_code,

//             d.department_name as assigned_employee_department,
//             d.id as department_id,
//             b.branch_name as assigned_employee_branch,
//             b.id as branch_id

//         FROM asset a
//         LEFT JOIN inventory i ON a.inventory_id = i.id
//         LEFT JOIN categories c ON i.category_id = c.id
//         LEFT JOIN employee e ON a.assigned_to = e.id
//         LEFT JOIN department_employee de ON e.id = de.employee_id
//         LEFT JOIN departments d ON de.department_id = d.id
//         LEFT JOIN branch b ON d.branch_id = b.id
//         WHERE a.id = ?
//     ";

//   $stmt = $pdo->prepare($query);
//   $stmt->execute([$assetId]);
//   return $stmt->fetch(PDO::FETCH_ASSOC);
// }
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
            a.assigned_to_branch,
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

            e.employee_name as assigned_employee_name,
            e.id as assigned_employee_id,
            e.employee_id as assigned_employee_code,

            d.department_name as assigned_employee_department,
            d.id as department_id,
            b.branch_name as assigned_employee_branch,
            b.id as branch_id,

            -- NEW: branch where the asset is currently assigned
            bb.branch_name AS asset_branch_name,
            bb.id AS asset_branch_id

        FROM asset a
        LEFT JOIN inventory i ON a.inventory_id = i.id
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN employee e ON a.assigned_to = e.id
        LEFT JOIN department_employee de ON e.id = de.employee_id
        LEFT JOIN departments d ON de.department_id = d.id
        LEFT JOIN branch b ON d.branch_id = b.id
        LEFT JOIN branch bb ON a.assigned_to_branch = bb.id  -- ★ ADDED

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




// Branch fetch 
// Add these functions to your database functions file (e.g., database.php or functions.php)

/**
 * Get asset statistics for a specific branch
 * Returns: total_assets, assigned_count, unassigned_count, in_repair_count
 */
// function getBranchAssetStats($pdo, $branchId)
// {
//   $query = "
//     SELECT 
//       COUNT(*) as total_assets,
//       SUM(CASE WHEN a.status = 'Employee Assigned' THEN 1 ELSE 0 END) as assigned_count,
//       SUM(CASE WHEN a.status = 'Branch Assigned' OR a.status = 'Available' THEN 1 ELSE 0 END) as unassigned_count,
//       SUM(CASE WHEN a.status = 'Under Maintenance' THEN 1 ELSE 0 END) as in_repair_count
//     FROM asset a
//     WHERE a.assigned_to_branch = ?
//   ";

//   try {
//     $stmt = $pdo->prepare($query);
//     $stmt->execute([$branchId]);
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);

//     return [
//       'total_assets' => $result['total_assets'] ?? 0,
//       'assigned_count' => $result['assigned_count'] ?? 0,
//       'unassigned_count' => $result['unassigned_count'] ?? 0,
//       'in_repair_count' => $result['in_repair_count'] ?? 0
//     ];
//   } catch (PDOException $e) {
//     error_log("Error fetching branch asset stats: " . $e->getMessage());
//     return [
//       'total_assets' => 0,
//       'assigned_count' => 0,
//       'unassigned_count' => 0,
//       'in_repair_count' => 0
//     ];
//   }
// }
function getBranchAssetStats($pdo, $branchId)
{
  $query = "
    SELECT 
      COUNT(*) as total_assets,
      SUM(CASE WHEN a.status = 'Employee Assigned' THEN 1 ELSE 0 END) as assigned_count,
      SUM(CASE WHEN a.status = 'Branch Assigned' OR a.status = 'Available' THEN 1 ELSE 0 END) as unassigned_count,
      SUM(CASE WHEN a.status = 'Under Maintenance' THEN 1 ELSE 0 END) as in_repair_count,
      SUM(CASE WHEN a.status = 'Uncommitted' THEN 1 ELSE 0 END) as uncommitted_count
    FROM asset a
    WHERE a.assigned_to_branch = ?
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
      'total_assets'      => $result['total_assets'] ?? 0,
      'assigned_count'    => $result['assigned_count'] ?? 0,
      'unassigned_count'  => $result['unassigned_count'] ?? 0,
      'in_repair_count'   => $result['in_repair_count'] ?? 0,
      'uncommitted_count' => $result['uncommitted_count'] ?? 0
    ];
  } catch (PDOException $e) {
    error_log("Error fetching branch asset stats: " . $e->getMessage());
    return [
      'total_assets'      => 0,
      'assigned_count'    => 0,
      'unassigned_count'  => 0,
      'in_repair_count'   => 0,
      'uncommitted_count' => 0
    ];
  }
}
/**
 * Get all branches with asset statistics
 * Returns: branches array with asset counts included
 */
function fetchBranchesWithAssetStats($pdo)
{
  try {
    // First, fetch all branches
    $stmt = $pdo->prepare("SELECT id, branch_name, created_at, updated_at FROM branch ORDER BY branch_name ASC");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enhance each branch with asset statistics
    foreach ($branches as &$branch) {
      $stats = getBranchAssetStats($pdo, $branch['id']);
      $branch['total_assets'] = $stats['total_assets'];
      $branch['assigned_count'] = $stats['assigned_count'];
      $branch['unassigned_count'] = $stats['unassigned_count'];
      $branch['in_repair_count'] = $stats['in_repair_count'];
      $branch['uncommitted_count'] = $stats['uncommitted_count'];
    }

    return $branches;
  } catch (PDOException $e) {
    error_log("Error fetching branches with asset stats: " . $e->getMessage());
    return [];
  }
}

/**
 * Get detailed asset list for a specific branch
 * Returns: all assets assigned to that branch with employee info
 */
function getBranchAssets($pdo, $branchId)
{
  $query = "
    SELECT 
      a.id as asset_id,
      a.asset_number,
      a.serial_number,
      a.status,
      a.conditions,
      a.assigned_to,
      i.manufacturer,
      i.model,
      c.name as category_name,
      e.employee_name,
      e.employee_id as employee_code,
      b.branch_name,
      a.created_at,
      a.updated_at
    FROM asset a
    LEFT JOIN inventory i ON a.inventory_id = i.id
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN employee e ON a.assigned_to = e.id
    LEFT JOIN branch b ON a.assigned_to_branch = b.id
    WHERE a.assigned_to_branch = ?
    ORDER BY a.status DESC, a.asset_number ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching branch assets: " . $e->getMessage());
    return [];
  }
}




/**
 * Get assigned assets for a specific branch (grouped by employee)
 */
function getBranchAssignedAssets($pdo, $branchId)
{
  $query = "
    SELECT 
      e.id as employee_id,
      e.employee_name,
      e.employee_id as employee_code,
      COUNT(*) as assigned_count,
      GROUP_CONCAT(
        CONCAT(a.asset_number, ' (', c.name, ')')
        ORDER BY a.asset_number ASC
        SEPARATOR ', '
      ) as asset_list
    FROM asset a
    LEFT JOIN employee e ON a.assigned_to = e.id
    LEFT JOIN categories c ON a.inventory_id IN (SELECT inventory_id FROM asset WHERE id = a.id)
    WHERE a.assigned_to_branch = ? AND a.status = 'Employee Assigned'
    GROUP BY e.id, e.employee_name, e.employee_code
    ORDER BY e.employee_name ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching branch assigned assets: " . $e->getMessage());
    return [];
  }
}

/**
 * Get unassigned assets for a specific branch
 */
function getBranchUnassignedAssets($pdo, $branchId)
{
  $query = "
    SELECT 
      a.id as asset_id,
      a.asset_number,
      a.serial_number,
      a.status,
      i.manufacturer,
      i.model,
      c.name as category_name,
      a.created_at
    FROM asset a
    LEFT JOIN inventory i ON a.inventory_id = i.id
    LEFT JOIN categories c ON i.category_id = c.id
    WHERE a.assigned_to_branch = ? AND a.assigned_to IS NULL
    ORDER BY a.asset_number ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching branch unassigned assets: " . $e->getMessage());
    return [];
  }
}


// Add these functions to your database functions file

/**
 * Assign asset to branch
 * Creates an entry in asset_branch_assignment_history
 */
function assignAssetToBranch($pdo, $assetId, $branchId, $assignedBy = null, $status = 'Branch Assigned')
{
  try {
    // Update asset status
    $stmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to_branch = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    ");
    $stmt->execute([$branchId, $status, $assetId]);

    // Log in asset_branch_assignment_history
    $stmt = $pdo->prepare("
      INSERT INTO asset_branch_assignment_history 
      (asset_id, branch_id, assigned_at, assigned_by, status) 
      VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?)
    ");
    $stmt->execute([$assetId, $branchId, $assignedBy, $status]);

    return true;
  } catch (PDOException $e) {
    error_log("Error assigning asset to branch: " . $e->getMessage());
    return false;
  }
}

/**
 * Unassign asset from branch
 * Closes the assignment record in asset_branch_assignment_history
 */
function unassignAssetFromBranch($pdo, $assetId, $branchId)
{
  try {
    // Update asset status
    $stmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to_branch = NULL, status = 'Available', updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    ");
    $stmt->execute([$assetId]);

    // Close assignment record
    $stmt = $pdo->prepare("
      UPDATE asset_branch_assignment_history 
      SET unassigned_at = CURRENT_TIMESTAMP 
      WHERE asset_id = ? AND branch_id = ? AND unassigned_at IS NULL
    ");
    $stmt->execute([$assetId, $branchId]);

    return true;
  } catch (PDOException $e) {
    error_log("Error unassigning asset from branch: " . $e->getMessage());
    return false;
  }
}

/**
 * Get all assets currently assigned to a branch
 */
function getBranchCurrentAssets($pdo, $branchId)
{
  $query = "
    SELECT 
      a.id as asset_id,
      a.asset_number,
      a.serial_number,
      a.ip_address,
      a.status,
      a.conditions,
      i.id as inventory_id,
      i.manufacturer,
      i.model,
      i.photo,
      i.purchase_date,
      i.warranty_years,
      c.id as category_id,
      c.name as category_name,
      b.id as branch_id,
      b.branch_name,
      abah.assigned_at,
      abah.assigned_by,
      e.employee_name as assigned_by_name
    FROM asset_branch_assignment_history abah
    JOIN asset a ON abah.asset_id = a.id
    JOIN inventory i ON a.inventory_id = i.id
    JOIN categories c ON i.category_id = c.id
    JOIN branch b ON abah.branch_id = b.id
    LEFT JOIN employee e ON abah.assigned_by = e.id
    WHERE abah.branch_id = ? AND abah.unassigned_at IS NULL
    ORDER BY i.manufacturer ASC, a.asset_number ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching branch current assets: " . $e->getMessage());
    return [];
  }
}

/**
 * Assign asset from branch to department
 * Updates the asset assignment chain
 */
function assignAssetToDepartment($pdo, $assetId, $departmentId, $assignedBy = null)
{
  try {
    // Get department info to get branch_id
    $stmt = $pdo->prepare("SELECT branch_id FROM departments WHERE id = ?");
    $stmt->execute([$departmentId]);
    $branchId = $stmt->fetchColumn();

    if (!$branchId) {
      throw new Exception("Department not found");
    }

    // Update asset - move from branch level to department level
    $stmt = $pdo->prepare("
      UPDATE asset 
      SET status = 'Department Assigned', updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    ");
    $stmt->execute([$assetId]);

    // Log in assignment history (you may want to create a new table for department assignments too)
    $stmt = $pdo->prepare("
      INSERT INTO assignment_history 
      (asset_id, action_type, assigned_by, notes) 
      VALUES (?, 'DEPARTMENT_ASSIGNED', ?, CONCAT('Assigned to department ID: ', ?))
    ");
    $stmt->execute([$assetId, $assignedBy, $departmentId]);

    return true;
  } catch (Exception $e) {
    error_log("Error assigning asset to department: " . $e->getMessage());
    return false;
  }
}

/**
 * Assign asset from department to employee
 */
function assignAssetToEmployee($pdo, $assetId, $employeeId, $assignedBy = null)
{
  try {
    // Update asset - move to employee level
    $stmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to = ?, status = 'Employee Assigned', updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    ");
    $stmt->execute([$employeeId, $assetId]);

    // Log in assignment_history
    $stmt = $pdo->prepare("
      INSERT INTO assignment_history 
      (asset_id, employee_id, action_type, assigned_by, assigned_date) 
      VALUES (?, ?, 'ASSIGNED', ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$assetId, $employeeId, $assignedBy]);

    return true;
  } catch (PDOException $e) {
    error_log("Error assigning asset to employee: " . $e->getMessage());
    return false;
  }
}

/**
 * Unassign asset from employee (returns to department level)
 */
function unassignAssetFromEmployee($pdo, $assetId)
{
  try {
    // Get employee_id before clearing
    $stmt = $pdo->prepare("SELECT assigned_to FROM asset WHERE id = ?");
    $stmt->execute([$assetId]);
    $employeeId = $stmt->fetchColumn();

    // Close employee assignment in assignment_history
    $stmt = $pdo->prepare("
      UPDATE assignment_history 
      SET unassigned_date = CURRENT_TIMESTAMP 
      WHERE asset_id = ? AND employee_id = ? AND unassigned_date IS NULL AND action_type = 'ASSIGNED'
    ");
    $stmt->execute([$assetId, $employeeId]);

    // Update asset - return to department level
    $stmt = $pdo->prepare("
      UPDATE asset 
      SET assigned_to = NULL, status = 'Department Assigned', updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    ");
    $stmt->execute([$assetId]);

    return true;
  } catch (PDOException $e) {
    error_log("Error unassigning asset from employee: " . $e->getMessage());
    return false;
  }
}


function getAssetAssignmentPath($pdo, $assetId)
{
  $query = "
    SELECT 
      a.id as asset_id,
      a.asset_number,
      a.status,
      a.assigned_department_id,
      b.id as branch_id,
      b.branch_name,
      abah.assigned_at as assigned_to_branch_date,
      d.id as department_id,
      d.department_name,
      e.id as employee_id,
      e.employee_name,
      ah.assigned_date as assigned_to_employee_date
    FROM asset a
    LEFT JOIN asset_branch_assignment_history abah ON a.id = abah.asset_id AND abah.unassigned_at IS NULL
    LEFT JOIN branch b ON abah.branch_id = b.id 
    LEFT JOIN departments d ON a.assigned_department_id = d.id
    LEFT JOIN assignment_history ah ON a.id = ah.asset_id AND ah.unassigned_date IS NULL AND ah.action_type = 'ASSIGNED'
    LEFT JOIN employee e ON ah.employee_id = e.id
    WHERE a.id = ?
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$assetId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching asset assignment path: " . $e->getMessage());
    return null;
  }
}
/**
 * Get branch summary with asset counts by status
 */
function getBranchAssetSummary($pdo, $branchId)
{
  $query = "
    SELECT 
      b.id as branch_id,
      b.branch_name,
      COUNT(a.id) as total_assets,
      SUM(CASE WHEN a.status = 'Branch Assigned' THEN 1 ELSE 0 END) as branch_level_assets,
      SUM(CASE WHEN a.status = 'Department Assigned' THEN 1 ELSE 0 END) as department_level_assets,
      SUM(CASE WHEN a.status = 'Employee Assigned' THEN 1 ELSE 0 END) as employee_level_assets,
      SUM(CASE WHEN a.status = 'Under Maintenance' THEN 1 ELSE 0 END) as under_maintenance,
      SUM(CASE WHEN a.status = 'Uncommitted' THEN 1 ELSE 0 END) as defective
    FROM branch b
    LEFT JOIN asset_branch_assignment_history abah ON b.id = abah.branch_id AND abah.unassigned_at IS NULL
    LEFT JOIN asset a ON abah.asset_id = a.id
    WHERE b.id = ?
    GROUP BY b.id, b.branch_name
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branchId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching branch asset summary: " . $e->getMessage());
    return null;
  }
}

function  fetchDepartmentsWithAssetStats($pdo, $branchId)
{
  try {
    // First, fetch departments
    $stmt = $pdo->prepare("
      SELECT id, department_name, department_head, created_at, updated_at 
      FROM departments 
      WHERE branch_id = ?
      ORDER BY department_name ASC
    ");
    $stmt->execute([$branchId]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enhance each branch with asset statistics
    foreach ($departments as &$department) {
      $stats = getDepartmentAssetSummary($pdo, $department['id']);
      $department['total_assets'] = $stats['total_assets'];
      $department['assigned_count'] = $stats['assigned_count'];
      $department['unassigned_count'] = $stats['unassigned_count'];
      $department['in_repair_count'] = $stats['in_repair_count'];
      $department['uncommitted_count'] = $stats['uncommitted_count'];
    }

    return $departments;
  } catch (PDOException $e) {
    error_log("Error fetching branches with asset stats: " . $e->getMessage());
    return [];
  }
}

function getDepartmentAssetSummary($pdo, $departmentId)
{
  $query = "
    SELECT 
      COUNT(*) as total_assets,
      SUM(CASE WHEN a.status = 'Employee Assigned' THEN 1 ELSE 0 END) as assigned_count,
      SUM(CASE WHEN a.status = 'Department Assigned' OR a.status = 'Available' THEN 1 ELSE 0 END) as unassigned_count,
      SUM(CASE WHEN a.status = 'Under Maintenance' THEN 1 ELSE 0 END) as in_repair_count,
      SUM(CASE WHEN a.status = 'Uncommitted' THEN 1 ELSE 0 END) as uncommitted_count
    FROM asset a
    WHERE a.assigned_department_id = ?
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$departmentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
      'total_assets'      => $result['total_assets'] ?? 0,
      'assigned_count'    => $result['assigned_count'] ?? 0,
      'unassigned_count'  => $result['unassigned_count'] ?? 0,
      'in_repair_count'   => $result['in_repair_count'] ?? 0,
      'uncommitted_count' => $result['uncommitted_count'] ?? 0
    ];
  } catch (PDOException $e) {
    error_log("Error fetching branch asset stats: " . $e->getMessage());
    return [
      'total_assets'      => 0,
      'assigned_count'    => 0,
      'unassigned_count'  => 0,
      'in_repair_count'   => 0,
      'uncommitted_count' => 0
    ];
  }
}


function getUnifiedAssignmentHistorySimple($pdo, $assetId)
{
  try {

    $query = "
            SELECT * FROM (
                
                /* ============================
                   BRANCH ASSIGNMENTS
                ============================= */
                SELECT 
                    abah.id,
                    'BRANCH' AS type,
                    b.branch_name AS assigned_to,
                    NULL AS employee_code,
                    abah.assigned_at AS assigned_date,
                    abah.unassigned_at AS unassigned_date,
                    abah.status AS status,
                    NULL AS action_type,
                    abah.assigned_by,
                    NULL AS file_path,
                    CASE 
                        WHEN abah.unassigned_at IS NOT NULL THEN
                            TIMESTAMPDIFF(DAY, abah.assigned_at, abah.unassigned_at)
                        ELSE
                            TIMESTAMPDIFF(DAY, abah.assigned_at, NOW())
                    END AS days_assigned
                FROM asset_branch_assignment_history abah
                LEFT JOIN branch b ON abah.branch_id = b.id
                WHERE abah.asset_id = ?

                UNION ALL

                /* ============================
                   EMPLOYEE ASSIGNMENTS
                ============================= */
                SELECT
                    ah.id,
                    'EMPLOYEE' AS type,
                    CASE 
                        WHEN e.employee_id IS NOT NULL AND e.employee_id != '' 
                        THEN CONCAT(e.employee_name, ' (', e.employee_id, ')')
                        ELSE e.employee_name
                    END AS assigned_to,
                    e.employee_id AS employee_code,
                    ah.assigned_date,
                    ah.unassigned_date,
                    NULL AS status,
                    ah.action_type,
                    ah.assigned_by,
                    ah.file_path,
                    CASE 
                        WHEN ah.unassigned_date IS NOT NULL THEN
                            TIMESTAMPDIFF(DAY, ah.assigned_date, ah.unassigned_date)
                        ELSE
                            TIMESTAMPDIFF(DAY, ah.assigned_date, NOW())
                    END AS days_assigned
                FROM assignment_history ah
                LEFT JOIN employee e ON ah.employee_id = e.id
                WHERE ah.asset_id = ?

            ) AS unified

            ORDER BY assigned_date DESC
        ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$assetId, $assetId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Unified assignment history error: " . $e->getMessage());
    return [];
  }
}
/**
 * Get department asset summary
 */
// function getDepartmentAssetSummary($pdo, $departmentId)
// {
//   $stmt = $pdo->prepare("
//     SELECT 
//       id,
//       department_id,
//       total_assets,
//       assigned_to_employees,
//       available_at_department,
//       updated_at
//     FROM department_asset_summary
//     WHERE department_id = ?
//   ");
//   $stmt->execute([$departmentId]);
//   return $stmt->fetch(PDO::FETCH_ASSOC);
// }



// ============================================
// HELPER FUNCTION: Update Department Asset Summary
// ============================================
function updateDepartmentAssetSummary($pdo, $departmentId)
{
  try {
    $stmt = $pdo->prepare("
      SELECT COUNT(*) FROM asset_department_assignments 
      WHERE department_id = ? AND is_active = TRUE
    ");
    $stmt->execute([$departmentId]);
    $totalAssets = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT COUNT(*) FROM asset_employee_assignments 
      WHERE department_id = ? AND is_active = TRUE
    ");
    $stmt->execute([$departmentId]);
    $assignedToEmployees = $stmt->fetchColumn();

    $availableAtDepartment = $totalAssets - $assignedToEmployees;

    $stmt = $pdo->prepare("
      INSERT INTO department_asset_summary (department_id, total_assets, assigned_to_employees, available_at_department)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE 
        total_assets = ?, 
        assigned_to_employees = ?, 
        available_at_department = ?,
        updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$departmentId, $totalAssets, $assignedToEmployees, $availableAtDepartment, $totalAssets, $assignedToEmployees, $availableAtDepartment]);
  } catch (Exception $e) {
    error_log("Error updating department asset summary: " . $e->getMessage());
  }
}




function getDepartmentAssets($pdo, $departmentId)
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
      i.manufacturer,
      i.model,
      i.photo,
      c.name as category_name,
      c.id as category_id,
      e.employee_name,
      e.employee_id as employee_code,
      e.id as employee_id,
      a.created_at,
      a.updated_at
    FROM asset a
    LEFT JOIN inventory i ON a.inventory_id = i.id
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN employee e ON a.assigned_to = e.id
    WHERE a.assigned_department_id = ?
    ORDER BY a.asset_number ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$departmentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching department assets: " . $e->getMessage());
    return [];
  }
}

// Alternative function if assets are linked through employee department assignments
function getDepartmentAssetsViaEmployees($pdo, $departmentId)
{
  $query = "
    SELECT DISTINCT
      a.id as asset_id,
      a.asset_number,
      a.serial_number,
      a.ip_address,
      a.status,
      a.conditions,
      a.assigned_to,
      i.manufacturer,
      i.model,
      i.photo,
      c.name as category_name,
      c.id as category_id,
      e.employee_name,
      e.employee_id as employee_code,
      e.id as employee_id,
      a.created_at,
      a.updated_at
    FROM asset a
    LEFT JOIN inventory i ON a.inventory_id = i.id
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN employee e ON a.assigned_to = e.id
    LEFT JOIN department_employee de ON e.id = de.employee_id
    WHERE de.department_id = ? AND a.assigned_to IS NOT NULL
    ORDER BY a.asset_number ASC
  ";

  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$departmentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching department assets via employees: " . $e->getMessage());
    return [];
  }
}
