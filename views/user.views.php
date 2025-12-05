<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
    <?php require("views/sidebar.php"); ?>
    <section class="col-span-12 md:col-span-10">
        <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5 bg-red-800">
            <?php require("views/banner.php") ?>
        </article>

        <div class="flex items-center justify-between col-span-1 md:col-span-2 lg:col-span-10 my-4">
            <div>
                <h2 class="text-2xl font-extrabold text-red-700 ml-6">User Management</h2>
            </div>
            <button class="flex items-center text-white bg-red-600 border px-3 py-2 text-sm hover:bg-red-700 hover:text-white rounded-xl gap-x-1 mr-6 cursor-pointer" id="add-user-btn">
                <i class="fa-solid fa-plus"></i>
                <span class="font-bold text-xs">New User</span>
            </button>
        </div>

        <!-- Success Message -->
        <?php if (!empty($_SESSION['user_success'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    text: '<?= htmlspecialchars($_SESSION['user_success']) ?> user created/updated successfully!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    position: 'top-end',
                    toast: true
                });
            </script>
            <?php unset($_SESSION['user_success']); ?>
        <?php endif; ?>

        <!-- User Modal -->
        <?php $errorModalVisible = !empty($errorMessage); ?>
        <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 <?= $errorModalVisible ? '' : 'hidden' ?>" id="user-modal">
            <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white">
                <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
                    <div>
                        <i class="fa-solid fa-user text-red-500 rounded-full bg-red-50 p-4"></i>
                    </div>
                    <h2 class="text-slate-800 font-bold text-2xl"><?= $editMode ? 'Edit User' : 'Add New User' ?></h2>
                </div>

                <form method="POST" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 text-sm font-medium" id="user-form">
                    <div>
                        <label for="inputUsername" class="block text-sm font-light text-slate-800">Username <span class="text-red-600">*</span></label>
                        <input type="text" placeholder="Enter username" class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3" id="input-username" name="inputUsername" value="<?= htmlspecialchars($formData['inputUsername'] ?? '') ?>">
                        <?php if ($errorModalVisible): ?>
                            <p class="text-sm text-pink-600 error-message"><?= htmlspecialchars($errorMessage) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="inputPassword" class="block text-sm font-light text-slate-800">Password <span class="text-red-600">*</span></label>
                        <input type="password" placeholder="Enter password" class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3" id="input-password" name="inputPassword">
                    </div>

                    <div>
                        <label for="inputConfirmPassword" class="block text-sm font-light text-slate-800">Confirm Password <span class="text-red-600">*</span></label>
                        <input type="password" placeholder="Confirm password" class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3" id="input-confirm-password" name="inputConfirmPassword">
                    </div>

                    <div>
                        <label for="inputRole" class="block text-sm font-light text-slate-800">Role <span class="text-red-600">*</span></label>
                        <select class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3" id="input-role" name="inputRole">
                            <option value="user" <?= ($formData['inputRole'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($formData['inputRole'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <input type="hidden" name="userId" id="user-id" value="<?= htmlspecialchars($formData['userId'] ?? 0) ?>">

                    <div class="flex items-center gap-4 pt-4">
                        <button class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer" id="toggle-close" type="button">Cancel</button>
                        <button type="submit" id="submit-btn" class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 cursor-pointer">
                            <?= $editMode ? 'Update User' : 'Create User' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <article class="col-span-10 text-sm mt-6 px-6">
            <div>
                <?php if (empty($users)): ?>
                    <div class="col-span-full text-center py-12">
                        <div class="text-slate-400 mb-4">
                            <i class="fa-solid fa-users text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-slate-600 mb-2">No Users Yet</h3>
                        <p class="text-slate-500 mb-4">Create your first user to get started.</p>
                    </div>
                <?php else: ?>
                    <table class="row-border text-left w-full hover">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Username</th>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Role</th>
                                <th class="px-3 py-2 text-left text-xs font-bold text-slate-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-4 py-3 text-xs text-slate-900 font-normal"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="px-4 py-3 text-xs text-slate-900 font-normal">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="relative">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-option"></i>
                                        <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow z-10 hidden options">
                                            <ul class="text-xs text-slate-700 font-light">
                                                <li class="px-4 py-2 hover:bg-slate-100 border-b"><button class="cursor-pointer block w-full text-left edit-user-btn" data-id="<?= $user['id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>" data-role="<?= $user['role'] ?>">Edit</button></li>
                                                <li class="px-4 py-2 hover:bg-slate-100"><button class="cursor-pointer w-full text-left delete-user-btn" data-id="<?= $user['id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>">Delete</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<script src="/assets/js/user-management.js"></script>

<?php require("views/partials/footer.php"); ?>