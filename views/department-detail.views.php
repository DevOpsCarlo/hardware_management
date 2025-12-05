<?php require("views/partials/head.php") ?>
<style>
  .filter-btn {
    background-color: white;
    color: #374151;
    border-color: #e2e8f0;
  }

  .filter-btn.active {
    background-color: #fb2c36;
    ;
    color: white;
    border-color: #c10007;
  }

  .filter-btn:hover {
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  }
</style>
<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 ">
    <?php if (isset($_SESSION['success_message'])): ?>
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '<?= htmlspecialchars($_SESSION['success_message']) ?>',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          position: 'top-end',
          toast: true
        });
      </script>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: '<?= htmlspecialchars($_SESSION['error_message']) ?>',
          showConfirmButton: true,
          position: 'top-end',
          toast: true
        });
      </script>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>


    <!-- Header with Back Button -->
    <div class="flex items-center justify-between my-4 px-2">
      <div class="flex items-center gap-4">
        <a href="/branch/<?= htmlspecialchars($branch['branch_name']) ?>/department" class="inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          <i class="fa-solid fa-arrow-left"></i>
          <p>
            Back to Department
          </p>
        </a>
      </div>
      <!-- 
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-modal">
        + Add Employee
      </button> -->
    </div>
    <div class="my-4 shadow-sm py-2 px-4">
      <h2 class="text-2xl font-extrabold text-red-700">
        <?= htmlspecialchars_decode($department['department_name']) ?> Department
      </h2>
      <!-- Branch Info Card -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-800 mt-1 font-bold">
            <span class="font-medium text-slate-600">Department Head:</span>
            <?= htmlspecialchars($department['department_head'] ?? 'Not assigned') ?>
          </p>
        </div>
      </div>

    </div>


    <!-- Inventory table -->
    <article class="col-span-10 text-sm mt-4 font-light" id="department-employee-table">

      <?php if (isset($departmentStats) && $department): ?>
        <!-- Filter Buttons Section -->
        <div class="my-4 px-4 pb-4 ">
          <!-- <p class="text-sm font-semibold text-slate-700 mb-3">Filter by Status:</p> -->
          <div class="flex flex-wrap gap-2 justify-end">
            <!-- All Assets Button -->
            <button class="filter-btn px-4 py-2 rounded-md text-sm font-medium border transition-all active" data-filter="all">
              <i class="fa-solid fa-list mr-2"></i>
              All Assets (<?= $departmentStats['total_assets'] ?? 0 ?>)
            </button>

            <!-- Assigned to Employees -->
            <button class="filter-btn px-4 py-2 rounded-md text-sm font-medium border transition-all" data-filter="assigned">
              <i class="fa-solid fa-user-check mr-2"></i>
              Assigned (<?= $departmentStats['assigned_count'] ?? 0 ?>)
            </button>

            <!-- In Repair -->
            <button class="filter-btn px-4 py-2 rounded-md text-sm font-medium border transition-all" data-filter="repair">
              <i class="fa-solid fa-wrench mr-2"></i>
              In Repair (<?= $departmentStats['in_repair_count'] ?? 0 ?>)
            </button>

            <!-- Uncommitted Assets -->
            <button class="filter-btn px-4 py-2 rounded-md text-sm font-medium border transition-all" data-filter="uncommitted">
              <i class="fa-solid fa-ban mr-2"></i>
              Uncommitted (<?= $departmentStats['uncommitted_count'] ?? 0 ?>)
            </button>

          </div>
        </div>
      <?php endif; ?>
      <div>
        <div>

          <!-- <table id="departmentEmployeeTable" class="row-border text-left w-full hover">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>

                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Asset</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Asset No.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Serial No.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Assigned To</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Condition</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($deparmentAssets)): ?>

                <?php foreach ($deparmentAssets as $index => $deparmentAsset): ?>
                  <tr class="text-xs ">
                    <td class="w-2/12">
                      <div class="flex items-center gap-x-2 ">
                        <img src="/<?= !empty($deparmentAsset['photo']) ? htmlspecialchars($deparmentAsset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                          alt="No image available"
                          class="w-2/12 rounded-full object-cover ">
                        <span>
                          <?= htmlspecialchars($deparmentAsset['manufacturer'] ?? '')  ?>
                          <?= htmlspecialchars($deparmentAsset['model'] ?? '—') ?>
                        </span>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['category_name'] ?? '—') ?></td>
                    <td>
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($deparmentAsset['asset_id']) ?>" class="text-blue-500 hover:text-blue-700 hover:underline hover:font-bold">
                        <?= htmlspecialchars($deparmentAsset['asset_number']) ?>
                      </a>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['serial_number'] ?? '—') ?></td>
                    <td class="font-bold italic"><?= htmlspecialchars(ucwords($deparmentAsset['employee_name'] ?? '—')) ?></td>
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
                    $currentStatus = $deparmentAsset['status'] ?? '—';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>
                    <td>
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?= $statusClass ?>">
                        <?= htmlspecialchars($currentStatus) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['conditions'] ?? '—') ?></td>
                    <td>
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($deparmentAsset['asset_id']) ?>">
                        <i class="fa-solid fa-eye "></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>

              <?php endif; ?>
            </tbody>
          </table> -->
          <table id="departmentEmployeeTable" class="row-border text-left w-full hover">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Asset</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Asset No.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Serial No.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Assigned To</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Condition</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($deparmentAssets)): ?>
                <?php foreach ($deparmentAssets as $index => $deparmentAsset): ?>
                  <tr class="text-xs asset-row" data-status="<?= htmlspecialchars($deparmentAsset['status'] ?? '—') ?>">
                    <td class="w-2/12">
                      <div class="flex items-center gap-x-2">
                        <img src="/<?= !empty($deparmentAsset['photo']) ? htmlspecialchars($deparmentAsset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                          alt="No image available"
                          class="w-2/12 rounded-full object-cover">
                        <span>
                          <?= htmlspecialchars($deparmentAsset['manufacturer'] ?? '') ?>
                          <?= htmlspecialchars($deparmentAsset['model'] ?? '—') ?>
                        </span>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['category_name'] ?? '—') ?></td>
                    <td>
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($deparmentAsset['asset_id']) ?>" class="text-blue-500 hover:text-blue-700 hover:underline hover:font-bold">
                        <?= htmlspecialchars($deparmentAsset['asset_number']) ?>
                      </a>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['serial_number'] ?? '—') ?></td>
                    <td class="font-bold italic"><?= htmlspecialchars(ucwords($deparmentAsset['employee_name'] ?? '—')) ?></td>
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
                    $currentStatus = $deparmentAsset['status'] ?? '—';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>
                    <td>
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?= $statusClass ?>">
                        <?= htmlspecialchars($currentStatus) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['conditions'] ?? '—') ?></td>
                    <td>
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($deparmentAsset['asset_id']) ?>">
                        <i class="fa-solid fa-eye"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </article>



  </section>
</main>


<script src="/assets/js/department-detail.js"></script>
<?php require("views/partials/footer.php") ?>