<?php
$pageTitle = "Manage Hardware";

// Handling the DELETE request for category deletion

function getExistingPhotoForManufacturerModel($pdo, $manufacturer, $model)
{
  $stmt = $pdo->prepare("
    SELECT photo 
    FROM inventory 
    WHERE manufacturer = ? AND model = ? AND photo IS NOT NULL AND photo != ''
    LIMIT 1
  ");
  $stmt->execute([$manufacturer, $model]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  return $result ? $result['photo'] : null;
}


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

  header("Location: /manage-hardware/add-inventory");
  exit;
}

function generateBatchId($pdo)
{
  $date = date('Ymd'); // Format: 20250614

  // Get the count of batches created today
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE DATE(created_at) = CURDATE()");
  $stmt->execute();
  $count = $stmt->fetchColumn();

  // Increment count for new batch
  $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

  $batch_id = "BATCH-{$date}-{$sequence}";

  // Ensure uniqueness (in case of concurrent requests)
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE batch_id = ?");
  $stmt->execute([$batch_id]);

  if ($stmt->fetchColumn() > 0) {
    // If batch_id exists, add random suffix
    $batch_id .= '-' . substr(uniqid(), -3);
  }

  return $batch_id;
}

function addLaptopCharger($pdo, $laptopQuantity, $laptopManufacturer, $model, $purchaseDate)
{
  // Get or create "Laptop Charger" category
  $stmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = 'laptop charger' LIMIT 1");
  $stmt->execute();
  $chargerCategory = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$chargerCategory) {
    // Create laptop charger category if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO categories (name, created_at) VALUES ('Laptop Charger', NOW())");
    $stmt->execute();
    $chargerCategoryId = $pdo->lastInsertId();
  } else {
    $chargerCategoryId = $chargerCategory['id'];
  }

  // Generate batch ID for charger
  $chargerBatchId = generateBatchId($pdo);

  // Insert laptop charger with same quantity as laptop
  $stmt = $pdo->prepare("INSERT INTO inventory (batch_id, manufacturer, model, purchase_date, quantity, warranty_years, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, ?, NOW(), NOW())");
  $stmt->execute([
    $chargerBatchId,
    $laptopManufacturer,
    $model,
    $purchaseDate,
    $laptopQuantity,
    $chargerCategoryId
  ]);

  return $chargerBatchId;
}


// Main form processing - your existing code with batch ID integration


// if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['add-inventory-btn'])) {
//   $inventoryId = intval($_POST['inventory_id'] ?? 0);
//   $categoryId = intval($_POST['category_id'] ?? 0);
//   $manufacturer = htmlspecialchars(trim($_POST['input-manufacturer'] ?? ''));
//   $model = htmlspecialchars(trim($_POST['input-model'] ?? ''));
//   $purchaseDate = htmlspecialchars(trim($_POST['purchase-date'] ?? ''));
//   $quantity = intval($_POST['input-qty'] ?? 0);
//   $warranty = intval($_POST['input-warranty'] ?? 0);
//   $photoPath = null;

//   // Handle file upload (your existing logic)
//   if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
//     $uploadDir = 'uploads/assets/';

//     // Create directory if it doesn't exist
//     if (!is_dir($uploadDir)) {
//       mkdir($uploadDir, 0755, true);
//     }

//     // Validate file type
//     $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
//     $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

//     if (in_array($fileExtension, $allowedExtensions)) {
//       // Generate unique filename to prevent conflicts
//       $uniqueFilename = uniqid('inventory_') . '.' . $fileExtension;
//       $photoPath = $uploadDir . $uniqueFilename;

//       if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
//         $photoPath = null; // Reset if upload failed
//         $_SESSION['inventory_error'] = "Failed to upload photo.";
//       }
//     } else {
//       $_SESSION['inventory_error'] = "Invalid file type. Please upload JPG, PNG, or GIF files only.";
//       header("Location: /manage-hardware/add-inventory");
//       exit;
//     }
//   }

//   // Validation (your existing logic)
//   if ($manufacturer && $quantity > 0 && $categoryId > 0) {

//     // UPDATE EXISTING INVENTORY (Edit mode)
//     if ($inventoryId > 0) {
//       // If a new photo is uploaded, include it in the update
//       if ($photoPath) {
//         $stmt = $pdo->prepare("UPDATE inventory SET manufacturer = ?, model = ?, purchase_date = ?, quantity = ?, warranty_years = ?, photo = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
//         $stmt->execute([$manufacturer, $model, $purchaseDate, $quantity, $warranty, $photoPath, $categoryId, $inventoryId]);
//       } else {
//         // If no new photo is uploaded, don't update the photo_path field (retain existing photo)
//         $stmt = $pdo->prepare("UPDATE inventory SET manufacturer = ?, model = ?, purchase_date = ?, quantity = ?, warranty_years = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
//         $stmt->execute([$manufacturer, $model, $purchaseDate, $quantity, $warranty, $categoryId, $inventoryId]);
//       }

