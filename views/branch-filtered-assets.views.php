<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
    <?php require("views/sidebar.php"); ?>

    <section class="col-span-12 md:col-span-10 mx-2">
        <!-- Success Message -->
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
                <a href="/branch/<?= urlencode($branch['branch_name']) ?>"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Branch
                </a>
            </div>
        </div>

        <!-- Page Title -->
        <article class="col-span-10 text-sm font-light px-2 pb-5">
            <div>
                <h2 class="text-2xl font-extrabold text-red-700 my-3">
                    <?= htmlspecialchars($branch['branch_name']) ?> - <?= htmlspecialchars($pageTitle) ?>
                </h2>

                <!-- Quick Stats for This Filter -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-slate-600">
                        <i class="fa-solid fa-list mr-2"></i>
                        <strong>Total <?= htmlspecialchars($pageTitle) ?>:</strong>
                        <span class="text-2xl font-bold text-blue-700 ml-2"><?= count($assets) ?></span>
                    </p>
                </div>

                <!-- Assets Table -->
                <div class="overflow-x-auto">
                    <?php if (!empty($assets)): ?>
                        <table id="filteredAssetTable" class="display text-left w-full">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Image</th>
                                    <th>Model</th>
                                    <th>Category</th>
                                    <th>Asset No.</th>
                                    <th>Serial No.</th>
                                    <th>Department</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Assigned Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-light">
                                <?php $itemCounter = 1; ?>
                                <?php foreach ($assets as $asset):
                                    // Fetch asset assignment path
                                    $assetPath = getAssetAssignmentPath($pdo, $asset['asset_id']);
                                ?>
                                    <tr class="text-xs font-light text-left border-b hover:bg-slate-50">
                                        <td class="py-3 px-2"><?= $itemCounter++ ?></td>
                                        <td class="py-3 px-2">
                                            <img
                                                src="/<?= !empty($asset['photo']) ? htmlspecialchars($asset['photo']) : 'uploads/default-photo/laptop-charger.jpg' ?>"
                                                alt="No image available"
                                                class="w-20 h-20 object-cover rounded">
                                        </td>
                                        <td class="py-3 px-2">
                                            <?= htmlspecialchars(ucfirst($asset['manufacturer'] ?? '-')) ?>
                                            /
                                            <?= htmlspecialchars(ucfirst($asset['model'] ?? '-')) ?>
                                        </td>
                                        <td class="py-3 px-2">
                                            <?= htmlspecialchars($asset['category_name'] ?? '-') ?>
                                        </td>
                                        <td class="py-3 px-2">
                                            <?= htmlspecialchars($asset['asset_number'] ?? '-') ?>
                                        </td>
                                        <td class="py-3 px-2">
                                            <?= htmlspecialchars($asset['serial_number'] ?? '-') ?>
                                        </td>

                                        <!-- Department Column -->
                                        <td class="py-3 px-2">
                                            <?php if (!empty($assetPath['department_name'])): ?>
                                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                                    <?= htmlspecialchars($assetPath['department_name']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Assigned To Column -->
                                        <td class="py-3 px-2">
                                            <?php if (!empty($assetPath['employee_name'])): ?>
                                                <?= htmlspecialchars($assetPath['employee_name']) ?>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <?php
                                        $statusClassMap = [
                                            'Available' => 'text-emerald-500 bg-emerald-100',
                                            'Branch Assigned' => 'text-emerald-500 bg-emerald-100',
                                            'Department Assigned' => 'text-amber-700 bg-amber-100',
                                            'Employee Assigned' => 'text-blue-500 bg-blue-100',
                                            'Assigned' => 'text-blue-500 bg-blue-100',
                                            'Under Maintenance' => 'text-gray-500 bg-gray-100',
                                            'Defective' => 'text-red-500 bg-red-100'
                                        ];
                                        $currentStatus = $asset['status'] ?? '-';
                                        $statusClass = $statusClassMap[$currentStatus] ?? 'text-gray-500 bg-gray-100';
                                        ?>

                                        <!-- Status Column -->
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?= $statusClass ?>">
                                                <?php
                                                $statusDisplay = [
                                                    'Available' => 'Available',
                                                    'Branch Assigned' => 'Available',
                                                    'Department Assigned' => 'Dept Level',
                                                    'Employee Assigned' => 'Assigned',
                                                    'Under Maintenance' => 'Under Maintenance',
                                                    'Defective' => 'Defective'
                                                ];
                                                echo htmlspecialchars($statusDisplay[$currentStatus] ?? $currentStatus);
                                                ?>
                                            </span>
                                        </td>

                                        <td class="py-3 px-2">
                                            <?= htmlspecialchars($asset['conditions'] ?? '-') ?>
                                        </td>

                                        <!-- Assigned Date -->
                                        <td class="py-3 px-2 text-xs text-slate-600">
                                            <?= !empty($asset['assigned_at']) ? date('M d, Y', strtotime($asset['assigned_at'])) : '-' ?>
                                        </td>

                                        <!-- Action Column -->
                                        <td class="py-3 px-2 relative">
                                            <div class="relative group">
                                                <i class="fa-solid fa-ellipsis-vertical cursor-pointer text-slate-400 hover:text-slate-600"></i>

                                                <div class="absolute top-6 right-0 w-48 bg-white border rounded shadow-lg z-10 hidden group-hover:block">
                                                    <ul class="text-xs text-slate-700 font-light">
                                                        <li class="border-b">
                                                            <button
                                                                class="cursor-pointer block w-full text-left px-4 py-2 hover:bg-slate-100 view-asset-btn"
                                                                data-asset-id="<?= htmlspecialchars($asset['asset_id']) ?>">
                                                                <i class="fa-solid fa-eye mr-2"></i>View Details
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="text-slate-400 mb-4">
                                <i class="fa-solid fa-inbox text-6xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-600 mb-2">No Assets Found</h3>
                            <p class="text-slate-500">There are no <?= strtolower($pageTitle) ?> for this branch.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>

    </section>
</main>

<script src="/assets/js/branch-detail.js"></script>
<?php require("views/partials/footer.php") ?>