<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5  bg-red-800">
      <?php require("views/banner.php") ?>
    </article>

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

    <!-- Action Buttons -->
    <div class="col-span-10 text-sm font-light flex justify-end gap-2 px-4 sticky top-0 bg-white shadow-lg py-2 z-10">
      <button class="text-red-700 border px-2 py-1 text-sm rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-branch-modal">
        Assign to Branch
      </button>
      <button class="text-red-700 border px-2 py-1 text-sm rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-employee-modal">
        Assign to Employee
      </button>
      <button class="text-orange-700 border px-2 py-1 text-sm rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-return-employee-modal">
        Return to Branch
      </button>
      <button class="text-yellow-700 border px-2 py-1 text-sm rounded-sm mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-return-branch-modal">
        Return to Pool
      </button>
    </div>

    <!-- MODAL 1: Assign to Branch -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="branch-modal">
      <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <i class="fa-solid fa-sitemap text-red-500 rounded-full bg-red-50 p-4"></i>
          <h2 class="text-slate-800 font-bold text-2xl">Assign Asset to Branch</h2>
        </div>
        <form id="assignBranchForm" method="POST" action="/manage-hardware/assign-asset" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
          <input type="hidden" name="assign_to_branch" value="1">
          <div id="selectedBranchAssetsContainer"></div>

          <div class="col-span-2">
            <label class="block mb-2">Select Branch</label>
            <select name="branch_id" id="branchSelect" class="w-full border rounded px-3 py-2 text-sm">
              <option value="">-- Select Branch --</option>
              <?php foreach ($branches as $branch): ?>
                <option value="<?= htmlspecialchars($branch['id']) ?>">
                  <?= htmlspecialchars($branch['branch_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-span-2 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-branch-btn">
              Cancel
            </button>
            <button type="submit" name="assign_to_branch" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer" id="branch-assign-btn">
              Assign to Branch
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: Assign to Employee (with file upload) -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="employee-modal">
      <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg max-h-96 overflow-y-auto">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <i class="fa-solid fa-user-tie text-blue-500 rounded-full bg-blue-50 p-4"></i>
          <h2 class="text-slate-800 font-bold text-2xl">Assign Asset to Employee</h2>
        </div>
        <form id="assignEmployeeForm" method="POST" action="/manage-hardware/assign-asset" enctype="multipart/form-data" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
          <input type="hidden" name="assign_to_employee" value="1" id="assignToEmployee">
          <div id="selectedEmployeeAssetsContainer"></div>

          <div class="col-span-2">
            <label class="block mb-2">Branch</label>
            <input type="text" id="employeeModalBranch" class="w-full border rounded px-3 py-2 text-sm bg-slate-100" disabled readonly>
            <input type="hidden" name="asset_branch_id" id="assetBranchId">
          </div>

          <div class="col-span-2">
            <label class="block mb-2">Select Employee</label>
            <select name="employee_id" id="employeeSelect" class="w-full border rounded px-3 py-2 text-sm">
              <option value="">-- Select Employee --</option>
              <?php
              $employees = fetchEmployeeActive($pdo, 'Active');
              foreach ($employees as $employee): ?>
                <option value="<?= htmlspecialchars($employee['id']) ?>" data-branch="<?= htmlspecialchars($employee['branch_id'] ?? '') ?>">
                  <?= htmlspecialchars($employee['employee_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- File Upload Section -->
          <div class="col-span-2">
            <label class="block mb-2 font-semibold text-slate-700">Upload Agreement File (Optional)</label>
            <div class="border-2 border-dashed border-slate-300 rounded-lg p-4 bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer" id="dropZone">
              <input type="file" name="agreement_file" id="agreement_file" accept=".pdf,.doc,.docx" class="hidden">
              <div class="text-center">
                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-2"></i>
                <p class="text-slate-600 text-sm">
                  <span id="fileName">Drag and drop your PDF or DOC file here, or click to select</span>
                </p>
                <p class="text-xs text-slate-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max 10MB)</p>
              </div>
            </div>
            <div id="fileInfo" class="mt-2 hidden">
              <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded px-3 py-2">
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-file text-emerald-600"></i>
                  <span id="uploadedFileName" class="text-sm text-emerald-700"></span>
                </div>
                <button type="button" id="removeFile" class="text-emerald-600 hover:text-emerald-800">
                  <i class="fa-solid fa-times"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="col-span-2 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-employee-btn">
              Cancel
            </button>
            <button type="submit" name="assign_to_employee" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer" id="employee-assign-btn">
              Assign to Employee
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: Return to Branch (from Employee) -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="return-employee-modal">
      <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <i class="fa-solid fa-arrow-rotate-left text-orange-500 rounded-full bg-orange-50 p-4"></i>
          <h2 class="text-slate-800 font-bold text-2xl">Return to Branch</h2>
        </div>
        <form id="returnEmployeeForm" method="POST" action="/manage-hardware/assign-asset" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
          <input type="hidden" name="unassign_from_employee" value="1">
          <div id="selectedReturnEmployeeAssetsContainer"></div>

          <div class="col-span-2">
            <p class="text-gray-600">This will return the asset(s) to the branch pool, making them available for reassignment.</p>
          </div>

          <div class="col-span-2 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-return-employee-btn">
              Cancel
            </button>
            <button type="submit" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer">
              Return to Branch
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 4: Return to Pool (from Branch) -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="return-branch-modal">
      <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <i class="fa-solid fa-undo text-yellow-500 rounded-full bg-yellow-50 p-4"></i>
          <h2 class="text-slate-800 font-bold text-2xl">Return to Pool</h2>
        </div>
        <form id="returnBranchForm" method="POST" action="/manage-hardware/assign-asset" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
          <input type="hidden" name="unassign_from_branch" value="1">
          <div id="selectedReturnBranchAssetsContainer"></div>

          <div class="col-span-2">
            <p class="text-gray-600">This will return the asset(s) to the general pool, removing all assignments.</p>
          </div>

          <div class="col-span-2 flex gap-4 pt-4">
            <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-return-branch-btn">
              Cancel
            </button>
            <button type="submit" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer">
              Return to Pool
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Asset Table -->
    <article class="col-span-10 text-sm font-light px-2 pt-3" id="asset-table">
      <table id="assetTable" class="display text-left">
        <thead>
          <tr>
            <th><input type="checkbox" id="headerCheckbox" class="select-all-checkbox"></th>
            <th>No.</th>
            <th>Image</th>
            <th>Model</th>
            <th>Category</th>
            <th>Asset No.</th>
            <th>Serial No.</th>
            <th>Status</th>
            <th>Assigned At</th>
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
                <td><?= $itemCounter++ ?></td>
                <td class="w-1/12 h-1/12 object-contain">
                  <img src="/<?= htmlspecialchars($asset['photo'] ?? 'uploads/default-photo/laptop-charger.jpg') ?>" alt="" class="w-8/12">
                </td>
                <td><?= htmlspecialchars(ucfirst($asset['manufacturer'] ?? 'N/A')) ?> / <?= htmlspecialchars(ucfirst($asset['model'] ?? 'N/A')) ?></td>
                <td><?= htmlspecialchars(ucfirst($asset['category_name'] ?? 'Empty')) ?></td>
                <td><?= htmlspecialchars($asset['asset_number'] ?? 'Empty') ?></td>
                <td><?= htmlspecialchars($asset['serial_number'] ?? 'Empty') ?></td>
                <?php
                $statusClassMap = [
                  'Available' => 'text-emerald-500 bg-emerald-100',
                  'Branch Assigned' => 'text-blue-500 bg-blue-100',
                  'Employee Assigned' => 'text-blue-500 bg-blue-100',
                  'Department Assigned' => 'text-orange-500 bg-orange-100',
                  'Surrender' => 'text-orange-500 bg-orange-100',
                  'Under Maintenance' => 'text-gray-500 bg-gray-100',
                  'Defective' => 'text-red-500 bg-red-100'
                ];
                $currentStatus = $asset['status'] ?? '-';
                $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                ?>
                <td>
                  <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?= $statusClass ?>">
                    <?= htmlspecialchars($currentStatus) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($asset['branch_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($asset['assigned_employee_name'] ?? '-') ?></td>
                <td>
                  <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </article>

  </section>
</main>

<script src="/assets/js/assign-asset.js"></script>
<?php require("views/partials/footer.php") ?>