//       if ($stmt->rowCount() > 0) {
//         $_SESSION['inventory_updated'] = "Inventory updated successfully.";
//       } else {
//         $_SESSION['inventory_error'] = "No changes were made.";
//       }

//       header("Location: /manage-hardware/add-inventory");
//       exit;
//     }

//     // INSERT NEW INVENTORY (Always create new record - no merging) - MODIFIED WITH BATCH ID
//     // Generate batch ID for new inventory
//     $batchId = generateBatchId($pdo);

//     if ($photoPath) {
//       $insertStmt = $pdo->prepare("INSERT INTO inventory (batch_id, manufacturer, model, purchase_date, quantity, warranty_years, photo, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
//       $insertStmt->execute([$batchId, $manufacturer, $model, $purchaseDate, $quantity, $warranty, $photoPath, $categoryId]);
//     } else {
//       $insertStmt = $pdo->prepare("INSERT INTO inventory (batch_id, manufacturer, model, purchase_date, quantity, warranty_years, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
//       $insertStmt->execute([$batchId, $manufacturer, $model, $purchaseDate, $quantity, $warranty, $categoryId]);
//     }

//     // Check if the added item is a laptop, then auto-add charger
//     $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
//     $stmt->execute([$categoryId]);
//     $categoryName = $stmt->fetchColumn();

//     $chargerBatchId = null;
//     if (strtolower($categoryName) === 'laptop') {
//       $chargerBatchId = addLaptopCharger($pdo, $quantity, $manufacturer, $model, $purchaseDate);
//     }

//     // Success message
//     if ($chargerBatchId) {
//       $_SESSION['inventory_added'] = "Successfully added [$quantity qty] of [$manufacturer $model] - Batch ID: $batchId<br>Automatically added [$quantity qty] of [$manufacturer Laptop Charger] - Batch ID: $chargerBatchId";
//     } else {
//       $_SESSION['inventory_added'] = "Successfully added [$quantity qty] of [$manufacturer $model] - Batch ID: $batchId";
//     }

//     header("Location: /manage-hardware/add-inventory");
//     exit;
//   } else {
//     $_SESSION['inventory_error'] = "Please enter manufacturer name, quantity, and select a category.";
//     header("Location: /manage-hardware/add-inventory");
//     exit;
//   }
// }


