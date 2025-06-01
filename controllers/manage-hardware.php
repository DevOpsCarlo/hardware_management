<?php
$pageTitle = "Manage Hardware";

// Handling the DELETE request for category deletion
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_inventory_id'])) {
  $inventoryIdToDelete = $_POST['delete_inventory_id'];

  // Check if category ID is valid
  if ($inventoryIdToDelete > 0) {
    // DELETE CATEGORY
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->execute([$inventoryIdToDelete]);

    if ($stmt->rowCount() > 0) {
      // Successfully deleted
      $_SESSION['inventory_deleted'] = "Category deleted successfully!";
    } else {
      // Failed to delete
      $_SESSION['inventory_error'] = "Failed to delete category.";
    }
  } else {
    $_SESSION['inventory_error'] = "Invalid category ID.";
  }

  header("Location: /manage-hardware");
  exit;
}

//  add inventory
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['add-inventory-btn'])) {
  $inventoryId = isset($_POST['inventory_id']) ? intval($_POST['inventory_id']) : 0; // <- NEW
  $manufacturer = isset($_POST['input-manufacturer']) ? htmlspecialchars(trim($_POST['input-manufacturer'])) : "";
  $inputQty = isset($_POST['input-qty']) ? intval($_POST['input-qty']) : 0;
  $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

  if ($manufacturer && $inputQty && $categoryId > 0) {
    // ✅ UPDATE LOGIC if ID is set
    if ($inventoryId > 0) {
      $stmt = $pdo->prepare("UPDATE inventory SET manufacturer = ?, quantity = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
      $stmt->execute([$manufacturer, $inputQty, $categoryId, $inventoryId]);

      if ($stmt->rowCount() > 0) {
        $_SESSION['inventory_updated'] = "Inventory updated successfully.";
      } else {
        $_SESSION['inventory_error'] = "No changes were made.";
      }

      header("Location: /manage-hardware");
      exit;
    }

    // ✅ EXISTING MERGE-OR-INSERT LOGIC
    $stmt = $pdo->prepare("SELECT id, quantity FROM inventory WHERE LOWER(manufacturer) = LOWER(:manufacturer) AND category_id = :categoryId LIMIT 1");
    $stmt->execute(['manufacturer' => $manufacturer, 'categoryId' => $categoryId]);
    $existingManufacturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingManufacturer) {
      $newQty = $existingManufacturer['quantity'] + $inputQty;
      $updateStmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
      $updateStmt->execute([$newQty, $existingManufacturer['id']]);

      $_SESSION['inventory_updated'] = "Successfuly added $inputQty qty for $manufacturer. Total: $newQty";
      header("Location: /manage-hardware");
      exit;
    } else {
      $insertStmt = $pdo->prepare("INSERT INTO inventory (manufacturer, quantity, category_id) VALUES (?, ?, ?)");
      $insertStmt->execute([$manufacturer, $inputQty, $categoryId]);

      $_SESSION['inventory_added'] = "Sucessfully added [$inputQty qty] to [$manufacturer]";
      header("Location: /manage-hardware");
      exit;
    }
  } else {
    $_SESSION['inventory_error'] = "Please enter brand name and quantity";
    header("Location: /manage-hardware");
    exit;
  }
}


// Add this function at the top of your file to map category names to codes
function getCategoryCode($categoryName)
{
  $categoryMapping = [
    'laptop' => '01',
    'laptops' => '01',
    'laptop charger' => '01',
    'laptop mouse' => '01',
    'television' => '02',
    'printer' => '05',
    'printers' => '05',
    'headset' => '01',
    'tv' => '02',
    'bracket' => '02',
    'desktop monitor' => '03',
    'mouse' => '03',
    'system unit' => '03',
    'monitor' => '04',
    'printer' => '05',
    'cellphone' => '06',
    'scanner' => '07',
    'paper shredder' => '08',
    'webcam' => '09',
    'ipad' => '10',
    'projector' => '11',
    'speaker' => '12',
    'amplifier' => '13',
    'microphone' => '14',
    'mixer' => '15'
  ];

  $categoryLower = strtolower(trim($categoryName));
  return isset($categoryMapping[$categoryLower]) ? $categoryMapping[$categoryLower] : '99';
}

// Function to determine if item is a laptop accessory and get suffix
function getLaptopAccessorySuffix($categoryName)
{
  $categoryLower = strtolower(trim($categoryName));
  $accessoryMapping = [
    'laptop charger' => 'C',
    'laptop mouse' => 'M',
    'headset' => 'H',
    'bracket' => 'BK',
    'mouse' => 'MO',
    'system unit' => 'SU',
  ];

  return isset($accessoryMapping[$categoryLower]) ? $accessoryMapping[$categoryLower] : null;
}

