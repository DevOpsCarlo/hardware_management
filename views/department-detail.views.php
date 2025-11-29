<?php require("views/partials/head.php") ?>

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
        <?= htmlspecialchars($department['department_name']) ?> Department
      </h2>
      <!-- Branch Info Card -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-800 mt-1 font-bold">
            <span class="font-medium text-slate-600">Department Head:</span>
            <?= htmlspecialchars($department['department_head'] ?? 'Not assigned') ?>
          </p>
          <p class="text-sm text-slate-800">
            <span class="font-medium text-slate-600">Total Assets:</span>
            <?= count($department) ?>
          </p>
        </div>
      </div>

    </div>


    <!-- Inventory table -->
    <article class="col-span-10 text-sm mt-4 font-light" id="department-employee-table">

      <?php if (isset($deparmentStats) && $department): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

          <!-- Assigned Assets Card -->
          <a href=""
            class="block p-4 bg-blue-50 border border-blue-200 rounded-lg hover:shadow-md hover:bg-blue-100 transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-slate-600">Total Assets</p>
              <i class="fa-solid fa-user text-blue-500"></i>
            </div>
            <p class="text-3xl font-bold text-blue-700"><?= $department['total_assets'] ?? 0 ?></p>
            <p class="text-xs text-slate-500 mt-2">Total asset of <?= $department['department_name'] ?? '-' ?></p>
          </a>

          <!-- Assigned Assets Card -->
          <a href=""
            class="block p-4 bg-amber-50 border border-amber-200 rounded-lg hover:shadow-md hover:bg-amber-100 transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-slate-600">Assigned Assets</p>
              <i class="fa-solid fa-inbox text-amber-500"></i>
            </div>
            <p class="text-3xl font-bold text-amber-700"><?= ($department['assigned_count'] ?? 0) ?></p>
            <p class="text-xs text-slate-500 mt-2">Assigned at Department level</p>
          </a>

          <!-- Unassigned Repair Card -->
          <a href=""
            class="block p-4 bg-orange-50 border border-orange-200 rounded-lg hover:shadow-md hover:bg-orange-100 transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-slate-600">In Repair</p>
              <i class="fa-solid fa-wrench text-orange-500"></i>
            </div>
            <p class="text-3xl font-bold text-orange-700"><?= $department['unassigned_count'] ?? 0 ?></p>
            <p class="text-xs text-slate-500 mt-2">Under maintenance</p>
          </a>

          <!-- Defective Assets Card -->
          <a href="/branch/<?= urlencode($branch['branch_name']) ?>/assets?filter=defective"
            class="block p-4 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md hover:bg-gray-100 transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-slate-600">In Repair Assets</p>
              <i class="fa-solid fa-ban text-gray-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= $department['in_repair_count'] ?? 0 ?></p>
            <p class="text-xs text-slate-500 mt-2">In Repair assets</p>
          </a>

        </div>
      <?php endif; ?>
      <div>
        <div>

          <table id="departmentEmployeeTable" class="display">
            <thead>
              <tr>
                <th>No</th>
                <th>Image</th>
                <th>Assigned To</th>
                <th>Asset No.</th>
                <th>Equipment</th>
                <th>Model</th>
                <th>Serial No.</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($deparmentAssets)): ?>

                <?php foreach ($deparmentAssets as $index => $deparmentAsset): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><img src="/<?= !empty($deparmentAsset['photo']) ? htmlspecialchars($deparmentAsset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                        alt="No image available"
                        class="w-20 h-20 object-cover rounded"></td>
                    <td><?= htmlspecialchars($deparmentAsset['employee_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($deparmentAsset['asset_number']) ?></td>
                    <td><?= htmlspecialchars($deparmentAsset['category_name'] ?? 'N/A') ?></td>
                    <td>
                      <?= htmlspecialchars($deparmentAsset['manufacturer'] ?? ' ')  ?>
                      <?= htmlspecialchars($deparmentAsset['model'] ?? '-') ?>
                    </td>
                    <td><?= htmlspecialchars($deparmentAsset['serial_number'] ?? '-') ?></td>
                    <td>
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($deparmentAsset['asset_id']) ?>">
                        <i class="fa-solid fa-eye "></i>
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