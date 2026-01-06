<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
    <?php require("views/sidebar.php"); ?>
    <section class="col-span-12 md:col-span-10 space-y-7">
        <header class="flex justify-between items-center pr-4 shadow-sm">
            <div class="flex items-center ">
                <img src="assets/images/toprank-logo.png" alt="" class="w-3/12">
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fa-solid fa-arrow-left"></i>
                    <p class="whitespace-nowrap ml-2">Back to Dashboard</p>
                </a>
            </div>
        </header>

        <!-- Filter Title -->
        <article class="px-6">
            <h2 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($filterTitle) ?></h2>
            <p class="text-slate-600 mt-2">Total: <span class="font-bold text-lg"><?= count($assets) ?></span> assets</p>
        </article>

        <!-- Assets Table -->
        <?php if (!empty($assets)): ?>
            <article class="px-6 pb-6">
                <div class="border border-slate-200 rounded-lg overflow-hidden bg-white">
                    <div class="overflow-x-auto">
                        <table id="dashboardFilterTable" class="row-border text-left w-full hover">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">
                                        Asset Number
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Asset</th>
                                    <!-- <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Category</th> -->
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Branch</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Department</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Assigned To</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Conditions</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Serial Number</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($assets as $asset): ?>
                                    <tr class="">
                                        <td class="px-4 py-3 text-xs text-slate-800">
                                            <a href="/manage-hardware/assign-asset/asset-details?id=<?= $asset['asset_id'] ?>" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                <?= htmlspecialchars($asset['asset_number'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-600 w-2/12">
                                            <div class="flex items-center gap-x-2 ">
                                                <img src="/<?= !empty($asset['photo']) ? htmlspecialchars($asset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>" alt="No image available" class="w-2/12 rounded-full object-contain">
                                                <span>
                                                    <?= htmlspecialchars($asset['manufacturer'] ?? '—') ?> <?= htmlspecialchars($asset['model'] ?? '') ?>
                                                </span>
                                            </div>

                                        </td>
                                        <!-- <td class="px-4 py-3 text-sm text-slate-600"><?= htmlspecialchars($asset['category_name'] ?? '—') ?></td> -->
                                        <td class="px-4 py-3 text-xs text-slate-600"><?= htmlspecialchars($asset['branch_name'] ?? '—') ?></td>
                                        <td class="px-4 py-3 text-xs text-slate-600 italic"><?= htmlspecialchars_decode($asset['department_name'] ?? '—') ?></td>
                                        <td class="px-4 py-3 text-xs text-slate-600 font-bold italic">
                                            <?php if ($filterType === 'total'): ?>
                                                <?php if ($asset['assigned_employee_name']): ?>
                                                    <?= htmlspecialchars(ucwords($asset['assigned_employee_name'])) ?>
                                                <?php else: ?>
                                                    <span class="text-slate-400 italic">—</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($asset['assigned_employee_name'] ?? '—') ?>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-4 py-3 text-xs text-slate-600"><?= htmlspecialchars($asset['conditions'] ?? '—') ?></td>
                                        <td class="px-4 py-3 text-xs text-slate-600">
                                            <?php if ($filterType === 'total'): ?>
                                                <?= htmlspecialchars($asset['serial_number'] ?? '—') ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($asset['serial_number'] ?? '—') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 ">
                                            <?php if ($filterType === 'total'): ?>
                                                <?php if ($asset['status']): ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block
                                                    <?php
                                                    if ($asset['status'] === 'Employee Assigned') echo 'bg-blue-50 text-blue-700 border border-blue-200';
                                                    elseif ($asset['status'] === 'Branch Assigned') echo 'bg-purple-50 text-purple-700 border border-purple-200';
                                                    elseif ($asset['status'] === 'Department Assigned') echo 'bg-amber-50 text-amber-700 border border-amber-200';
                                                    elseif ($asset['status'] === 'Under Maintenance') echo 'bg-orange-50 text-orange-700 border border-orange-200';
                                                    elseif ($asset['status'] === 'Uncommitted') echo 'bg-red-50 text-red-700 border border-red-200';
                                                    elseif ($asset['status'] === 'Available') echo 'bg-green-50 text-green-700 border border-green-200';
                                                    else echo 'bg-slate-50 text-slate-700 border border-slate-200';
                                                    ?>
                                                ">

                                                        <?php
                                                        $currentStatus = $asset['status'] ?? '-';
                                                        $statusDisplay = [
                                                            'Available' => 'Available',
                                                            'Branch Assigned' => 'Available',
                                                            'Department Assigned' => 'Dept Assigned',
                                                            'Employee Assigned' => 'Employee Assigned',
                                                            'Under Maintenance' => 'Under Maintenance',
                                                            'Defective' => 'Defective',
                                                            'Uncommitted' => 'Uncommitted'

                                                        ];
                                                        echo htmlspecialchars($statusDisplay[$currentStatus] ?? $currentStatus);
                                                        ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block bg-gray-50 text-gray-700 border border-gray-200">
                                                        In Inventory
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block
                                            <?php
                                                if ($asset['status'] === 'Employee Assigned') echo 'bg-blue-50 text-blue-700 border border-green-200';
                                                elseif ($asset['status'] === 'Branch Assigned') echo 'bg-purple-50 text-purple-700 border border-purple-200';
                                                elseif ($asset['status'] === 'Department Assigned') echo 'bg-amber-50 text-amber-700 border border-amber-200';
                                                elseif ($asset['status'] === 'Under Maintenance') echo 'bg-orange-50 text-orange-700 border border-orange-200';
                                                elseif ($asset['status'] === 'Uncommitted') echo 'bg-red-50 text-red-700 border border-red-200';
                                                elseif ($asset['status'] === 'Available') echo 'bg-green-50 text-green-700 border border-green-200';
                                                else echo 'bg-slate-50 text-slate-700 border border-slate-200';
                                            ?>
                              ">
                                                    <?= htmlspecialchars($asset['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <article class="px-6 pb-6">
                <div class="border border-slate-200 rounded-lg p-8 bg-white text-center">
                    <i class="fa-solid fa-inbox text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-600 text-lg">No assets found in this category</p>
                </div>
            </article>
        <?php endif; ?>

    </section>
</main>

<?php require("views/partials/footer.php"); ?>