// Function to check if item is a main laptop
function isMainLaptop($categoryName)
{
  $categoryLower = strtolower(trim($categoryName));
  return in_array($categoryLower, ['laptop']);
}

// Function to check if item is a laptop charger
function isLaptopCharger($categoryName)
{
  $categoryLower = strtolower(trim($categoryName));
  return in_array($categoryLower, ['laptop charger']);
}

// Update the asset creation section
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assigned-btn'])) {

  // Get form data
  $inventoryId = $_POST['inventory_id'] ?? null;
  $model = $_POST['input-model'] ?? '';
  $serialNumber = $_POST['input-serial-number'] ?? '';
  $ipAddress = $_POST['input-ip-address'] ?? '';
  $dateOfPurchase = $_POST['date-purchase'] ?? null;
  $warrantyDate = $_POST['warranty-date'] ?? null;
  $status = $_POST['status'] ?? 'Available';
  $condition = $_POST['condition'] ?? 'New';

  // Laptop charger data (if applicable)
  $chargerModel = $_POST['charger-model'] ?? '';
  $chargerSerialNumber = $_POST['charger-serial-number'] ?? '';
  $chargerCondition = $_POST['charger-condition'] ?? 'New';

  // Validate required fields
  if (empty($inventoryId)) {
    $_SESSION['inventory_error'] = 'Inventory ID is required but missing.';
    header('Location: /manage-hardware');
    exit;
  }

  try {
    // Get category information from inventory table
    $inventoryStmt = $pdo->prepare("
      SELECT i.category_id, i.manufacturer, c.name as category_name, c.id as category_db_id
      FROM inventory i 
      JOIN categories c ON i.category_id = c.id 
      WHERE i.id = ?
    ");
    $inventoryStmt->execute([$inventoryId]);
    $inventoryData = $inventoryStmt->fetch(PDO::FETCH_ASSOC);

    if (!$inventoryData) {
      $_SESSION['inventory_error'] = 'Inventory item not found.';
      header('Location: /manage-hardware');
      exit;
    }

    // Get the correct category code using your mapping
    $categoryName = $inventoryData['category_name'];
    $categoryCode = getCategoryCode($categoryName);
    $manufacturer = $inventoryData['manufacturer'];
    $isMainLaptop = isMainLaptop($categoryName);
    $isLaptopCharger = isLaptopCharger($categoryName);
    $accessorySuffix = getLaptopAccessorySuffix($categoryName);

    // Generate asset number based on type
    if ($isMainLaptop) {
      // Main laptop gets standard numbering: TRA-01-0001
      $assetNumber = generateNextAssetNumber($categoryCode);
    } elseif ($isLaptopCharger) {
      // Laptop chargers get their own tracked numbering with suffix: TRA-01-0001C
      $assetNumber = generateNextLaptopChargerAssetNumber($categoryCode);
    } elseif ($accessorySuffix) {
      // Other laptop accessories get base number + suffix: TRA-01-0001M
      $baseAssetNumber = getLastAssetNumber($categoryCode);
      if ($baseAssetNumber) {
        // Use the same base number as the last laptop
        $assetNumber = $baseAssetNumber . $accessorySuffix;
      } else {
        // If no laptop exists, create base number and add suffix
        $newBaseNumber = generateNextAssetNumber($categoryCode);
        $assetNumber = $newBaseNumber . $accessorySuffix;
        // Update the tracking for the base number
        updateLastAssetNumber($categoryCode, $newBaseNumber);
      }
    } else {
      // Other categories get their own numbering: TRA-03-0001, TRA-05-0001
      $assetNumber = generateNextAssetNumber($categoryCode);
    }

    // Handle file upload for asset photo
    $assetPhotoPath = null;
    if (isset($_FILES['asset_photo']) && $_FILES['asset_photo']['error'] == UPLOAD_ERR_OK) {
      $uploadDir = 'uploads/assets/';

      // Create directory if it doesn't exist
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generate unique filename to prevent conflicts
      $fileExtension = pathinfo($_FILES['asset_photo']['name'], PATHINFO_EXTENSION);
      $uniqueFilename = uniqid('asset_') . '.' . $fileExtension;
      $assetPhotoPath = $uploadDir . $uniqueFilename;

      if (!move_uploaded_file($_FILES['asset_photo']['tmp_name'], $assetPhotoPath)) {
        $assetPhotoPath = null; // Reset if upload failed
      }
    }

    // Prepare the SQL query to insert the asset
    $stmt = $pdo->prepare("INSERT INTO assets 
            (asset_number, model, category_code, item_type, serial_number, ip_address, status, conditions, date_of_purchase, warranty_date, inventory_id, asset_photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
      $assetNumber,
      $model,
      $categoryCode,
      $categoryName,
      $serialNumber,
      $ipAddress,
      $status,
      $condition,
      $dateOfPurchase,
      $warrantyDate,
      $inventoryId,
      $assetPhotoPath
    ]);

    $laptopAssetId = $pdo->lastInsertId();

    // If this is a main laptop and charger info is provided, create a separate charger asset
    if ($isMainLaptop && !empty($chargerModel)) {
      // Generate charger asset number with its own tracking
      $chargerAssetNumber = generateNextLaptopChargerAssetNumber($categoryCode);

      $chargerStmt = $pdo->prepare("INSERT INTO assets 
              (asset_number, model, category_code, item_type, serial_number, status, conditions, related_laptop_id, inventory_id, date_of_purchase, warranty_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      $chargerStmt->execute([
        $chargerAssetNumber,
        $model,
        $categoryCode, // Same category code "01"
        'Laptop Charger',
        $chargerSerialNumber,
        $status,
        $chargerCondition,
        $laptopAssetId,
        $inventoryId,
        $dateOfPurchase,
        $warrantyDate
      ]);

      // Update charger tracking
      updateLastLaptopChargerAssetNumber($categoryCode, $chargerAssetNumber);
    }

    // Update the last asset number based on item type
    if ($isMainLaptop || (!$accessorySuffix && !$isLaptopCharger)) {
      updateLastAssetNumber($categoryCode, $assetNumber);
    } elseif ($isLaptopCharger) {
      updateLastLaptopChargerAssetNumber($categoryCode, $assetNumber);
    }
    // Count already assigned assets
    $assignedStmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE inventory_id = ?");
    $assignedStmt->execute([$inventoryId]);
    $assignedCount = (int) $assignedStmt->fetchColumn();

    // Get allowed quantity
    $inventoryQtyStmt = $pdo->prepare("SELECT quantity FROM inventory WHERE id = ?");
    $inventoryQtyStmt->execute([$inventoryId]);
    $inventoryQty = (int) $inventoryQtyStmt->fetchColumn();

    // Stop if no remaining units
    if ($assignedCount >= $inventoryQty) {
      $_SESSION['inventory_error'] = 'No more items available to assign from inventory.';
      header('Location: /manage-hardware');
      exit;
    }
  } catch (PDOException $e) {
    $_SESSION['inventory_error'] = 'Error saving asset: ' . $e->getMessage();
    header('Location: /manage-hardware');
    exit;
  }
}

// Function to generate the next asset number
function generateNextAssetNumber($categoryCode)
{
  $lastAssetNumber = getLastAssetNumber($categoryCode);

  if ($lastAssetNumber) {
    // Extract the number part (e.g., 0001 from TRA-01-0001)
    $lastNumber = (int)substr($lastAssetNumber, -4);
    $nextNumber = $lastNumber + 1;
  } else {
    // If no last asset number found, start from 0001
    $nextNumber = 1;
  }

  // Format the next asset number (e.g., TRA-01-0002)
  $nextAssetNumber = "TRA-" . $categoryCode . "-" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);

  return $nextAssetNumber;
}

// Function to generate the next laptop charger asset number
function generateNextLaptopChargerAssetNumber($categoryCode)
{
  $lastChargerAssetNumber = getLastLaptopChargerAssetNumber($categoryCode);

  if ($lastChargerAssetNumber) {
    // Extract the number part (e.g., 0001 from TRA-01-0001C)
    $lastNumber = (int)substr($lastChargerAssetNumber, -5, 4); // Get 4 digits before the 'C'
    $nextNumber = $lastNumber + 1;
  } else {
    // If no last charger asset number found, start from 0001
    $nextNumber = 1;
  }

  // Format the next charger asset number (e.g., TRA-01-0002C)
  $nextAssetNumber = "TRA-" . $categoryCode . "-" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT) . "C";

  return $nextAssetNumber;
}

