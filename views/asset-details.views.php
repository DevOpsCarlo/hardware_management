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
              'Employee Assigned' => 'text-blue-700 bg-blue-100',
              'Branch Assigned' => 'text-blue-600 bg-blue-100',
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
            <div class="space-y-6">
              <!-- Asset Image -->
              <div class="text-center w-full bg-gray-50 rounded-lg p-4">
                <img src="/<?= htmlspecialchars($assetDetails['photo'] ?? 'uploads/default-photo/laptop-charger.jpg') ?>"
                  alt="Asset Image"
                  class="mx-auto object-contain h-64 w-auto">
              </div>

              <!-- Basic Information -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-bold text-slate-700 mb-3">Asset Information</h3>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Asset no.</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['asset_number']) ?></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Serial Number</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['serial_number'] ?? '-') ?></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Category</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['category_name'] ?? '-') ?></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Manufacturer</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['manufacturer']) ?></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Model</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['model']) ?></span>
                  </div>
                  <?php if (!empty($assetDetails['ip_address'])): ?>
                    <div class="flex justify-between">
                      <span class="text-slate-500 font-bold">IP Address</span>
                      <span class="text-slate-800"><?= htmlspecialchars($assetDetails['ip_address']) ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Condition</span>
                    <span class="text-slate-800"><?= htmlspecialchars($assetDetails['conditions'] ?? '-') ?></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Purchase Date</span>
                    <span class="text-slate-800"><?= $assetDetails['purchase_date'] ? date('M d, Y', strtotime($assetDetails['purchase_date'])) : '-' ?></span>
                  </div>
                  <?php if (!empty($assetDetails['warranty_years'])): ?>
                    <div class="flex justify-between">
                      <span class="text-slate-500 font-bold">Warranty</span>
                      <span class="text-slate-800"><?= htmlspecialchars($assetDetails['warranty_years']) ?> year(s)</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Right Column - Current Assignment -->
            <div class="space-y-6">
              <!-- Current Assignment -->
              <?php if ($assetDetails['status'] === 'Employee Assigned' && $assetDetails['assigned_employee_name']): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h3 class="text-lg font-semibold text-blue-900 mb-3">Currently Assigned To Employee</h3>
                  <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                      <span class="text-slate-500 font-bold">Employee Name</span>
                      <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_name']) ?></span>
                    </div>

                    <!-- if (!empty($assetDetails['assigned_employee_department'])): ?>
                      <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Department</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_department']) ?></span>
                      </div>
                    endif; ?> -->

                    <?php if (!empty($assetDetails['assigned_employee_branch'])): ?>
                      <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Branch</span>
                        <span class="text-blue-800 font-semibold"><?= htmlspecialchars($assetDetails['assigned_employee_branch']) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php elseif ($assetDetails['status'] === 'Branch Assigned'): ?>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                  <h3 class="text-lg font-semibold text-indigo-900 mb-3">
                    Currently Assigned To <span class="text-red-500"> <?= htmlspecialchars($assetDetails['asset_branch_name']) ?></span> Branch
                  </h3>


                  <p class="text-indigo-700 mt-2">
                    Waiting for employee assignment.
                  </p>
                </div>
              <?php else: ?>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                  <h3 class="text-lg font-semibold text-slate-700 mb-2">Not Currently Assigned</h3>
                  <p class="text-slate-600">This asset is available for assignment</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignment History - UNIFIED TABLE -->
      <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-900">Assignment History</h2>
          <p class="text-sm text-gray-600">Branch and Employee assignments timeline</p>
        </div>

        <?php if (empty($assignmentHistory)): ?>
          <div class="px-6 py-12 text-center text-gray-500">
            <i class="fa-solid fa-history text-5xl mb-4 opacity-30"></i>
            <p class="text-lg font-medium">No assignment history</p>
            <p class="text-sm">This asset has not been assigned yet</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-100 border-b-2 border-gray-300">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">No.</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Assigned To</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Assigned Date</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Unassigned Date</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Duration</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                  <th class="px-4 py-3 text-left font-semibold text-gray-700">Agreement Form</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($assignmentHistory as $index => $history): ?>
                  <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                    <!-- No. -->
                    <td class="px-4 py-3 text-gray-900 font-medium">
                      <?= $index + 1 ?>
                    </td>
                    <!-- Type Badge -->
                    <td class="px-4 py-3">
                      <?php if ($history['type'] === 'BRANCH'): ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                          <i class="fa-solid fa-building mr-1"></i> Branch
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                          <i class="fa-solid fa-user mr-1"></i> Employee
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Assigned To -->
                    <td class="px-4 py-3 font-medium text-gray-900">
                      <?= htmlspecialchars($history['assigned_to']) ?>
                    </td>

                    <!-- Assigned Date -->
                    <td class="px-4 py-3 text-gray-900">
                      <?= date('M d, Y H:i', strtotime($history['assigned_date'])) ?>
                    </td>

                    <!-- Unassigned Date -->
                    <td class="px-4 py-3 text-gray-900">
                      <?= $history['unassigned_date'] ? date('M d, Y H:i', strtotime($history['unassigned_date'])) : '-' ?>
                    </td>

                    <!-- Duration -->
                    <td class="px-4 py-3 text-gray-900 font-medium">
                      <?= $history['days_assigned'] ?> day<?= $history['days_assigned'] != 1 ? 's' : '' ?>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-3">
                      <?php if ($history['unassigned_date'] === null): ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                          <i class="fa-solid fa-check-circle mr-1"></i>Active
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                          <i class="fa-solid fa-times-circle mr-1"></i>Completed
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Uploaded file -->
                    <td class="px-4 py-3">
                      <?php if ($history['type'] === 'EMPLOYEE' && !empty($history['file_path'])): ?>
                        <a href="/<?= htmlspecialchars($history['file_path']) ?>"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded hover:bg-amber-200 transition">
                          <i class="fa-solid fa-file-pdf mr-1"></i>
                          View Agreement
                        </a>
                      <?php else: ?>
                        <span class="text-gray-400 text-xs">No file</span>
                      <?php endif; ?>
                    </td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<!-- PDF Viewer Modal (Optional - for inline viewing) -->
<div id="pdfViewerModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-xl w-11/12 h-5/6 flex flex-col">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-bold text-gray-900">Agreement Form</h3>
      <button id="closePdfModal" class="text-gray-500 hover:text-gray-700 text-2xl">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <iframe id="pdfFrame" src="" class="flex-1 border-0"></iframe>
  </div>
</div>

<script>
  const pdfLinks = document.querySelectorAll('a[href$=".pdf"]');
  const pdfViewerModal = document.getElementById('pdfViewerModal');
  const pdfFrame = document.getElementById('pdfFrame');
  const closePdfModal = document.getElementById('closePdfModal');

  pdfLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      pdfFrame.src = link.href;
      pdfViewerModal.classList.remove('hidden');
    });
  });

  closePdfModal.addEventListener('click', () => {
    pdfViewerModal.classList.add('hidden');
    pdfFrame.src = '';
  });

  pdfViewerModal.addEventListener('click', (e) => {
    if (e.target === pdfViewerModal) {
      pdfViewerModal.classList.add('hidden');
      pdfFrame.src = '';
    }
  });
</script>
<?php require("views/partials/footer.php") ?>