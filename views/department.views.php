<?php require("views/partials/head.php") ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
    <?php require("views/sidebar.php"); ?>

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


    <section class="col-span-12 md:col-span-10 mx-2">
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between my-4">
            <div class="flex items-center gap-4">
                <a href="/branch/<?= htmlspecialchars($branch['branch_name']) ?>" class="inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fa-solid fa-arrow-left ml-2"></i>
                    Back to <?= htmlspecialchars($branch['branch_name'] ?? ' ') ?> Branch
                </a>
            </div>

            <button class="text-white bg-red-600 border px-3 py-2 text-sm hover:bg-red-700 rounded-sm mr-2 cursor-pointer rounded-xl flex items-center gap-x-1" id="toggle-department-modal">
                <i class="fa-solid fa-plus"></i>
                <span class="font-bold text-xs">
                    New Department
                </span>
            </button>
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
                    <input type="hidden" name="branchId" value="<?= htmlspecialchars($departments['branch_id'] ?? 0) ?>">
                    <input type="hidden" name="departmentId" id="department-id" value="<?= $departments['id'] ?? 0 ?>">
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


        <article class="col-span-10 text-sm font-light px-2" id="department-table-section">


            <div>
                <h2 class="text-2xl font-extrabold text-red-700 my-3"><?= $branchName ?> Department</h2>

                <table id="branchDepartmentTable" class="display text-left w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">No.</th>
                            <th class="px-4 py-2">Department Name</th>
                            <th class="px-4 py-2">Department Head</th>
                            <th class="px-4 py-2">Total Assets</th>
                            <th class="px-4 py-2">Assigned</th>
                            <th class="px-4 py-2">Available</th>
                            <th class="px-4 py-2">In Repair</th>
                            <th class="px-4 py-2">Uncommitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($deparmentStats)): ?>
                            <?php $count = 1; ?>
                            <?php foreach ($deparmentStats as $department): ?>
                                <tr class="border-b text-xs font-light text-left hover:bg-slate-100 transition duration-150 cursor-pointer"
                                    data-branch-name="<?= htmlspecialchars($branch['branch_name']) ?>"
                                    data-department-name="<?= htmlspecialchars($department['department_name']) ?>"
                                    data-department-id="<?= $department['id'] ?>">
                                    <td><?= $count++ ?></td>

                                    <td>
                                        <?= htmlspecialchars_decode($department['department_name'] ?? 'N/A') ?>
                                    </td>
                                    <td><?= htmlspecialchars($department['department_head'] ?? '-') ?></td>

                                    <!-- TOTAL ASSETS -->
                                    <td class="px-4 py-2">
                                        <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $department['total_assets'] ?? 0 ?>
                                        </span>
                                    </td>

                                    <!-- ASSIGNED ASSETS -->
                                    <td class="px-4 py-2">
                                        <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $department['assigned_count'] ?? 0 ?>
                                        </span>
                                    </td>

                                    <!-- UNASSIGNED ASSETS -->
                                    <td class="px-4 py-2">
                                        <span class="inline-block bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $department['unassigned_count'] ?? 0 ?>
                                        </span>
                                    </td>

                                    <!-- IN REPAIR -->
                                    <td class="px-4 py-2">
                                        <span class="inline-block bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $department['in_repair_count'] ?? 0 ?>
                                        </span>
                                    </td>

                                    <!-- Uncommited -->
                                    <td class="px-4 py-2">
                                        <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $department['uncommitted_count'] ?? 0 ?>
                                        </span>
                                    </td>


                                    <td class="relative">
                                        <i
                                            class="fa-solid fa-ellipsis-vertical cursor-pointer text-slate-400 hover:text-slate-600 department-menu-trigger"
                                            data-department-id="<?= $department['id'] ?>">
                                        </i>

                                        <div
                                            class="absolute top-6 right-10 w-32 bg-white border rounded shadow-lg z-10 hidden department-menu"
                                            id="department-menu-<?= $department['id'] ?>">

                                            <ul class="text-xs text-slate-700 font-light">

                                                <li class="px-4 py-2 hover:bg-slate-100 border-b">
                                                    <a href="/branch/<?= urlencode($branch['branch_name']) ?>/<?= urlencode($department['department_name']) ?>">
                                                        View
                                                    </a>
                                                </li>

                                                <li class="px-4 py-2 hover:bg-slate-100 border-b">
                                                    <button
                                                        class="w-full text-left edit-department-btn"
                                                        data-department-id="<?= $department['id'] ?>"
                                                        data-department-name="<?= htmlspecialchars($department['department_name']) ?>"
                                                        data-department-head=" <?= htmlspecialchars($department['department_head'] ?? '') ?>">
                                                        Edit
                                                    </button>
                                                </li>

                                                <li class="px-4 py-2 hover:bg-slate-100">
                                                    <button
                                                        class="w-full text-left delete-department-btn"
                                                        data-department-id="<?= $department['id'] ?>"
                                                        data-department-name="<?= htmlspecialchars($department['department_name']) ?>">
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
        </article>




    </section>


</main>

<script src="/assets/js/department.js"></script>
<?php require("views/partials/footer.php") ?>