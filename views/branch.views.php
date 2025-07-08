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

    <div class="flex items-center justify-between my-4">
      <h2 class="text-2xl font-extrabold text-red-700 ml-2">Toprank Branches</h2>
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-modal"> Add Branch</button>
    </div>

    <!-- Add Branch Modal -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden " id="modal">
      <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id=modal-form-box>
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-building text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="modal-title">Add New Branch</h2>
        </div>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" method="POST" id="modal-form">
          <!-- Branch -->
          <input type="hidden" name="branchId" id="branch-id" value="<?= htmlspecialchars($branch['id'] ?? 0) ?>">
          <div class="col-span-10">
            <label for="input-branch" class="block text-sm font-light text-slate-800">Branch Name <span class="text-red-600">*</span></label>
            <div class="relative">
              <input type="text"
                class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3 "
                placeholder="e.g., Legarda"
                name="inputBranch"
                id="input-branch" />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-location-dot"></i>
              </div>
            </div>
            <span class="text-pink-600 text-sm hidden font-light" id="input-validation"></span>
          </div>

          <!-- Branch Manager-->
          <div class="col-span-10">
            <label for="input-branch" class="block text-sm font-light text-slate-800">Branch Manager</label>
            <div class="relative">
              <input type="text"
                class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3"
                placeholder="e.g Juan Dela Cruz (Optional)"
                name="inputBranchManager"
                id="input-branch-manager" />

              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-user"></i>
              </div>
            </div>

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
              name="modalAddBtn"
              id="modal-add-btn">
              Add Branch
            </button>
          </div>
        </form>
      </div>
    </div>

    <article class="col-span-10 text-sm font-light" id="branch-table">
      <div>
        <div>
          <table id="branchTable" class="display text-left">
            <thead>
              <tr>
                <th>No.</th>
                <th>Branch</th>
                <th>Branch Manager</th>
                <th>Branch Asset</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody class="text-sm font-light">
              <?php if (!empty($branches)): ?>
                <?php foreach ($branches as $index => $branch): ?>
                  <tr class="text-xs font-light text-left">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($branch['branch_name'] ?? "Empty") ?></td>
                    <td><?= htmlspecialchars($branch['branch_manager'] ?? "Empty") ?></td>
                    <td>20</td>
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
                              data-branch-id="<?= htmlspecialchars($branch['id'] ?? 0) ?>"
                              data-branch-name="<?= htmlspecialchars($branch['branch_name']) ?>"
                              data-branch-manager="<?= htmlspecialchars($branch['branch_manager'] ?? "") ?>">
                              Edit
                            </button>
                          </li>
                          <!-- Delete -->
                          <li class="px-4 py-2 hover:bg-slate-100">
                            <button class="cursor-pointer w-full text-left delete-btn"
                              data-branch-id="<?= htmlspecialchars($branch['id'] ?? 0) ?>"
                              data-branch-name="<?= htmlspecialchars($branch['branch_name']) ?>"
                              data-branch-manager="<?= htmlspecialchars($branch['branch_manager']) ?>">
                              Delete
                            </button>
                          </li>

                        </ul>
                      </div>
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


<script src="/assets/js/branch.js"></script>
<?php require("views/partials/footer.php") ?>