// Function to fetch the last used asset number
function getLastAssetNumber($categoryCode)
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT last_asset_number FROM asset_number_tracking WHERE category_code = ?");
  $stmt->execute([$categoryCode]);

  $lastAssetNumber = $stmt->fetch(PDO::FETCH_ASSOC);

  return $lastAssetNumber ? $lastAssetNumber['last_asset_number'] : null;
}

// Function to fetch the last used laptop charger asset number
function getLastLaptopChargerAssetNumber($categoryCode)
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT last_charger_asset_number FROM asset_number_tracking WHERE category_code = ?");
  $stmt->execute([$categoryCode]);

  $lastAssetNumber = $stmt->fetch(PDO::FETCH_ASSOC);

  return $lastAssetNumber ? $lastAssetNumber['last_charger_asset_number'] : null;
}

// Function to update the last asset number in the database
function updateLastAssetNumber($categoryCode, $newAssetNumber)
{
  global $pdo;

  // Check if the category already has a record in the tracking table
  $stmt = $pdo->prepare("SELECT * FROM asset_number_tracking WHERE category_code = ?");
  $stmt->execute([$categoryCode]);
  $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existingRecord) {
    // Update the existing record
    $stmt = $pdo->prepare("UPDATE asset_number_tracking SET last_asset_number = ? WHERE category_code = ?");
    $stmt->execute([$newAssetNumber, $categoryCode]);
  } else {
    // Insert a new record if no record exists
    $stmt = $pdo->prepare("INSERT INTO asset_number_tracking (category_code, last_asset_number) VALUES (?, ?)");
    $stmt->execute([$categoryCode, $newAssetNumber]);
  }
}

