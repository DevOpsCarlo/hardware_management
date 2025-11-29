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
          timer: 2000,
          timerProgressBar: true,
          position: 'top-end',
          toast: true
        });
      </script>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="flex items-center justify-between my-4">
      <h2 class="text-2xl font-extrabold text-red-700 ml-2">Toprank Branches</h2>
      <button class="text-white bg-red-600 border px-3 py-2 text-sm hover:bg-red-700 hover:text-white rounded-xl mr-2 cursor-pointer flex items-center gap-x-1" id="toggle-modal">
        <i class="fa-solid fa-plus"></i>
        <span class="font-bold text-xs">
          New Branch
        </span>
      </button>
    </div>

    <!-- Add Branch Modal -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden " id="modal">
      <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id="modal-form-box">
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div>
            <i class="fa-solid fa-building text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="modal-title">Add New Branch</h2>
        </div>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" method="POST" id="modal-form">
          <input type="hidden" name="branchId" id="branch-id" value="<?= htmlspecialchars($branch['id'] ?? 0) ?>">
          <div class="col-span-10">
            <label for="input-branch" class="block text-sm font-light text-slate-800">Branch Name <span class="text-red-600">*</span></label>
            <div class="relative">
              <input type="text"
                class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3"
                placeholder="e.g., Legarda"
                name="inputBranch"
                id="input-branch" />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-location-dot"></i>
              </div>
            </div>
            <span class="text-pink-600 text-sm hidden font-light" id="input-validation"></span>
          </div>

          <div class="col-span-10 flex gap-4 pt-4">
            <button class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="modal-cancel-btn" type="button">
              Cancel
            </button>
            <button type="submit" class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 cursor-pointer" name="modalAddBtn" id="modal-add-btn">
              Add Branch
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Branch Table -->
    <article class="col-span-10 text-sm font-light" id="branch-table">
      <div>
        <table id="branchTable" class="row-border text-left w-full hover">
          <thead class="text-left">
            <tr>
              <!-- <th class="px-4 py-2">No.</th> -->
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Branch</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Total Assets</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Assigned</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Available</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">In Repair</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Uncommitted</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Action</th>
            </tr>
          </thead>
          <tbody class="text-sm font-light">
            <?php if (!empty($branches)): ?>
              <?php foreach ($branches as $index => $branch): ?>
                <tr class="text-xs font-light text-left hover:bg-slate-100 transition duration-150 cursor-pointer">
                  <!-- <td class="px-4 py-2"><?= $index + 1 ?></td> -->
                  <td class="px-4 py-2"><?= htmlspecialchars($branch['branch_name'] ?? "Empty") ?></td>

                  <!-- TOTAL ASSETS -->
                  <td class="px-4 py-2">
                    <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                      <?= $branch['total_assets'] ?? 0 ?>
                    </span>
                  </td>

                  <!-- ASSIGNED ASSETS -->
                  <td class="px-4 py-2">
                    <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                      <?= $branch['assigned_count'] ?? 0 ?>
                    </span>
                  </td>

                  <!-- UNASSIGNED ASSETS -->
                  <td class="px-4 py-2">
                    <span class="inline-block bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                      <?= $branch['unassigned_count'] ?? 0 ?>
                    </span>
                  </td>

                  <!-- IN REPAIR -->
                  <td class="px-4 py-2">
                    <span class="inline-block bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">
                      <?= $branch['in_repair_count'] ?? 0 ?>
                    </span>
                  </td>

                  <!-- Uncommited -->
                  <td class="px-4 py-2">
                    <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                      <?= $branch['uncommitted_count'] ?? 0 ?>
                    </span>
                  </td>

                  <!-- ACTIONS -->
                  <td class="px-4 py-2 relative">
                    <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                    <div class="absolute top-5 left-5 mb-2 w-20 bg-white border rounded shadow z-10 hidden menu">
                      <ul class="text-xs text-slate-700 font-light">
                        <li class="px-4 py-2 hover:bg-slate-100 border-b">
                          <a href="/branch/<?= urlencode($branch['branch_name']) ?>" class="cursor-pointer w-full text-left">View</a>
                        </li>
                        <li class="px-4 py-2 hover:bg-slate-100 border-b">
                          <button class="cursor-pointer block w-full text-left edit-btn" type="button"
                            data-branch-id="<?= htmlspecialchars($branch['id'] ?? 0) ?>"
                            data-branch-name="<?= htmlspecialchars($branch['branch_name']) ?>">
                            Edit
                          </button>
                        </li>
                        <li class="px-4 py-2 hover:bg-slate-100">
                          <button class="cursor-pointer w-full text-left delete-btn"
                            data-branch-id="<?= htmlspecialchars($branch['id'] ?? 0) ?>"
                            data-branch-name="<?= htmlspecialchars($branch['branch_name']) ?>">
                            Delete
                          </button>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center py-4 text-slate-500">No branches found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</main>

<script src="/assets/js/branch.js"></script>
<?php require("views/partials/footer.php") ?>