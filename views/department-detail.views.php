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
        <a href="/branch/<?= urlencode($branch['branch_name']) ?>" class="text-sm text-red-600 hover:underline">
          ← Back to <?= htmlspecialchars($branch['branch_name']) ?> Branch
        </a>
      </div>
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-modal">
        + Add Employee
      </button>
    </div>
    <div class="my-4 shadow-sm py-2 px-4">
      <h2 class="text-2xl font-extrabold text-red-700">
        <?= htmlspecialchars($department['department_name']) ?> Department
      </h2>
      <!-- Branch Info Card -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-800 mt-1 font-bold">
            <span class="font-medium text-slate-600">Head Dept:</span>
            <?= htmlspecialchars($department['department_head'] ?? 'Not assigned') ?>
          </p>
          <p class="text-sm text-slate-800">
            <span class="font-medium text-slate-600">Total Departments:</span>
            <?= count($department) ?>
          </p>
        </div>
      </div>

    </div>

    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="modal">
      <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id="employee-modal-form-box">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-users text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="employee-modal-title">Add Employees</h2>
        </div>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" method="POST" id="employee-modal-form">
          <!-- Table of Employees with Checkboxes -->
          <div class="col-span-10">
            <label for="input-department" class="block text-sm font-light text-slate-800">Select Employees<span class="text-red-600">*</span></label>
            <?php if (!empty($fetchEmployees)): ?>
              <table class="display" id="addEmployeeTable">
                <thead>
                  <tr>
                    <th class="border p-2">Select</th>
                    <th class="border p-2">Employee Name</th>
                    <th class="border p-2">Employee ID</th>
                    <th class="border p-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($fetchEmployees as $employee): ?>
                    <tr>
                      <td class="border p-2">
                        <input type="checkbox" name="employee_ids[]" value="<?= $employee['id'] ?>" class="employee-checkbox" />
                      </td>
                      <td class="border p-2"><?= htmlspecialchars(ucfirst($employee['employee_name'])) ?></td>
                      <td class="border p-2"><?= htmlspecialchars($employee['employee_id']) ?></td>
                      <td class="border p-2"><?= htmlspecialchars($employee['option_status']) ?></td>

                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="col-span-full text-center py-12">
                <div class="text-slate-400 mb-4">
                  <i class="fa-solid fa-building text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-600 mb-2">No employees added yet</h3>
                <p class="text-slate-500 mb-4">Start by adding your first employee to this department.</p>
                <button class="text-red-600 hover:text-red-700 font-medium cursor-pointer" id="add-first-employee">
                  <i class="fa-solid fa-plus mr-2"></i>
                  Add First Employee
                </button>
              </div>
            <?php endif; ?>
          </div>

          <!-- Submit Button -->
          <div class="col-span-10 flex gap-4 pt-4">
            <button type="button"
              class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
              id="modal-cancel-btn">
              Cancel
            </button>
            <button type="submit"
              class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 cursor-pointer"
              name="modalAddEmployee"
              id="modal-add-btn">
              Add Employee
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Inventory table -->
    <article class="col-span-10 text-sm mt-4 font-light" id="department-employee-table">
      <div>
        <div>

          <table id="departmentEmployeeTable" class="display">
            <thead>
              <tr>
                <th>Employee Name</th>
                <th>Asset No.</th>
                <th>Equipment</th>
                <th>Brand</th>
                <th>Serial No.</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($departmentEmployees)): ?>

                <?php foreach ($departmentEmployees as $employee): ?>
                  <tr>
                    <td><?= htmlspecialchars($employee['employee_name']) ?></td>
                    <td>TRA-01-0002</td>
                    <td>Desktop Monitor</td>
                    <td>Acer</td>
                    <td>01cr23g</td>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-5 left-5 mb-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light">
                          <!-- <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <a href="/branch/<?= urlencode($branch['branch_name']) ?>" class="cursor-pointer w-full text-left">View</a>
                          </li> -->

                          <!-- Edit Asset -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left edit-btn" type="button"
                              data-employee-id="<?= htmlspecialchars($employee['id']) ?>"
                              data-employee-name="<?= htmlspecialchars($employee['employee_name']) ?>">
                              Edit
                            </button>
                          </li>
                          <!-- Delete -->
                          <li class="px-4 py-2 hover:bg-slate-100">
                            <button class="cursor-pointer w-full text-left delete-btn"
                              data-employee-id="<?= htmlspecialchars($employee['id']) ?>"
                              data-employee-name="<?= htmlspecialchars($employee['employee_name']) ?>">
                              Remove
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
                    <i class="fa-solid fa-building text-6xl"></i>
                  </div>
                  <h3 class="text-lg font-medium text-slate-600 mb-2">Employee added in this department</h3>
                  <p class="text-slate-500 mb-4">Start by adding your first employee to this department.</p>
                  <button class="text-red-600 hover:text-red-700 font-medium cursor-pointer" id="add-first-department">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Add First Employee
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

<script src="/assets/js/department-detail.js"></script>
<?php require("views/partials/footer.php") ?>