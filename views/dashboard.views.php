<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 space-y-7">
    <header class="flex justify-between items-center pr-4 shadow-sm">
      <div class="flex items-center ">
        <img src="assets/images/toprank-logo.png" alt="" class="w-3/12 py-1 px-2">
        <!-- <div class="flex flex-col gap-0">
          <h4 class="text-slate-400">Company</h4>
          <h2 class="text-slate-600 font-bold">TOPRANK</h2>
        </div> -->
      </div>
      <div>
        <button class="flex items-center bg-red-500 hover:bg-red-600 text-white w-full rounded-sm font-bold text-sm py-3 px-4 cursor-pointer">
          <i class="fa-solid fa-plus"></i>
          <p class="whitespace-nowrap ml-2">Add Hardware</p>
        </button>
      </div>
    </header>

    <!-- Dashboard Title -->
    <article class="px-6">
      <h2 class="text-2xl font-bold text-slate-800">Asset Inventory Management Dashboard</h2>
    </article>

    <!-- Main Statistics Cards -->
    <article class="px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

      <!-- Total Hardware -->
      <a href="/dashboard-filter?type=total" class="block p-4 bg-cyan-50 border border-cyan-200 rounded-lg hover:shadow-md hover:bg-cyan-100 transition-all cursor-pointer">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-slate-600">Total Asset</p>
          <i class="fa-solid fa-server text-slate-500"></i>
        </div>
        <p class="text-3xl font-bold text-slate-700"><?= $stats['total_assets'] ?? 0 ?></p>
        <p class="text-xs text-slate-500 mt-2">All devices in system</p>
      </a>

      <!-- Assigned Assets (Combined) -->
      <a href="/dashboard-filter?type=assigned" class="block p-4 bg-blue-50 border border-blue-200 rounded-lg hover:shadow-md hover:bg-blue-100 transition-all cursor-pointer">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-slate-600">Assigned Assets</p>
          <i class="fa-solid fa-chalkboard-user text-blue-500"></i>
        </div>
        <p class="text-3xl font-bold text-blue-700"><?= ($stats['employee_assigned'] ?? 0) + ($stats['branch_assigned'] ?? 0) + ($stats['department_assigned'] ?? 0) ?></p>
        <p class="text-xs text-slate-500 mt-2">All assigned devices</p>
      </a>

      <!-- Available Devices -->
      <a href="/dashboard-filter?type=available" class="block p-4 bg-green-50 border border-green-200 rounded-lg hover:shadow-md hover:bg-green-100 transition-all cursor-pointer">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-slate-600">Available</p>
          <i class="fa-solid fa-inbox text-green-500"></i>
        </div>
        <p class="text-3xl font-bold text-green-700"><?= $stats['available'] ?? 0 ?></p>
        <p class="text-xs text-slate-500 mt-2">Ready for assignment</p>
      </a>

      <!-- In Maintenance -->
      <a href="/dashboard-filter?type=maintenance" class="block p-4 bg-orange-50 border border-orange-200 rounded-lg hover:shadow-md hover:bg-orange-100 transition-all cursor-pointer">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-slate-600">In Maintenance</p>
          <i class="fa-solid fa-wrench text-orange-500"></i>
        </div>
        <p class="text-3xl font-bold text-orange-700"><?= $stats['under_maintenance'] ?? 0 ?></p>
        <p class="text-xs text-slate-500 mt-2">Under maintenance</p>
      </a>

      <!-- Defective Assets -->
      <a href="/dashboard-filter?type=defective" class="block p-4 bg-red-50 border border-red-200 rounded-lg hover:shadow-md hover:bg-red-100 transition-all cursor-pointer">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-slate-600">Defective Assets</p>
          <i class="fa-solid fa-ban text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-red-700"><?= $stats['uncommitted'] ?? 0 ?></p>
        <p class="text-xs text-slate-500 mt-2">Uncommitted assets</p>
      </a>
    </article>



    <!-- Recent Assignments Table -->
    <?php if (!empty($recentAssignments)): ?>
      <article class="px-6 pb-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Recent Assignments</h3>
        <div class="border border-slate-200 rounded-lg overflow-hidden bg-white">
          <div class="overflow-x-auto">
            <table id="recentAssignmentTable" class="display text-left w-full">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Asset Number</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Hardware</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Category</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Assigned To</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Last Update</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <?php foreach ($recentAssignments as $assignment): ?>
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-slate-800"><?= htmlspecialchars($assignment['asset_number']) ?></td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                      <?= htmlspecialchars($assignment['manufacturer'] ?? 'N/A') ?> <?= htmlspecialchars($assignment['model'] ?? '') ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600"><?= htmlspecialchars($assignment['category_name'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-600"><?= htmlspecialchars($assignment['assigned_to'] ?? 'Unassigned') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-600"><?= date('M d, Y', strtotime($assignment['last_update'])) ?></td>
                    <td class="px-4 py-3 text-sm">
                      <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block
                    <?php
                    if ($assignment['status'] === 'Employee Assigned') echo 'bg-green-50 text-green-700 border border-green-200';
                    elseif ($assignment['status'] === 'Branch Assigned') echo 'bg-purple-50 text-purple-700 border border-purple-200';
                    elseif ($assignment['status'] === 'Department Assigned') echo 'bg-blue-50 text-blue-700 border border-blue-200';
                    elseif ($assignment['status'] === 'Under Maintenance') echo 'bg-orange-50 text-orange-700 border border-orange-200';
                    elseif ($assignment['status'] === 'Uncommitted') echo 'bg-red-50 text-red-700 border border-red-200';
                    else echo 'bg-slate-50 text-slate-700 border border-slate-200';
                    ?>
                  ">
                        <?= htmlspecialchars($assignment['status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </article>
    <?php endif; ?>

  </section>
</main>

<?php require("views/partials/footer.php"); ?>