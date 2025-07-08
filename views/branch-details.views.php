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
        <a href="/branch" class="text-slate-700 hover:text-red-800 text-sm flex items-center gap-1">
          <i class="fa-solid fa-arrow-left ml-2"></i>
          Back to Branches
        </a>

      </div>
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-department-modal">
        Add Department
      </button>
    </div>
    <div class="my-4">
      <h2 class="text-2xl font-extrabold text-red-700">
        <?= htmlspecialchars($branch['branch_name'] ?? 'Unknown Branch') ?> Branch
      </h2>
      <!-- Branch Info Card -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-800 mt-1 font-bold">
            <span class="font-medium text-slate-600">Branch Manager:</span>
            <?= htmlspecialchars($branch['branch_manager'] ?? 'Not assigned') ?>
          </p>
          <p class="text-sm text-slate-800">
            <span class="font-medium text-slate-600">Total Departments:</span>
            <?= count($departments) ?>
          </p>
        </div>
      </div>

    </div>

    <!-- Add Department Modal -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="department-modal">
      <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id="department-modal-form-box">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-building text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="department-modal-title">Add New Department</h2>
        </div>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" method="POST" id="department-modal-form">
          <!-- Department -->
          <input type="hidden" name="branchId" value="<?= htmlspecialchars($branch['id'] ?? 0) ?>">
          <input type="hidden" name="departmentId" id="department-id" value="0">
          <input type="hidden" name="branchName" value="<?= htmlspecialchars($branch['branch_name']) ?>">

          <div class="col-span-10">
            <label for="input-department" class="block text-sm font-light text-slate-800">Department Name <span class="text-red-600">*</span></label>
            <div class="relative">
              <input type="text"
                class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3"
                placeholder="e.g., Human Resources"
                name="inputDepartment"
                id="input-department" />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-sitemap"></i>
              </div>
            </div>
          </div>

          <!-- Department Head -->
          <div class="col-span-10">
            <label for="input-department-head" class="block text-sm font-light text-slate-800">Department Head</label>
            <div class="relative">
              <input type="text"
                class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3"
                placeholder="e.g., Maria Santos (Optional)"
                name="inputDepartmentHead"
                id="input-department-head" />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-user-tie"></i>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="col-span-10 flex gap-4 pt-4">
            <button type="button"
              class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
              id="department-modal-cancel-btn">
              Cancel
            </button>
            <button type="submit"
              class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 cursor-pointer"
              name="modalAddDepartmentBtn"
              id="department-modal-add-btn">
              Add Department
            </button>
          </div>
        </form>
      </div>
    </div>


    <!-- Departments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if (!empty($departments)): ?>
        <?php foreach ($departments as $department): ?>
          <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-sitemap text-red-500"></i>
                  </div>
                  <div>
                    <h3 class="font-semibold text-slate-800 text-lg">
                      <?= htmlspecialchars($department['department_name'] ?? 'Unknown') ?>
                    </h3>
                  </div>
                </div>
                <div class="relative">
                  <i class="fa-solid fa-ellipsis-vertical cursor-pointer text-slate-400 hover:text-slate-600 department-menu-trigger" data-department-id="<?= $department['id'] ?>"></i>
                  <div class="absolute top-6 right-0 w-32 bg-white border rounded shadow-lg z-10 hidden department-menu" id="department-menu-<?= $department['id'] ?>">
                    <ul class="text-xs text-slate-700 font-light">
                      <li class="px-4 py-2 hover:bg-slate-100 border-b">
                        <button class="cursor-pointer w-full text-left edit-department-btn" data-department-id="<?= $department['id'] ?>">
                          Edit
                        </button>
                      </li>
                      <li class="px-4 py-2 hover:bg-slate-100">
                        <button class="cursor-pointer w-full text-left delete-department-btn" data-department-id="<?= $department['id'] ?>">
                          Delete
                        </button>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="space-y-2 text-sm text-slate-600">
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-user-tie text-slate-400"></i>
                  <span>
                    <strong>Head:</strong>
                    <?= htmlspecialchars($department['department_head'] ?? 'Not assigned') ?>
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-calendar text-slate-400"></i>
                  <span>
                    <strong>Created:</strong>
                    <?= date('M d, Y', strtotime($department['created_at'] ?? 'now')) ?>
                  </span>
                </div>
              </div>
            </div>

            <div class="bg-slate-50 px-6 py-3 border-t">
              <button class="text-red-600 hover:text-red-700 text-sm font-medium w-full text-left">
                <i class="fa-solid fa-eye mr-2"></i>
                View Details
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full text-center py-12">
          <div class="text-slate-400 mb-4">
            <i class="fa-solid fa-building text-6xl"></i>
          </div>
          <h3 class="text-lg font-medium text-slate-600 mb-2">No Departments Yet</h3>
          <p class="text-slate-500 mb-4">Start by adding your first department to this branch.</p>
          <button class="text-red-600 hover:text-red-700 font-medium" id="add-first-department">
            <i class="fa-solid fa-plus mr-2"></i>
            Add First Department
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<script src="/assets/js/branch-detail.js"></script>
<?php require("views/partials/footer.php") ?>