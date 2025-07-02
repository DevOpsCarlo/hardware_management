<?php
$pageTitle = 'Add Asset';

$chargerCategory = fetchLaptopChargerId($pdo);

if ($chargerCategory) {
    $chargerCategoryId = $chargerCategory['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'Delete Asset') {
    $assetId = $_POST['asset_id'] ?? 0;

    if (!$assetId || !is_numeric($assetId)) {
        $_SESSION['form_errors'] = ['general' => 'Invalid asset ID for deletion.'];
        header('Location: /manage-hardware/add-asset');
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM asset WHERE id = ?");
        $stmt->execute([$assetId]);

        $_SESSION['success_message'] = 'Asset deleted successfully.';
        header('Location: /manage-hardware/add-asset');
        exit;
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        $_SESSION['form_errors'] = ['general' => 'Failed to delete asset.'];
        header('Location: /manage-hardware/add-asset');
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Get form data
        $inventoryId = $_POST['inventory_id'];
        $assetId = $_POST['asset-id'] ?? 0;
        $serialNumber = trim($_POST['input-serial-number']);
        $ipAddress = trim($_POST['input-ip-address']);
        $status = $_POST['status'];
        $condition = $_POST['conditions'];
        $categoryName = $_POST['category_display'];
        $categoryId = $_POST['category_id'] ?? null;
        $action = $_POST['action'] ?? 'Add Asset';

        // Validation
        $errors = [];

        if (empty($serialNumber)) {
            $errors['serial'] = 'Serial number is required';
        } else {
            // Check if serial number already exists for different asset
            $stmt = $pdo->prepare("SELECT id FROM asset WHERE serial_number = ? AND id != ?");
            $stmt->execute([$serialNumber, $assetId]);
            if ($stmt->fetch()) {
                $errors['serial'] = 'Serial number already exists';
            }
        }

        if (!empty($ipAddress) && !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $errors['ip'] = 'Invalid IP address format';
        }


        // Generate asset number if new asset
        $assetNumber = null;
        if (empty($assetId) || $assetId == 0) {
            $assetNumber = generateAssetNumber($categoryId, $categoryName);
        }

        // Insert or update main laptop asset
        if ($action === 'Add Asset' && (empty($assetId) || $assetId == 0)) {
            $stmt = $pdo->prepare("
                INSERT INTO asset (inventory_id, asset_number, serial_number, ip_address, conditions, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $inventoryId,
                $assetNumber,
                $serialNumber,
                $ipAddress ?: null,
                $condition,
                $status
            ]);
            $mainAssetId = $pdo->lastInsertId();
        } else if ($action === 'Update Asset' && !empty($assetId)) {
            $stmt = $pdo->prepare("
                UPDATE asset SET serial_number = ?, ip_address = ?, conditions = ?, status = ?, updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([
                $serialNumber,
                $ipAddress ?: null,
                $condition,
                $status,
                $assetId
            ]);
            $mainAssetId = $assetId;
        } else {
            throw new Exception("Invalid asset action.");
        }

        // Handle charger if laptop
        if (strtolower($categoryName) === 'laptop') {
            $chargerAssetNumber = trim($_POST['charger-asset-number'] ?? '');
            $chargerSerialNumber = trim($_POST['charger-serial-number'] ?? '');
            $chargerCondition = $_POST['charger-condition'] ?? 'Good';
            $chargerManufacturer = trim($_POST['manufacturer'] ?? '');
            $chargerModel = trim($_POST['input-model'] ?? '');
            $chargerId = $_POST['charger-id'] ?? 0;



            if (!empty($chargerSerialNumber)) {
                // Check if charger inventory exists
                $stmt = $pdo->prepare("SELECT id, quantity FROM inventory WHERE manufacturer = ? AND model = ? AND category_id = ?");
                $stmt->execute([$chargerManufacturer, $chargerModel, $chargerCategoryId]);
                $chargerInventory = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($chargerInventory) {
                    // Increment charger inventory quantity by 1
                    $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity + 1, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$chargerInventory['id']]);
                    $chargerInventoryId = $chargerInventory['id'];
                } else {
                    // Insert new charger inventory record
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory (manufacturer, model, category_id, quantity, created_at, updated_at) 
                        VALUES (?, ?, ?, 1, NOW(), NOW())
                    ");
                    $stmt->execute([$chargerManufacturer, $chargerModel, $chargerCategoryId]);
                    $chargerInventoryId = $pdo->lastInsertId();
                }

                // Generate charger asset number if not provided
                if (empty($chargerAssetNumber)) {
                    $chargerAssetNumber = generateAssetNumber($chargerCategoryId, 'charger');
                }

                if (empty($chargerId) || $chargerId == 0) {
                    // Insert charger asset linked to charger inventory and laptop asset
                    $stmt = $pdo->prepare("
                        INSERT INTO asset (inventory_id, asset_number, serial_number, conditions, status, related_laptop_id, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $chargerInventoryId,
                        $chargerAssetNumber,
                        $chargerSerialNumber,
                        $chargerCondition,
                        $status,
                        $mainAssetId
                    ]);
                } else {
                    // Update existing charger asset
                    $stmt = $pdo->prepare("
                        UPDATE asset
                        SET asset_number = ?, serial_number = ?, conditions = ?, status = ?, related_laptop_id = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $chargerAssetNumber,
                        $chargerSerialNumber,
                        $chargerCondition,
                        $status,
                        $mainAssetId,
                        $chargerId
                    ]);
                }
            }
        }

        $pdo->commit();

        unset($_SESSION['form_errors']);
        $_SESSION['success_message'] = ($action === 'Update Asset')
            ? 'Asset updated successfully!'
            : 'Asset added successfully!';
        header('Location: /manage-hardware/add-asset');
        exit;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error: " . $e->getMessage());
        $_SESSION['form_errors'] = ['general' => 'Error assigning asset: ' . $e->getMessage()];
        header('Location: /manage-hardware/add-asset');
        exit;
    }
}

$assets = fetchAssetsWithInventoryAndCategory($pdo);
$assetsByInventory = [];
foreach ($assets as $asset) {
    $invId = $asset['inventory_id'];
    if (!isset($assetsByInventory[$invId])) {
        $assetsByInventory[$invId] = [];
    }
    $assetsByInventory[$invId][] = $asset;
}
$inventories = fetchInventoryWithCategory($pdo);
require("views/add-asset.views.php");
