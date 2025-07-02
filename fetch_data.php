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