// Function to update the last laptop charger asset number in the database
function updateLastLaptopChargerAssetNumber($categoryCode, $newAssetNumber)
{
  global $pdo;

  // Check if the category already has a record in the tracking table
  $stmt = $pdo->prepare("SELECT * FROM asset_number_tracking WHERE category_code = ?");
  $stmt->execute([$categoryCode]);
  $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existingRecord) {
    // Update the existing record
    $stmt = $pdo->prepare("UPDATE asset_number_tracking SET last_charger_asset_number = ? WHERE category_code = ?");
    $stmt->execute([$newAssetNumber, $categoryCode]);
  } else {
    // Insert a new record if no record exists - initialize last_asset_number with a default value
    $defaultAssetNumber = "TRA-" . $categoryCode . "-0000"; // Default starting point
    $stmt = $pdo->prepare("INSERT INTO asset_number_tracking (category_code, last_asset_number, last_charger_asset_number) VALUES (?, ?, ?)");
    $stmt->execute([$categoryCode, $defaultAssetNumber, $newAssetNumber]);
  }
}
if (isset($_GET['get_next_asset_number']) && isset($_GET['category_name'])) {
  header('Content-Type: application/json');

  $categoryName = $_GET['category_name'];
  $categoryCode = getCategoryCode($categoryName);
  $isLaptopCharger = isLaptopCharger($categoryName);

  if ($isLaptopCharger) {
    $nextAssetNumber = generateNextLaptopChargerAssetNumber($categoryCode);
  } else {
    $nextAssetNumber = generateNextAssetNumber($categoryCode);
  }

  echo json_encode(['asset_number' => $nextAssetNumber]);
  exit;
}


// ** FETCH DATA FROM DATABASE

//FETCH CATEGORY
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

//FETCH INVENTORY
$stmt = $pdo->prepare("
    SELECT 
        inventory.id AS inventory_id,
        inventory.manufacturer,
        inventory.quantity,
        inventory.category_id,
        inventory.created_at,
        inventory.updated_at,
        categories.name AS category_name
    FROM inventory
    INNER JOIN categories ON inventory.category_id = categories.id
    ORDER BY inventory.manufacturer ASC
");
$stmt->execute();
$inventories = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
    SELECT * FROM assets
   
");
$stmt->execute();
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$assetsByInventory = [];

foreach ($assets as $asset) {
  $invId = $asset['inventory_id']; // must exist in each asset record
  if (!isset($assetsByInventory[$invId])) {
    $assetsByInventory[$invId] = [];
  }
  $assetsByInventory[$invId][] = $asset;
}
// $stmt = $pdo->prepare(" SELECT inventory.id AS inventory_id, inventory.manufacturer, inventory.quantity, inventory.category_id,   inventory.created_at, inventory.updated_at, categories.name AS category_name FROM inventory INNER JOIN categories ON inventory.category_id = categories.id ORDER BY inventory.manufacturer ASC ");
// $stmt->execute();
// $inventories = $stmt->fetchAll(PDO::FETCH_ASSOC);




// **


require("views/manage-hardware.views.php");
