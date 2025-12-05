<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 mx-2">
    <?php if (isset($_SESSION['success_message'])): ?>
      <script>
        Swal.fire({
          icon: 'success',
          text: '<?= $_SESSION['success_message']['text'] ?>',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
          position: 'top-end',
          toast: true
        });
      </script>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Header with Back Button -->
    <div class="flex items-center justify-between my-4">
      <div class="flex items-center gap-4">
        <a href="/branch" class="inline-flex items-center px-4 py-2 gap-x-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          <i class="fa-solid fa-arrow-left"></i>
          <p>
            Back to Branch
          </p>
        </a>
      </div>

      <div class="flex items-center gap-4">
        <a href="/branch/<?= $branchName ?>/department" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          Go to Department
        </a>
      </div>
    </div>




    <article class="col-span-10 text-sm font-light px-2 pb-5" id="assigned-assets">
      <div class="">
        <div class="flex justify-between items-center col-span-10 text-sm font-light gap-2 px-4 sticky top-0 shadow-sm py-2 z-10">
          <h1 class="text-2xl font-extrabold text-red-700 my-3"><?= $branchName ?> Branch</h1>

          <!-- Action Buttons -->
          <div class="">
            <button class="text-red-600 border px-3 py-2 text-xs font-bold rounded-xl mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-dept-modal">
              Assign to Department
            </button>
            <button class="text-red-600 border px-3 py-2 text-xs font-bold rounded-xl mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-employee-modal">
              Assign to Employee
            </button>
            <button class="text-amber-600 border px-3 py-2 text-xs font-bold rounded-xl mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-move-branch-modal">
              Move to Branch
            </button>
            <button class="text-orange-600 border px-3 py-2 text-xs font-bold rounded-xl mr-2 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="toggle-return-employee-modal">
              Return to Department
            </button>
          </div>
        </div>


        <!-- MODAL 1: Assign to Department -->
        <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="assign-dept-modal">
          <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
            <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
              <i class="fa-solid fa-sitemap text-blue-500 rounded-full bg-blue-50 p-4"></i>
              <h2 class="text-slate-800 font-bold text-2xl">Assign Assets to Department</h2>
            </div>
            <form id="assignDeptForm" method="POST" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
              <input type="hidden" name="assign_to_department" value="1">
              <div id="selectedDeptAssetsContainer"></div>

              <div class="col-span-2">
                <label class="block mb-2">Select Department</label>
                <select name="department_id" id="departmentSelect" class="w-full border rounded px-3 py-2 text-sm">
                  <option value="">-- Select Department --</option>
                  <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['id']) ?>">
                      <?= htmlspecialchars_decode($dept['department_name'] ?? '') ?>

                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-span-2" id="deptAssetsSummary">
                <p class="text-slate-600 text-sm">Selected: <span id="deptAssetsCount">0</span> asset(s)</p>
              </div>

              <div class="col-span-2 flex gap-4 pt-4">
                <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-dept-btn">
                  Cancel
                </button>
                <button type="submit" class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 cursor-pointer">
                  Assign to Department
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- MODAL 2: Assign to Employee (from Department) -->
        <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="assign-employee-dept-modal">
          <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg max-h-96 overflow-y-auto">
            <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
              <i class="fa-solid fa-user-tie text-blue-500 rounded-full bg-blue-50 p-4"></i>
              <h2 class="text-slate-800 font-bold text-2xl">Assign Assets to Employee</h2>
            </div>
            <form id="assignEmployeeDeptForm" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
              <input type="hidden" name="assign_to_employee" value="1">
              <div id="selectedEmployeeDeptAssetsContainer"></div>

              <div class="col-span-2">
                <label class="block mb-2">Department</label>
                <input type="text" id="deptModalDept" class="w-full border rounded px-3 py-2 text-sm bg-slate-100" disabled readonly>
                <input type="hidden" name="department_id" id="deptModalDeptId">
              </div>

              <div class="col-span-2">

                <label class="block mb-2">Select Employee</label>
                <select name="employee_id" id="employeeDeptSelect" class="w-full border rounded px-3 py-2 text-sm">
                  <option value="">-- Select Employee --</option>
                  <?php
                  $employees = fetchEmployeeActive($pdo, 'Active');
                  foreach ($employees as $employee): ?>
                    <option value="<?= htmlspecialchars($employee['id']) ?>">
                      <!-- data-branch="htmlspecialchars($employee['branch_id'] ?? '') ?>" -->
                      <?= htmlspecialchars(ucwords($employee['employee_name'])) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                </select>
              </div>

              <!-- File Upload Section -->
              <div class="col-span-2">
                <label class="block mb-2 font-semibold text-slate-700">Upload Agreement File (Optional)</label>
                <div class="border-2 border-dashed border-slate-300 rounded-lg p-4 bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer" id="dropZoneDept">
                  <input type="file" name="agreement_file" id="agreement_file_dept" accept=".pdf,.doc,.docx" class="hidden">
                  <div class="text-center">
                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600 text-sm">
                      <span id="fileNameDept">Drag and drop your PDF or DOC file here, or click to select</span>
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max 10MB)</p>
                  </div>
                </div>
                <div id="fileInfoDept" class="mt-2 hidden">
                  <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded px-3 py-2">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-file text-emerald-600"></i>
                      <span id="uploadedFileNameDept" class="text-sm text-emerald-700"></span>
                    </div>
                    <button type="button" id="removeFileDept" class="text-emerald-600 hover:text-emerald-800">
                      <i class="fa-solid fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-span-2" id="employeeAssetsSummary">
                <p class="text-slate-600 text-sm">Selected: <span id="employeeAssetsCount">0</span> asset(s)</p>
              </div>

              <div class="col-span-2 flex gap-4 pt-4">
                <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-employee-dept-btn">
                  Cancel
                </button>
                <button type="submit" class="bg-red-600 block w-full px-2 py-1 text-white text-xs font-bold rounded-sm hover:bg-red-700 cursor-pointer">
                  Assign to Employee
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- MODAL 3: Move to Branch Level (from Department) -->
        <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="move-branch-modal">
          <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
            <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
              <i class="fa-solid fa-arrow-rotate-left text-amber-500 rounded-full bg-amber-50 p-4"></i>
              <h2 class="text-slate-800 font-bold text-2xl">Move Assets to Branch Level</h2>
            </div>
            <form id="moveBranchForm" method="POST" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
              <input type="hidden" name="unassign_from_department" value="1">
              <div id="selectedMoveBranchAssetsContainer"></div>

              <div class="col-span-2">
                <p class="text-gray-600">This will return the selected asset(s) to the branch level, making them available for assignment to other departments.</p>
              </div>

              <div class="col-span-2" id="moveBranchAssetsSummary">
                <p class="text-slate-600 text-sm">Selected: <span id="moveBranchAssetsCount">0</span> asset(s)</p>
              </div>

              <div class="col-span-2 flex gap-4 pt-4">
                <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-move-branch-btn">
                  Cancel
                </button>
                <button type="submit" class="bg-amber-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-amber-700 cursor-pointer">
                  Move to Branch Level
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- MODAL 4: Return to Department (from Employee) -->
        <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="return-employee-modal">
          <div class="w-full max-w-2xl bg-white mx-auto rounded-sm p-4 shadow-lg">
            <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
              <i class="fa-solid fa-arrow-rotate-left text-orange-500 rounded-full bg-orange-50 p-4"></i>
              <h2 class="text-slate-800 font-bold text-2xl">Return Assets to Department</h2>
            </div>
            <form id="returnEmployeeForm" method="POST" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-4 form text-sm font-medium">
              <input type="hidden" name="unassign_from_employee" value="1">
              <div id="selectedReturnEmployeeAssetsContainer"></div>

              <div class="col-span-2">
                <p class="text-gray-600">This will return the selected asset(s) to the department level.</p>
              </div>

              <div class="col-span-2" id="returnEmployeeAssetsSummary">
                <p class="text-slate-600 text-sm">Selected: <span id="returnEmployeeAssetsCount">0</span> asset(s)</p>
              </div>

              <div class="col-span-2 flex gap-4 pt-4">
                <button type="button" class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="cancel-return-employee-btn">
                  Cancel
                </button>
                <button type="submit" class="bg-orange-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-orange-700 cursor-pointer">
                  Return to Department
                </button>
              </div>
            </form>
          </div>
        </div>


        <!-- <h2 class="text-2xl font-extrabold text-red-700 my-3">Branch Assets</h2> -->


        <!-- Asset Statistics Cards -->
        <?php if (isset($branchSummary) && $branchSummary): ?>
          <div class="mt-4 mb-0 px-4 pb-4 ">
            <div class="flex flex-wrap gap-2 justify-end">
              <!-- All Assets Button -->
              <button class="filter-btn px-4 py-2 rounded-md text-xs font-medium border transition-all active cursor-pointer" data-filter="all">
                <i class="fa-solid fa-list mr-2"></i>
                All Assets (<?= $branchSummary['total_assets'] ?? 0 ?>)
              </button>

              <!-- Assigned to Employees -->
              <button class="filter-btn px-4 py-2 rounded-md text-xs font-medium border transition-all cursor-pointer" data-filter="assigned">
                <i class="fa-solid fa-user-check mr-2"></i>
                Assigned (<?= $branchSummary['employee_level_assets'] ?? 0 ?>)
              </button>

              <!-- Department Level -->
              <button class="filter-btn px-4 py-2 rounded-md text-xs font-medium border transition-all cursor-pointer" data-filter="department">
                <i class="fa-solid fa-sitemap mr-2"></i>
                Department Level (<?= $branchSummary['department_level_assets'] ?? 0 ?>)
              </button>

              <!-- In Repair -->
              <button class="filter-btn px-4 py-2 rounded-md text-xs font-medium border transition-all cursor-pointer" data-filter="repair">
                <i class="fa-solid fa-wrench mr-2"></i>
                In Repair (<?= $branchSummary['under_maintenance'] ?? 0 ?>)
              </button>

              <!-- Defective Assets -->
              <button class="filter-btn px-4 py-2 rounded-md text-xs font-medium border transition-all cursor-pointer" data-filter="defective">
                <i class="fa-solid fa-ban mr-2"></i>
                Defective (<?= $branchSummary['defective'] ?? 0 ?>)
              </button>

            </div>
          </div>
        <?php endif; ?>

        <!-- 
        <div class="overflow-x-auto">
          <table id="branchAssetTable" class="row-border text-left w-full hover">
            <thead>
              <tr>
                <th><input type="checkbox" id="headerCheckbox" class="select-all-checkbox"></th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Category</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Serial No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Department</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Assigned To</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Status</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php if (!empty($assets)): ?>
                <?php $itemCounter = 1; ?>
                <?php foreach ($assets as $asset):
                  $assetPath = getAssetAssignmentPath($pdo, $asset['asset_id']);
                ?>


                  <tr class="text-xs font-light text-left border-b hover:bg-slate-50">
                    <td class="py-3 px-2">
                      <input type="checkbox" name="selected_assets[]" value="<?= htmlspecialchars($asset['asset_id']) ?>" class="asset-checkbox" data-status="<?= htmlspecialchars($asset['status']) ?>"
                        data-department-id="<?= $assetPath['department_id'] ?>">
                    </td>

                    <td class="w-2/12 object-contain px-4 py-3 text-xs text-slate-900 font-normal">
                      <div class="flex items-center gap-x-2">
                        <img
                          src="/<?= !empty($asset['photo']) ? htmlspecialchars($asset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                          alt="No image available"
                          class="w-2/12 rounded-full object-contain">
                        <span>
                          <?= htmlspecialchars(ucfirst($asset['manufacturer'] ?? '—')) ?>
                          <?= htmlspecialchars(ucfirst($asset['model'] ?? '—')) ?>
                        </span>

                      </div>
                    </td>
                    <td class="py-3 px-2">
                      <?= htmlspecialchars($asset['category_name'] ?? '—') ?>
                    </td>
                    <td class="py-3 px-2 w-1/12 text-blue-500">
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>" class="hover:text-blue-700 hover:underline hover:font-bold">
                        <?= htmlspecialchars($asset['asset_number'] ?? '—') ?>
                      </a>
                    </td>
                    <td class="py-3 px-2">
                      <?= htmlspecialchars($asset['serial_number'] ?? '—') ?>
                    </td>

                   
                    <td class="py-3 px-2 italic">
                      <?= htmlspecialchars_decode($assetPath['department_name'] ?? '—') ?>
                    </td>

                    <td class="py-3 px-2 font-bold text-xs italic">
                      <?php if (!empty($assetPath['employee_name'])): ?>
                        <?= htmlspecialchars(ucwords($assetPath['employee_name'])) ?>
                      <?php else: ?>
                        <span class="text-slate-400 text-xs">—</span>
                      <?php endif; ?>
                    </td>

                    <?php
                    $statusClassMap = [
                      'Branch Assigned' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                      'Department Assigned' => 'text-amber-700 bg-amber-50 border border-amber-200',
                      'Employee Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Under Maintenance' => 'text-orange-700 bg-orange-50 border border-orange-200',
                      'Uncommitted' => 'text-red-700 bg-red-50 border border-red-200'

                    ];
                    $currentStatus = $asset['status'] ?? '-';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>

                   
                    <td class="py-3 px-2">
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?= $statusClass ?>">
                        <?php
                        $statusDisplay = [
                          'Available' => 'Available',
                          'Branch Assigned' => 'Available',
                          'Department Assigned' => 'Dept Assigned',
                          'Employee Assigned' => 'Assigned',
                          'Under Maintenance' => 'Under Maintenance',
                          'Defective' => 'Defective',
                          'Uncommitted' => 'Uncommitted'

                        ];
                        echo htmlspecialchars($statusDisplay[$currentStatus] ?? $currentStatus);
                        ?>
                      </span>
                    </td>

                

                    <td class="py-3 px-2 relative">
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>">
                        <i class="fa-solid fa-eye"></i>
                      </a>

                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div> -->
        <div class="overflow-x-auto">
          <table id="branchAssetTable" class="row-border text-left w-full hover">
            <thead>
              <tr>
                <th><input type="checkbox" id="headerCheckbox" class="select-all-checkbox"></th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Category</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Asset No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Serial No.</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Department</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Assigned To</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Status</th>
                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php if (!empty($assets)): ?>
                <?php foreach ($assets as $asset):
                  $assetPath = getAssetAssignmentPath($pdo, $asset['asset_id']);
                ?>
                  <tr class="text-xs font-light text-left border-b hover:bg-slate-50 asset-row" data-status="<?= htmlspecialchars($asset['status']) ?>">
                    <td class="py-3 px-2">
                      <input type="checkbox" name="selected_assets[]" value="<?= htmlspecialchars($asset['asset_id']) ?>" class="asset-checkbox" data-status="<?= htmlspecialchars($asset['status']) ?>"
                        data-department-id="<?= $assetPath['department_id'] ?>">
                    </td>

                    <td class="w-2/12 object-contain px-4 py-3 text-xs text-slate-900 font-normal">
                      <div class="flex items-center gap-x-2">
                        <img
                          src="/<?= !empty($asset['photo']) ? htmlspecialchars($asset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                          alt="No image available"
                          class="w-2/12 rounded-full object-contain">
                        <span>
                          <?= htmlspecialchars(ucfirst($asset['manufacturer'] ?? '—')) ?>
                          <?= htmlspecialchars(ucfirst($asset['model'] ?? '—')) ?>
                        </span>
                      </div>
                    </td>
                    <td class="py-3 px-2">
                      <?= htmlspecialchars($asset['category_name'] ?? '—') ?>
                    </td>
                    <td class="py-3 px-2 w-1/12 text-blue-500">
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>" class="hover:text-blue-700 hover:underline hover:font-bold">
                        <?= htmlspecialchars($asset['asset_number'] ?? '—') ?>
                      </a>
                    </td>
                    <td class="py-3 px-2">
                      <?= htmlspecialchars($asset['serial_number'] ?? '—') ?>
                    </td>

                    <!-- Department Column -->
                    <td class="py-3 px-2 italic">
                      <?= htmlspecialchars_decode($assetPath['department_name'] ?? '—') ?>
                    </td>

                    <!-- Assigned To Column -->
                    <td class="py-3 px-2 font-bold text-xs italic">
                      <?php if (!empty($assetPath['employee_name'])): ?>
                        <?= htmlspecialchars(ucwords($assetPath['employee_name'])) ?>
                      <?php else: ?>
                        <span class="text-slate-400 text-xs">—</span>
                      <?php endif; ?>
                    </td>

                    <?php
                    $statusClassMap = [
                      'Branch Assigned' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                      'Department Assigned' => 'text-amber-700 bg-amber-50 border border-amber-200',
                      'Employee Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Assigned' => 'text-blue-700 bg-blue-50 border border-blue-200',
                      'Under Maintenance' => 'text-orange-700 bg-orange-50 border border-orange-200',
                      'Uncommitted' => 'text-red-700 bg-red-50 border border-red-200'
                    ];
                    $currentStatus = $asset['status'] ?? '-';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>

                    <!-- Status Column -->
                    <td class="py-3 px-2">
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?= $statusClass ?>">
                        <?php
                        $statusDisplay = [
                          'Available' => 'Available',
                          'Branch Assigned' => 'Available',
                          'Department Assigned' => 'Dept Assigned',
                          'Employee Assigned' => 'Assigned',
                          'Under Maintenance' => 'Under Maintenance',
                          'Defective' => 'Defective',
                          'Uncommitted' => 'Uncommitted'
                        ];
                        echo htmlspecialchars($statusDisplay[$currentStatus] ?? $currentStatus);
                        ?>
                      </span>
                    </td>

                    <td class="py-3 px-2 relative">
                      <a href="/manage-hardware/assign-asset/asset-details?id=<?= htmlspecialchars($asset['asset_id']) ?>">
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
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let table = $('#branchAssetTable').DataTable();
    const filterButtons = document.querySelectorAll('.filter-btn');
    let currentFilter = 'all';

    // Custom filter function for DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const row = table.row(dataIndex).node();
      const status = row.getAttribute('data-status');

      if (currentFilter === 'all') {
        return true;
      } else if (currentFilter === 'assigned') {
        return status === 'Employee Assigned';
      } else if (currentFilter === 'department') {
        return status === 'Department Assigned';
      } else if (currentFilter === 'repair') {
        return status === 'Under Maintenance';
      } else if (currentFilter === 'defective') {
        return status === 'Uncommitted';
      }
      return true;
    });

    // Filter button click handler
    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        currentFilter = this.getAttribute('data-filter');

        // Update active button styling
        filterButtons.forEach(btn => {
          btn.classList.remove('active', 'bg-blue-700', 'text-white', 'border-blue-700');
          btn.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
        });

        this.classList.add('active', 'bg-blue-700', 'text-white', 'border-blue-700');
        this.classList.remove('bg-white', 'text-slate-700', 'border-slate-300');

        // Redraw DataTable with new filter
        table.draw();
      });
    });
  });
</script>

<style>
  .filter-btn {
    background-color: white;
    color: #374151;
    border-color: #e2e8f0;
  }

  .filter-btn.active {
    background-color: #fb2c36;
    color: white;
    border-color: #c10007;
  }

  .filter-btn:hover {
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  }
</style>

<script src="/assets/js/branch-detail.js"></script>
<?php require("views/partials/footer.php") ?>