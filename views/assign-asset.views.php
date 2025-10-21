<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5  bg-red-800">
      <!-- <div class="col-span-10 "> -->
      <?php require("views/banner.php") ?>
    </article>
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

    <div class="col-span-10 text-sm font-light flex justify-end gap-2 px-4 sticky top-0 bg-white shadow-lg py-2 z-10">
      <button class="text-red-700 border px-2 py-1 text-sm rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-modal">
        Assign Asset
      </button>

      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-unassign-modal" type="button">Unassigned Asset</button>
    </div>

    <!-- Asset Assignment Form -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="modal">
      <div class="w-full max-w-2xl bg-white col-span-4 mx-auto rounded-sm p-4 shadow-lg" id="inventory-form">
        <!-- Dynamic Title -->
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-cubes text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="form-title">Assign Asset</h2>
        </div>
        <form id="assignAssetForm" method="POST" action="/manage-hardware/assign-asset" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-2 form text-sm font-medium">
          <!-- In your modal form -->
          <div id="selectedAssetsContainer"></div>

          <div class="col-span-10 gap-4">
            <select name="employee_id" id="employeeSelect" class="border rounded px-3 py-2 text-sm ">
              <option value="">Select Employee</option>
              <?php
              $employees = fetchEmployeeActive($pdo, 'Active');
              foreach ($employees as $employee): ?>
                <option value="<?= htmlspecialchars($employee['id']) ?>">
                  <?= htmlspecialchars($employee['employee_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-span-10 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-btn">
              Cancel
            </button>
            <button type="submit" name="assign_assets" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 add-inventory-btn cursor-pointer" id="assign-btn">
              Assign
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Asset Unassignment Modal -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="unassign-modal">
      <div class="w-full max-w-2xl bg-white col-span-4 mx-auto rounded-sm p-4 shadow-lg">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-user-minus text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl">Unassign Asset</h2>
        </div>
        <form id="unassignAssetForm" method="POST" action="/manage-hardware/assign-asset" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-2 form text-sm font-medium">
          <div id="selectedUnassignAssetsContainer"></div>

          <div class="col-span-10">
            <p class="text-gray-600 mb-4">Are you sure you want to unassign the selected asset(s)? This will remove the current employee assignment and set the status to 'Available'.</p>
          </div>

          <div class="col-span-10 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-unassign-btn">
              Cancel
            </button>
            <button type="submit" name="unassign_assets" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer" id="unassign-btn">
              Unassign
            </button>
          </div>
        </form>
      </div>
    </div>

    <article class="col-span-10 text-sm font-light px-2 pt-3" id="asset-table">
      <div>
        <div>
          <table id="assetTable" class="display text-left">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" id="headerCheckbox" class="select-all-checkbox">
                </th>
                <th>No.</th>
                <th>Image</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <th>Category</th>
                <th>Asset No.</th>
                <th>Serial No.</th>
                <th>Status</th>
                <th>Conditions</th>
                <th>Assigned To</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php
              $itemCounter = 1;
              foreach ($assetsByInventory as $inventoryAssets): ?>
                <?php foreach ($inventoryAssets as $asset): ?>

                  <tr class="text-xs font-light text-left">
                    <td>
                      <input type="checkbox" name="selected_assets[]" value="<?= htmlspecialchars($asset['asset_id']) ?>" class="asset-checkbox" data-status="<?= htmlspecialchars($asset['status']) ?>">
                    </td>
                    <td><?= $itemCounter++ ?> </td>
                    <td class="w-1/12 h-1/12 object-contain">
                      <img src="/<?= htmlspecialchars($asset['photo'] ?? 'uploads/default-photo/laptop-charger.jpg') ?>" alt="" class="w-8/12">
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
                      'Surrender' => 'text-orange-500 bg-orange-100',
                      'Under Maintenance' => 'text-gray-500 bg-gray-100',
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
                    <td> <?= htmlspecialchars($asset['assigned_employee_name'] ?? '-') ?> </td>
                    <td class="">
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>">
                        <i class="fa-solid fa-eye"></i>
                      </a>
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



<script src="/assets/js/assign-asset.js"> </script>
<?php require("views/partials/footer.php") ?>