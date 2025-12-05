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
      if (!empty($_SESSION['success_message'])):
        $inventorySuccessMessage = htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
      ?>
        <script>
          Swal.fire({
            icon: 'success',
            text: '<?= $inventorySuccessMessage ?>',
            showConfirmButton: false,
            position: 'top-end',
            timer: 1500,
            timerProgressBar: true,
            toast: true
          });
        </script>
      <?php
        unset($_SESSION['success_message']);
      endif;
      ?>

      <?php require("views/banner.php") ?>
    </article>

    <!-- All Inventory List -->
    <article class="col-span-10 text-sm font-light px-2" id="detailed-list-table">
      <div>
        <h2 class="text-2xl font-extrabold text-red-700 my-3">Asset List</h2>
        <div>
          <table id="detailedTable" class="row-border text-left w-full hover">
            <thead>
              <tr>
                <!-- <th>No.</th> -->
                <!-- <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Image</th> -->
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Category</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Serial No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Status</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Conditions</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php
              $itemCounter = 1;

              // Organize assets by inventory (including chargers since they have their own inventory_id)
              $assetsByInventory = [];

              foreach ($assets as $asset) {
                $invId = $asset['inventory_id'];
                if (!isset($assetsByInventory[$invId])) {
                  $assetsByInventory[$invId] = [];
                }
                $assetsByInventory[$invId][] = $asset;
              }

              // Display inventories with their assets
              foreach ($inventories as $inventory):
                $invId = $inventory['inventory_id'];
                $assetsForInventory = $assetsByInventory[$invId] ?? [];
                $quantity = $inventory['quantity'];

                // Loop through quantity - display actual assets first, then empty slots
                for ($i = 0; $i < $quantity; $i++):
                  $asset = isset($assetsForInventory[$i]) ? $assetsForInventory[$i] : null;
              ?>
                  <tr class="text-xs font-light text-left">
                    <td class="w-3/12 object-contain px-4 py-3 text-xs text-slate-900 font-normal">
                      <div class="flex items-center gap-x-2">
                        <?php
                        // Determine the appropriate photo based on category
                        $photoToDisplay = $inventory['photo'];

                        // If category is "Laptop Charger" and no photo, use default charger image
                        if ($inventory['category_name'] === 'Laptop Charger' && empty($inventory['photo'])) {
                          $photoToDisplay = 'uploads/default-photo/laptop-charger.jpg';
                        }
                        // If category is "Laptop" and no photo, use default laptop image (or adjust as needed)
                        elseif ($inventory['category_name'] === 'Laptop' && empty($inventory['photo'])) {
                          $photoToDisplay = 'uploads/default-photo/laptop.jpg'; // adjust path if needed
                        }
                        ?>
                        <img src="/<?= htmlspecialchars($photoToDisplay) ?>" alt="No image available" class="w-2/12 rounded-full object-contain">
                        <?= htmlspecialchars(ucfirst($inventory['manufacturer'] ?? '—')) ?> <?= htmlspecialchars(ucfirst($inventory['model'] ?? '—')) ?>
                        </span>
                      </div>

                    </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['category_name'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars($asset['asset_number'] ?? '—') ?></td>

                    <td><?= htmlspecialchars($asset['serial_number'] ?? '—') ?></td>
                    <?php
                    $statusClassMap = [
                      'Available' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                      'Branch Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Employee Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Surrender' => 'text-orange-700 bg-orange-50 border border-orange-200',
                      'Under Maintenance' => 'text-orange-700 bg-orange-50 border border-orange-200',
                      'Department Assigned' => 'text-amber-700 bg-amber-50 border border-amber-200',
                      'Uncommitted' => 'text-red-700 bg-red-50 border border-red-200'
                    ];
                    $currentStatus = $asset['status'] ?? '—';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>
                    <td>
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?= $statusClass ?>">
                        <?= htmlspecialchars($currentStatus) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($asset['conditions'] ?? '—') ?></td>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-10 right-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light">
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
                              data-asset-id="<?= htmlspecialchars($asset['asset_id'] ?? 0) ?>">
                              Add
                            </button>
                          </li>
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
                              data-asset-id="<?= htmlspecialchars($asset['asset_id'] ?? 0) ?>">
                              Delete
                            </button>
                          </li>
                          <!-- <li class="px-4 py-2 hover:bg-slate-100">
                            <a href="/manage-hardware/add-asset/view-asset" class="cursor-pointer w-full text-left">View</a>
                          </li> -->
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

    <!-- Asset Modal -->
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

              <img src="/<?= htmlspecialchars($inventory['photo'] ?? 'uploads/default-photo/laptop-charger.jpg') ?>" alt="" class="w-20 h-20 object-contain" id="photo-preview">
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
                <input class="w-full border rounded px-3 py-1 bg-slate-200" readonly name="asset-number" id="modal-asset-number">
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Model</label>
                <input type="text" name="input-model" class="w-full border rounded px-3 py-1 bg-slate-200" readonly id="input-model">
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Serial No.</label>
                <div class="flex w-full">
                  <input type="text" name="input-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter serial number" id="input-serial-number">
                  <?php if (!empty($_SESSION['form_errors']['serial'])): ?>
                    <p class="text-pink-600 text-xs w-full"><?= htmlspecialchars($_SESSION['form_errors']['serial']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">IP Address</label>
                <div class="flex w-full">
                  <input type="text" name="input-ip-address" class="w-full border rounded px-3 py-1" placeholder="Enter IP address (optional)" id="input-ip-address">
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
                $statusOptions = ['Available', 'Assigned', 'Employee Assigned', 'Branch Assigned', 'Surrender', 'Under Maintenance', 'Uncommitted'];
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
                $conditionOptions = ['Good', 'For Maintenance', 'For Repair', 'For Replacement', 'Lost', 'Defective'];
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