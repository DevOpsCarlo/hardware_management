<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5  bg-red-800">
      <!-- <div class="col-span-10 "> -->
      <?php require("views/banner.php") ?>
    </article>

    <article class="col-span-10 text-sm font-light" id="detailed-list-table">

      <div>
        <h4 class="text-slate-700 font-semibold text-base">All inventory list</h4>
        <div>
          <table id="detailedTable" class="display text-left">
            <thead>
              <tr>
                <th>No.</th>
                <th>Image</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <th>Category</th>
                <th>Asset No.</th>
                <th>Serial No.</th>
                <th>Status</th>
                <th>Conditions</th>
                <!-- <th>Assigned To</th> -->
                <th>Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php
              $itemCounter = 1;
              foreach ($assetsByInventory as $inventoryAssets): ?>
                <?php foreach ($inventoryAssets as $asset): ?>
                  <?php
                  // Skip if category is Laptop Charger
                  // if (strtolower($asset['category_name']) === 'laptop charger') continue;
                  ?>
                  <tr class="text-xs font-light text-left">
                    <td><?= $itemCounter++ ?></td>
                    <td class="w-1/12 h-1/12 object-contain">
                      <img src="/<?= htmlspecialchars($asset['photo'] ?? 'Empty') ?>" alt="">
                    </td>
                    <td><?= htmlspecialchars(ucfirst($asset['manufacturer'] ?? 'Empty')) ?></td>
                    <td><?= htmlspecialchars(ucfirst($asset['model'] ?? 'Empty')) ?></td>
                    <td><?= htmlspecialchars(ucfirst($asset['category_name'] ?? 'Empty')) ?></td>
                    <td><?= htmlspecialchars($asset['asset_number'] ?? 'Empty') ?></td>
                    <td><?= htmlspecialchars($asset['serial_number'] ?? 'Empty') ?></td>
                    <?php
                    $statusClassMap = [
                      'Available' => 'text-emerald-500 bg-emerald-100',
                      'Assigned' => 'text-blue-500 bg-blue-100',
                      'Under Maintenance' => 'text-orange-500 bg-orange-100',
                      'Defective' => 'text-red-500 bg-red-100'
                    ];
                    $currentStatus = $asset['status'] ?? 'Empty';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>
                    <td>
                      <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?= $statusClass ?>">
                        <?= htmlspecialchars($currentStatus) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($asset['conditions'] ?? 'Empty') ?></td>
                    <!-- <td> htmlspecialchars($asset['assigned_to'] ?? 'Empty') </td> -->
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-10 right-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light">
                          <!-- Add Asset -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left add-asset-btn"
                              data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>"
                              data-item-number="<?= $itemCounter - 1 ?>"
                              data-manufacturer="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-category-id="<?= htmlspecialchars($inventory['category_id']) ?>"
                              data-category-option="<?= htmlspecialchars($inventory['category_name']) ?>"
                              data-asset-number="<?= htmlspecialchars($asset['asset_number'] ?? '') ?>"
                              data-model="<?= htmlspecialchars($inventory['model'] ?? '') ?>"
                              data-serial-number="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>"
                              data-ip-address="<?= htmlspecialchars($asset['ip_address'] ?? '') ?>"
                              data-status="<?= htmlspecialchars($asset['status'] ?? '') ?>"
                              data-conditions="<?= htmlspecialchars($asset['conditions'] ?? '') ?>"
                              data-photo="<?= htmlspecialchars($inventory['photo'] ?? '') ?>"
                              data-asset-id="<?= htmlspecialchars($asset['asset_id'] ?? 0) ?>"
                              <?php if ($relatedCharger): ?>
                              data-charger-id="<?= htmlspecialchars($relatedCharger['id'] ?? 0) ?>"
                              data-charger-asset-number="<?= htmlspecialchars($relatedCharger['asset_number']) ?>"
                              data-charger-model="<?= htmlspecialchars($relatedCharger['model']) ?>"
                              data-charger-serial-number="<?= htmlspecialchars($relatedCharger['serial_number'] ?? '') ?>"
                              data-charger-conditions="<?= htmlspecialchars($relatedCharger['conditions']) ?>"
                              data-charger-status="<?= htmlspecialchars($relatedCharger['status']) ?>"
                              <?php endif; ?>>
                              Add
                            </button>
                          </li>
                          <!-- Delete -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer w-full text-left delete-asset-btn"
                              data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>"
                              data-item-number="<?= $itemCounter - 1 ?>"
                              data-manufacturer="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-category-id="<?= htmlspecialchars($inventory['category_id']) ?>"
                              data-category-option="<?= htmlspecialchars($inventory['category_name']) ?>"
                              data-asset-number="<?= htmlspecialchars($asset['asset_number'] ?? '') ?>"
                              data-model="<?= htmlspecialchars($inventory['model'] ?? '') ?>"
                              data-serial-number="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>"
                              data-ip-address="<?= htmlspecialchars($asset['ip_address'] ?? '') ?>"
                              data-status="<?= htmlspecialchars($asset['status'] ?? '') ?>"
                              data-conditions="<?= htmlspecialchars($asset['conditions'] ?? '') ?>"
                              data-photo="<?= htmlspecialchars($inventory['photo'] ?? '') ?>"
                              data-asset-id="<?= htmlspecialchars($asset['asset_id'] ?? 0) ?>"
                              data-item-number="<?= $itemCounter - 1 ?>"
                              <?php if ($relatedCharger): ?>
                              data-charger-id="<?= htmlspecialchars($relatedCharger['id'] ?? 0) ?>"
                              data-charger-asset-number="<?= htmlspecialchars($relatedCharger['asset_number']) ?>"
                              data-charger-model="<?= htmlspecialchars($relatedCharger['model']) ?>"
                              data-charger-serial-number="<?= htmlspecialchars($relatedCharger['serial_number'] ?? '') ?>"
                              data-charger-conditions="<?= htmlspecialchars($relatedCharger['conditions']) ?>"
                              data-charger-status="<?= htmlspecialchars($relatedCharger['status']) ?>"
                              <?php endif; ?>>

                              Delete
                            </button>
                          </li>
                          <li class="px-4 py-2 hover:bg-slate-100">
                            <a href="/manage-hardware/add-asset/view-asset" class="cursor-pointer w-full text-left">View</a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </article>

  </section>
</main>




<?php require("views/partials/footer.php") ?>