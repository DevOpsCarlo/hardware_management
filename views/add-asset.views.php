<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5 bg-red-800">
      <?php
      if (!empty($_SESSION['forms_errors'])):
        $inventoryErrorMessage = htmlspecialchars($_SESSION['forms_errors'], ENT_QUOTES, 'UTF-8');
      ?>
        <script>
          Swal.fire({
            icon: 'error',
            text: '<?= $inventoryErrorMessage ?>',
            showConfirmButton: true,
            position: 'center'
          });
        </script>
      <?php
        unset($_SESSION['forms_errors']);
      endif;
      ?>
      <?php
      // Handle success messages
      if (!empty($_SESSION['success_message'])):
        $inventorySuccessMessage = htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
      ?>
        <script>
          Swal.fire({
            icon: 'success',
            text: '<?= $inventorySuccessMessage ?>',
            showConfirmButton: true,
            position: 'center',
            timer: 3000,
            timerProgressBar: true
          });
        </script>
      <?php
        unset($_SESSION['success_message']);
      endif;
      ?>

      <?php require("views/banner.php") ?>

    </article>

    <!-- All Inventory List -->
    <?php
    $laptopChargerAsset = null;
    foreach ($inventories as $inventory) {
      if (strtolower($inventory['category_name']) === 'laptop charger') {
        $laptopChargerAsset = $inventory;
        break;
      }
    }
    ?>

    <?php
    $category = $_SESSION['inventory_form_data'] ?? [];
    $editMode = $_SESSION['inventory_edit_mode'] ?? false;
    $isEditMode = !empty($asset['asset_number']) || !empty($asset['model']);
    unset($_SESSION['category_form_data'], $_SESSION['category_edit_mode']);
    ?>
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

              // Step 1: Group assets
              $laptopAssetsByInventory = [];
              $chargerAssetsByLaptop = [];

              foreach ($assets as $asset) {
                if (preg_match('/C$/', $asset['asset_number']) || strtolower($asset['category_name']) === 'laptop charger') {
                  // It's a charger
                  $chargerAssetsByLaptop[$asset['related_laptop_id']] = $asset;
                  continue;
                }

                // Group valid (non-charger) assets under their inventory
                $invId = $asset['inventory_id'];
                if (!isset($laptopAssetsByInventory[$invId])) {
                  $laptopAssetsByInventory[$invId] = [];
                }
                $laptopAssetsByInventory[$invId][] = $asset;
              }
              foreach ($inventories as $inventory):
                if (strtolower($inventory['category_name']) === 'laptop charger') {
                  continue;
                }
                $invId = $inventory['inventory_id'];
                $assetsForInventory = $laptopAssetsByInventory[$invId] ?? [];
                $quantity = $inventory['quantity'];
                // Loop through the quantity (expected number of assets)
                for ($i = 0; $i < $quantity; $i++):
                  $asset = $assetsForInventory[$i] ?? null;
                  $relatedCharger = ($asset && isset($chargerAssetsByLaptop[$asset['asset_id']])) ? $chargerAssetsByLaptop[$asset['asset_id']] : null;
              ?>
                  <tr class="text-xs font-light text-left">
                    <td><?= $itemCounter++ ?></td>
                    <td class="w-1/12 h-1/12 object-contain">
                      <img src="/<?= htmlspecialchars($inventory['photo'] ?? 'Empty') ?>" alt="">
                    </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'] ?? 'Empty')) ?></td>
                    <td><?= htmlspecialchars(ucfirst($inventory['model'] ?? 'Empty')) ?></td>
                    <td><?= htmlspecialchars(ucfirst($inventory['category_name'] ?? 'Empty')) ?></td>
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
                              data-charger-id="<?= htmlspecialchars($relatedCharger['asset_id'] ?? 0) ?>"
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
                <?php endfor; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </article>




    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 <?= (!empty($_SESSION['form_errors'])) ? '' : 'hidden' ?>" id="add-asset-modal">
      <div class="w-full max-w-5xl bg-white col-span-4 mx-auto rounded-sm p-7 shadow-lg" id="category-modal-box">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-computer text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl modal-title">Hardware details</h2>
        </div>
        <form class="text-sm font-light col-span-10 grid gap-2" id="add-asset-form" method="POST" action="">
          <div class="hidden">
            <input type="text" name="item_number" id="modal-item-number">
            <input type="hidden" name="category_id" id="modal-category-id">
            <input type="text" name="inventory_id" id="modal-inventory-id">
            <input type="hidden" name="asset-id" value="<?= htmlspecialchars($asset['id'] ?? 0) ?>" id="asset-id">
            <input type="hidden" name="action" id="asset-action" value="Add Asset">
          </div>

          <figure class="border border-slate-200 p-4 grid gap-2">
            <div class="flex justify-between items-center">
              <h4 class="mb-2 text-slate-600 text-lg font-semibold" id="figure-title"></h4>
              <img src="/<?= htmlspecialchars($inventory['photo'] ?? '') ?>" alt="" class="w-20 h-20 object-contain" id="photo-preview">
            </div>

            <span id="has-error" class="text-sm text-pink-600"></span>
            <div class="flex items-center justify-center gap-1">
              <div class="w-full flex flex-col gap-1">
                <label class="text-xs font-bold" for="">Manufacturer</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" type="text" name="manufacturer" id="modal-manufacturer" readonly>
              </div>
              <div class="w-full flex flex-col gap-1">
                <label class="text-xs font-bold" for="">Equipment</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" name="category_display" id="modal-category-select" readonly>
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Asset No.</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" readonly name="asset-number" id="modal-asset-number" value="<?= htmlspecialchars($asset['asset_number'] ?? '') ?>">
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Model</label>
                <input type="text" name="input-model" class="w-full border rounded px-3 py-1 bg-slate-200" readonly id="input-model" value="<?= htmlspecialchars($asset['model'] ?? '') ?>">

              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Serial No.</label>
                <div class="flex w-full">
                  <input type="text" name="input-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter serial number" id="input-serial-number" value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
                  <?php if (!empty($_SESSION['form_errors']['serial'])): ?>
                    <p class="text-pink-600 text-xs w-full"><?= htmlspecialchars($_SESSION['form_errors']['serial']) ?></p>
                  <?php endif; ?>
                </div>

              </div>
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">IP Address</label>
                <div class="flex w-full">
                  <input type="text" name="input-ip-address" class="w-full border rounded px-3 py-1" placeholder="Enter IP address (optional)" id="input-ip-address" value="<?= htmlspecialchars($asset['ip_address'] ?? '') ?>">
                  <?php if (!empty($_SESSION['form_errors']['ip'])): ?>
                    <p class="text-pink-600 text-xs"><?= htmlspecialchars($_SESSION['form_errors']['ip']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Status</label>
                <?php
                $statusOptions = ['Available', 'Assigned', 'Under Maintenance', 'Defective'];
                $currentStatus = $asset['status'] ?? '';
                ?>
                <select class="w-full border rounded px-3 py-1" name="status" id="select-status">
                  <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $currentStatus === $status ? 'selected' : '' ?>>
                      <?= htmlspecialchars($status) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Condition</label>
                <?php
                $conditionOptions = ['Good', 'For Maintenance', 'For Repair', 'For Replacement', 'Lost'];
                $currentCondition = $asset['conditions'] ?? '';
                ?>
                <select name="conditions" class="w-full border rounded px-3 py-1" id="select-conditions">
                  <?php foreach ($conditionOptions as $condition): ?>
                    <option value="<?= htmlspecialchars($condition) ?>" <?= $currentCondition === $condition ? 'selected' : '' ?>>
                      <?= htmlspecialchars($condition) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

          </figure>

          <figure class="border border-slate-200 p-4 grid gap-1  <?= $laptopChargerAsset ? '' : 'hidden' ?>" id="laptop-charger-section">

            <input type="hidden" name="asset-type" id="asset-type" value="laptop charger">
            <input type="hidden" name="charger-id" value="<?= htmlspecialchars($relatedCharger['id'] ?? 0) ?>" id="charger-id">

            <h4 class="mb-2 text-slate-600 text-lg font-semibold">Laptop charger info</h4>
            <div class="flex items-center gap-2">
              <div class="flex flex-col gap-1 w-full">
                <label for="">Manufacturer</label>
                <input type="text" name="charger-model" class="w-full border rounded px-3 py-1" id="model-charger" placeholder="Enter charger model" readonly>
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label for="">Asset No.</label>
                <input type="text" name="charger-asset-number" class="w-full border rounded px-3 py-1" id="charger-asset-number" placeholder="Enter charger asset number" value="<?= htmlspecialchars($relatedCharger['asset_number'] ?? '') ?>">
                <?php if (!empty($_SESSION['form_errors']['charger_asset_number'])): ?>
                  <p class="text-pink-600 text-xs"><?= htmlspecialchars($_SESSION['form_errors']['charger_asset_number']) ?></p>
                <?php endif; ?>
              </div>
            </div>

            <div class="flex items-center justify-between gap-2 mt-2">
              <div class="flex flex-col gap-1 w-full">
                <label>Serial No.</label>
                <input type="text" name="charger-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter charger serial no." id="charger-serial-number">
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label for="">Condition</label>
                <select name="charger-condition" class="w-full border rounded px-3 py-1">
                  <option value="Good">Good</option>
                  <option value="For Maintenance">For Maintenance</option>
                  <option value="For Repair">For Repair</option>
                  <option value="For Replacement">For Replacement</option>
                  <option value="Lost">Lost</option>
                </select>
              </div>
            </div>
          </figure>

          <div class="flex items-center gap-4">
            <button
              class="border border-slate-300 text-sm w-1/2 px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
              id="asset-cancel-btn"
              type="button">
              Cancel
            </button>
            <button type="submit" class="bg-red-500 hover:bg-red-600 cursor-pointer w-1/2 font-bold text-sm text-white px-4 py-2 rounded" name="add-asset" id="form-add-asset">Add Asset</button>
          </div>
        </form>
      </div>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
  </section>
</main>




<script src="/assets/js/add-asset.js"></script>
<?php require("views/partials/footer.php"); ?>