<?php
$pageTitle = 'Add Asset';

$chargerCategory = fetchLaptopChargerId($pdo);

if ($chargerCategory) {
    $chargerCategoryId = $chargerCategory['id'];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'Delete Asset') {
    header('Content-Type: application/json');

    $assetId = $_POST['asset_id'] ?? null;

    error_log("Delete request received for asset ID: " . $assetId);

    if (!$assetId || !is_numeric($assetId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid asset ID for deletion.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // First, delete from asset_branch_assignment_history (child table)
        $stmt = $pdo->prepare("DELETE FROM asset_branch_assignment_history WHERE asset_id = ?");
        $stmt->execute([$assetId]);
        error_log("Deleted from asset_branch_assignment_history. Rows affected: " . $stmt->rowCount());

        // Then delete from asset_employee_assignment_history (if it exists and has foreign key)
        $stmt = $pdo->prepare("DELETE FROM assignment_history WHERE asset_id = ?");
        $stmt->execute([$assetId]);
        error_log("Deleted from asset_employee_assignment_history. Rows affected: " . $stmt->rowCount());

        // Finally, delete the asset itself
        $stmt = $pdo->prepare("DELETE FROM asset WHERE id = ?");
        $result = $stmt->execute([$assetId]);

        error_log("Delete asset executed. Rows affected: " . $stmt->rowCount());

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Asset deleted successfully.']);
            exit;
        } else {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Asset not found.']);
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Delete error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete asset: ' . $e->getMessage()]);
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

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            header('Location: /manage-hardware/add-asset');
            exit;
        }

        // Generate asset number if new asset
        $assetNumber = null;
        if (empty($assetId) || $assetId == 0) {
            $assetNumber = generateAssetNumber($categoryId, $categoryName);
        }

        // Insert or update main asset
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
