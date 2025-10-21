<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
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

    <?php if (isset($_SESSION['error_message'])): ?>
      <script>
        Swal.fire({
          icon: 'error',
          text: '<?= $_SESSION['error_message'] ?>',
          showConfirmButton: false,
          position: 'top-end',
          toast: true,
          timer: 3000,
          timerProgressBar: true,
        });
      </script>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>



    <div class="flex items-center justify-between my-4">
      <h2 class="text-2xl font-extrabold text-red-700 ml-2">Toprank Employee</h2>
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-modal"> + Add Employee</button>
    </div>



    <article class="col-span-10 text-sm font-light" id="employee-table">
      <div>

        <div>
          <table id="employeeTable" class="display text-left">
            <?php if (!empty($employees)): ?>
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Status</th>
                  <th>Branch</th>
                  <th>Department</th>
                  <th>Employee Asset</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="text-sm font-light">
                <?php foreach ($employees as $index => $employee): ?>
                  <tr class="text-xs font-light text-left">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($employee['employee_code'] ?? "Empty") ?></td>
                    <td><?= htmlspecialchars($employee['employee_name'] ?? "Empty") ?></td>

                    <?php
                    $statusClassMap = [
                      'Active' => 'text-emerald-500 bg-emerald-100',
                      'Inactive' => 'text-slate-500 bg-slate-100',
                      'Resigned' => 'text-orange-500 bg-orange-100',
                      'Terminated' => 'text-red-500 bg-red-100',
                      'Retired' => 'text-slate-500 bg-slate-100'
                    ];
                    $currentStatus = $employee['status'] ?? '-';
                    $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                    ?>
                    <td>
                      <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?= $statusClass ?>">
                        <?= htmlspecialchars($currentStatus) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($employee['branch_name'] ?? "-") ?></td>
                    <td><?= htmlspecialchars($employee['department_name'] ?? "-") ?></td>
                    <td><?= htmlspecialchars($employee['total_assets'] ?? "0") ?></td>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-5 left-5 mb-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light">
                          <!-- View -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <a href="/branch/<?= urlencode($branch['branch_name']) ?>" class="cursor-pointer w-full text-left">View</a>
                          </li>

                          <!-- Edit Asset -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left edit-btn" type="button"
                              data-id="<?= htmlspecialchars($employee['employee_id'] ?? 0) ?>"
                              data-employee-name="<?= htmlspecialchars($employee['employee_name']) ?>"
                              data-employee-id="<?= htmlspecialchars($employee['employee_code'] ?? "") ?>"
                              data-option-status="<?= htmlspecialchars($employee['status'] ?? "Select Status") ?>">
                              Edit
                            </button>
                          </li>
                          <!-- Delete -->
                          <li class="px-4 py-2 hover:bg-slate-100">
                            <button class="cursor-pointer w-full text-left delete-btn"
                              data-id="<?= htmlspecialchars($employee['employee_id'] ?? 0) ?>"
                              data-employee-name="<?= htmlspecialchars($employee['employee_name']) ?>"
                              data-employee-id="<?= htmlspecialchars($employee['employee_code'] ?? "") ?>">
                              Delete
                            </button>
                          </li>

                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-span-full text-center py-12">
                  <div class="text-slate-400 mb-4">
                    <i class="fa-solid fa-users text-6xl"></i>
                  </div>
                  <h3 class="text-lg font-medium text-slate-600 mb-2">No Employee Users Yet</h3>
                  <p class="text-slate-500 mb-4">Start by adding your first employee user.</p>
                  <button class="text-red-600 hover:text-red-700 font-medium cursor-pointer" id="add-first-department">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Add First User
                  </button>
                </div>
              <?php endif; ?>
              </tbody>
          </table>
        </div>
      </div>
    </article>
  </section>
</main>

<!-- Add Employee Modal -->
<div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden " id="modal">
  <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id=modal-form-box>
    <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
      <div class="">
        <i class="fa-solid fa-users text-red-500 rounded-full bg-red-50 p-4"></i>
      </div>
      <h2 class="text-slate-800 font-bold text-2xl" id="modal-title">Add New Employee</h2>
    </div>

    <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" method="POST" id="modal-form">
      <!-- Employee Name -->
      <input type="hidden" name="id" id="id" value="<?= $employee['employee_id'] ?>">
      <div class="col-span-10">
        <label for="input-employee" class="block text-sm font-light text-slate-800">Employee Name <span class="text-red-600">*</span></label>
        <div class="relative">
          <input type="text"
            class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3 "
            placeholder="e.g., Juan Dela Cruz"
            name="inputEmployeeName"
            id="input-employee-name" />
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <i class="fa-solid fa-user"></i>
          </div>
        </div>
        <span class="text-pink-600 text-xs hidden font-light" id="input-validation"></span>
      </div>

      <!-- Employee ID-->
      <div class="col-span-10">
        <label for="input-employee-id" class="block text-sm font-light text-slate-800">Employee ID</label>
        <div class="relative">
          <input type="text"
            class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3"
            placeholder="e.g 0012 (Optional)"
            name="inputEmployeeId"
            id="input-employee-id" />

          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <i class="fa-solid fa-address-card"></i>
          </div>
        </div>
      </div>

      <!-- Employee Status-->
      <div class="col-span-10">
        <label for="option-status" class="block text-sm font-light text-slate-800">Employee Status</label>
        <select name="optionStatus" id="option-status" class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3">
          <option value="" disabled selected>Select Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Resigned">Resigned</option>
          <option value="Terminated">Terminated</option>
          <option value="Retired">Retired</option>
        </select>
        <span class="text-pink-600 text-xs hidden font-light" id="input-validation-status"></span>

      </div>


      <!-- Submit Button -->
      <div class="col-span-10 flex gap-4 pt-4">
        <button
          class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
          id="modal-cancel-btn"
          type="button">
          Cancel
        </button>
        <button type="submit"
          class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 add-inventory-btn cursor-pointer"
          name="modalAddEmployee"
          id="modal-add-btn">
          Add Employee
        </button>
      </div>
    </form>
  </div>
</div>
<script src="/assets/js/employee.js"></script>
<?php require("views/partials/footer.php") ?>