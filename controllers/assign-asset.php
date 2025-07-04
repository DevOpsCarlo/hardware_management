<?php
$pageTitle = 'Assign Asset';


$assets = fetchAssetsWithInventoryAndCategory($pdo);
$assetsByInventory = [];
foreach ($assets as $asset) {
  $invId = $asset['inventory_id'];
  if (!isset($assetsByInventory[$invId])) {
    $assetsByInventory[$invId] = [];
  }
  $assetsByInventory[$invId][] = $asset;
}
require("views/assign-asset.views.php");
