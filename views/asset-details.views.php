<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5 bg-red-800">
      <?php require("views/banner.php") ?>
    </article>

    <!-- Back Button -->
    <div class="px-6 py-4">
      <a href="/manage-hardware/assign-asset" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        <i class="fa-solid fa-arrow-left mr-2"></i>
        Back to Asset List
      </a>
    </div>

    <!-- Asset Details Card -->
    <div class="px-6 pb-6">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Asset Details</h1>
            <?php
            $statusClassMap = [
              'Available' => 'text-emerald-600 bg-emerald-100',
              'Assigned' => 'text-blue-600 bg-blue-100',
              'Surrender' => 'text-orange-600 bg-orange-100',
              'Under Maintenance' => 'text-gray-600 bg-gray-100',
              'Defective' => 'text-red-600 bg-red-100'
            ];
            $currentStatus = $assetDetails['status'] ?? 'Unknown';
            $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-600 bg-gray-100';
            ?>
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold <?= $statusClass ?>">
              <?= htmlspecialchars($currentStatus) ?>
            </span>
          </div>
        </div>

        <!-- Asset Information -->
        <div class="px-6 py-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Asset Image and Basic Info -->
            <div class="space-y-6 rounded-lg shadow-md">
              <!-- Asset Image -->
              <div class="text-center w-full">
                <img src="/<?= htmlspecialchars($assetDetails['photo'] ?? 'uploads/default-photo/laptop-charger.jpg') ?>"
                  alt="Asset Image"
                  class="mx-auto object-contain w-1/2 h-1/2">
              </div>

              <!-- Basic Information -->
              <div class="bg-gray-50 p-4">
                <h3 class="text-lg font-bold text-slate-700 mb-3">Asset Information</h3>
                <div class="space-y-1">
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Asset no.</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['asset_number']) ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Serial Number</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['serial_number'] ?? '-') ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Category</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['category_name'] ?? 'Laptop Charger') ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Manufacturer</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['manufacturer']) ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Model</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['model']) ?></span>
                  </div>
                  <?php if (!empty($assetDetails['ip_address'])): ?>
                    <div class="flex justify-between text-sm">
                      <span class="text-slate-500 font-bold">IP Address</span>
                      <span class="text-slate-800"><?= htmlspecialchars($assetDetails['ip_address'] ?? '-') ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Condition</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['conditions'] ?? '-') ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Purchase Date</span>
                    <span class="text-slate-800"><?= $assetDetails['purchase_date'] ? date('M d, Y', strtotime($assetDetails['purchase_date'])) : '-' ?></span>
                  </div>
                  <?php if (!empty($assetDetails['warranty_years'])): ?>
                    <div class="flex justify-between text-sm">
                      <span class="text-slate-500 font-bold">Warranty</span>
                      <span class="text-slate-800"><?= htmlspecialchars($assetDetails['warranty_years']) ?> year(s)</span>
                    </div>
                  <?php endif; ?>
                  <!-- <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Created</span>
                    <span class="text-slate-800 font-semibold"><?= date('M d, Y', strtotime($assetDetails['asset_created_at'])) ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-bold">Last Updated</span>
                    <span class="text-slate-800 font-semibold"><?= date('M d, Y', strtotime($assetDetails['asset_updated_at'])) ?></span>
                  </div> -->
                </div>
              </div>
            </div>

            <!-- Right Column - Current Assignment and Details -->
            <div class="space-y-6">
              <!-- Current Assignment -->
              <?php if ($assetDetails['status'] === 'Assigned' && $assetDetails['assigned_employee_name']): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h3 class="text-lg font-semibold text-blue-900 mb-3">Currently Assigned To</h3>
                  <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                      <span class="text-slate-500 font-bold">Employee Name</span>
                      <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_name']) ?></span>
                    </div>
                    <?php if (!empty($assetDetails['assigned_employee_code'])): ?>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-bold">Employee ID</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_code']) ?></span>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($assetDetails['assigned_employee_email'])): ?>
                      <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Email</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_email']) ?></span>
                      </div>
                    <?php endif; ?>
                    <!-- if (!empty($assetDetails['assigned_employee_position'])):
                      <div class="flex justify-between">
                        <span class="text-blue-700 font-bold">Position</span>
                        <span class="text-blue-900 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_position']) ?></span>
                      </div>
                     endif; -->
                    <?php if (!empty($assetDetails['assigned_employee_department'])): ?>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-bold">Department</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_department']) ?></span>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($assetDetails['assigned_employee_branch'])): ?>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-bold">Branch</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_branch']) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php else: ?>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                  <h3 class="text-lg font-semibold text-slate-700 mb-2">Not Currently Assigned</h3>
                  <p class="text-slate-600">This asset is available for assignment</p>
                </div>
              <?php endif; ?>


              <!-- Assignment Statistics -->
              <!-- if ($assignmentStats && $assignmentStats['total_assignments'] > 0): ?> -->
              <!-- <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <h3 class="text-lg font-semibold text-green-900 mb-4">Assignment Statistics</h3>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-green-700">Total Assignments:</span>
                      <span class="font-medium text-green-900"><?= $assignmentStats['total_assignments'] ?></span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-green-700">Employees Assigned:</span>
                      <span class="font-medium text-green-900"><?= $assignmentStats['total_employees_assigned'] ?></span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-green-700">Average Days/Assignment:</span>
                      <span class="font-medium text-green-900"><?= round($assignmentStats['avg_assignment_days'], 1) ?> days</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-green-700">First Assignment:</span>
                      <span class="font-medium text-green-900"><?= date('M d, Y', strtotime($assignmentStats['first_assignment'])) ?></span>
                    </div>
                  </div>
                </div> -->
              <!-- endif; ?> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Assignment History -->
      <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-900">Assignment History</h2>
        </div>

        <?php if (empty($assignmentHistory)): ?>
          <div class="px-6 py-8 text-center text-gray-500">
            <i class="fa-solid fa-history text-4xl mb-4"></i>
            <p class="text-lg">No assignment history available</p>
            <p class="text-sm">This asset has never been assigned to anyone</p>
          </div>
        <?php else: ?>
          <table id="assetDetailsTable" class="display">
            <thead>
              <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Assigned Date</th>
                <th>Unassigned Date</th>
                <th>Duration</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($assignmentHistory as $index => $history): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td class="<?= $index === 0 && !$history['unassigned_date'] ? 'text-blue-900' : 'text-gray-900' ?>">
                    <?= htmlspecialchars($history['assigned_employee_name']) ?>
                    <?php if ($index === 0 && !$history['unassigned_date']): ?>
                      <span class="ml-2 inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Current</span>
                    <?php endif; ?>
                  </td>
                  <td> <?= htmlspecialchars($history['assigned_employee_branch'] ?? '-') ?> </td>
                  <td><?= date('M d, Y', strtotime($history['assigned_date'])) ?></td>

                  <td><?= $history['unassigned_date'] ? date('M d, Y', strtotime($history['unassigned_date'])) : '-' ?></td>

                  <td class="">
                    <?= $history['days_assigned'] ?> days
                  </td>
                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>

        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php require("views/partials/footer.php") ?>