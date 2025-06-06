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