if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['add-inventory-btn'])) {
  $inventoryId = intval($_POST['inventory_id'] ?? 0);
  $categoryId = intval($_POST['category_id'] ?? 0);
  $manufacturer = htmlspecialchars(trim($_POST['input-manufacturer'] ?? ''));
  $model = htmlspecialchars(trim($_POST['input-model'] ?? ''));
  $purchaseDate = htmlspecialchars(trim($_POST['purchase-date'] ?? ''));
  $quantity = intval($_POST['input-qty'] ?? 0);
  $warranty = intval($_POST['input-warranty'] ?? 0);
  $photoPath = null;

  // Handle file upload
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/assets/';

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if (in_array($fileExtension, $allowedExtensions)) {
      $uniqueFilename = uniqid('inventory_') . '.' . $fileExtension;
      $photoPath = $uploadDir . $uniqueFilename;

      if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
        $photoPath = null;
        $_SESSION['inventory_error'] = "Failed to upload photo.";
      }
    } else {
      $_SESSION['inventory_error'] = "Invalid file type. Please upload JPG, PNG, or GIF files only.";
      header("Location: /manage-hardware/add-inventory");
      exit;
    }
  } else {
    // ========== NEW: If no photo uploaded, check for existing photo ==========
    $existingPhoto = getExistingPhotoForManufacturerModel($pdo, $manufacturer, $model);
    if ($existingPhoto) {
      $photoPath = $existingPhoto;
    }
    // ========== END NEW LOGIC ==========
  }

  // Validation
  if ($manufacturer && $quantity > 0 && $categoryId > 0) {

    // UPDATE EXISTING INVENTORY (Edit mode)
    if ($inventoryId > 0) {
      if ($photoPath) {
        $stmt = $pdo->prepare("UPDATE inventory SET manufacturer = ?, model = ?, purchase_date = ?, quantity = ?, warranty_years = ?, photo = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$manufacturer, $model, $purchaseDate, $quantity, $warranty, $photoPath, $categoryId, $inventoryId]);
      } else {
        $stmt = $pdo->prepare("UPDATE inventory SET manufacturer = ?, model = ?, purchase_date = ?, quantity = ?, warranty_years = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$manufacturer, $model, $purchaseDate, $quantity, $warranty, $categoryId, $inventoryId]);
      }

      if ($stmt->rowCount() > 0) {
        $_SESSION['inventory_updated'] = "Inventory updated successfully.";
      } else {
        $_SESSION['inventory_error'] = "No changes were made.";
      }

      header("Location: /manage-hardware/add-inventory");
      exit;
    }

    // INSERT NEW INVENTORY
    $batchId = generateBatchId($pdo);

    if ($photoPath) {
      $insertStmt = $pdo->prepare("INSERT INTO inventory (batch_id, manufacturer, model, purchase_date, quantity, warranty_years, photo, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
      $insertStmt->execute([$batchId, $manufacturer, $model, $purchaseDate, $quantity, $warranty, $photoPath, $categoryId]);
    } else {
      $insertStmt = $pdo->prepare("INSERT INTO inventory (batch_id, manufacturer, model, purchase_date, quantity, warranty_years, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
      $insertStmt->execute([$batchId, $manufacturer, $model, $purchaseDate, $quantity, $warranty, $categoryId]);
    }

    // Check if the added item is a laptop, then auto-add charger
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $categoryName = $stmt->fetchColumn();

    $chargerBatchId = null;
    if (strtolower($categoryName) === 'laptop') {
      $chargerBatchId = addLaptopCharger($pdo, $quantity, $manufacturer, $model, $purchaseDate);
    }

    // Success message
    if ($chargerBatchId) {
      $_SESSION['inventory_added'] = "Successfully added [$quantity qty] of [$manufacturer $model] - Batch ID: $batchId<br>Automatically added [$quantity qty] of [$manufacturer Laptop Charger] - Batch ID: $chargerBatchId";
    } else {
      $_SESSION['inventory_added'] = "Successfully added [$quantity qty] of [$manufacturer $model] - Batch ID: $batchId";
    }

    header("Location: /manage-hardware/add-inventory");
    exit;
  } else {
    $_SESSION['inventory_error'] = "Please enter manufacturer name, quantity, and select a category.";
    header("Location: /manage-hardware/add-inventory");
    exit;
  }
}

function getAllInventory($pdo)
{
  $sql = "SELECT 
            MIN(i.id) AS inventory_id,  -- Use MIN to get a representative ID
            i.manufacturer, 
            i.model, 
            MIN(i.purchase_date) AS purchase_date,  -- Show earliest purchase date
            MIN(i.warranty_years) AS warranty_years,  -- Show minimum warranty
            SUM(i.quantity) AS total_quantity,
            i.category_id,
            c.name AS category_name,
            GROUP_CONCAT(DISTINCT i.photo) AS photos
          FROM inventory i 
          LEFT JOIN categories c ON i.category_id = c.id 
          GROUP BY i.manufacturer, i.model, i.category_id, c.name
          ORDER BY i.manufacturer, i.model";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get inventory by ID (for editing)
function getInventoryById($pdo, $id)
{
  $sql = "SELECT i.*, c.name as category_name 
            FROM inventory i 
            LEFT JOIN categories c ON i.category_id = c.id 
            WHERE i.id = ?";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get inventory summary by manufacturer and model
function getInventorySummary($pdo)
{
  $sql = "SELECT manufacturer, model, 
                    COUNT(*) as batch_count,
                    SUM(quantity) as total_quantity,
                    MIN(purchase_date) as earliest_purchase,
                    MAX(purchase_date) as latest_purchase
            FROM inventory 
            GROUP BY manufacturer, model 
            ORDER BY manufacturer, model";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getAllCategories($pdo)
{
  $sql = "SELECT id, name FROM categories ORDER BY name ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Function to check warranty status
function getWarrantyStatus($purchase_date, $warranty_years)
{
  $purchase = new DateTime($purchase_date);
  $warranty_end = clone $purchase;
  $warranty_end->add(new DateInterval("P{$warranty_years}Y"));
  $now = new DateTime();

  if ($now > $warranty_end) {
    return ['status' => 'expired', 'end_date' => $warranty_end->format('Y-m-d')];
  } elseif ($now->diff($warranty_end)->days <= 90) {
    return ['status' => 'expiring_soon', 'end_date' => $warranty_end->format('Y-m-d')];
  } else {
    return ['status' => 'active', 'end_date' => $warranty_end->format('Y-m-d')];
  }
}

// Fetch database
$inventories = getAllInventory($pdo);
$categories = getAllCategories($pdo);
// dd($inventories);
require("views/add-inventory.views.php");
