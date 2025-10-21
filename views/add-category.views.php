<?php require("views/partials/head.php"); ?>

<?php
$errorMessage = $_SESSION['category_error'] ?? '';
$category = $_SESSION['category_form_data'] ?? [];
$editMode = $_SESSION['category_edit_mode'] ?? false;

unset($_SESSION['category_error'], $_SESSION['category_form_data'], $_SESSION['category_edit_mode']);
?>


<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 ">
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5 bg-red-800">
      <?php require("views/banner.php") ?>

    </article>
    <div class="flex items-center justify-between col-span-1 md:col-span-2 lg:col-span-10 my-4">
      <div>
        <h2 class="text-2xl font-extrabold text-red-700 ml-6">Category List</h2>
      </div>
      <button class="flex items-center text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-6 cursor-pointer" id="add-category-btn">
        + Add Category
      </button>
    </div>
    <article class="mt-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">


      <?php
      $successTypes = ['category_added' => 'added', 'category_updated' => 'updated'];

      foreach ($successTypes as $sessionKey => $action) {
        if (!empty($_SESSION[$sessionKey])):
          $categoryName = htmlspecialchars(ucfirst($_SESSION[$sessionKey]), ENT_QUOTES, 'UTF-8');
      ?>
          <script>
            Swal.fire({
              icon: 'success',
              text: '<?= $categoryName ?> category <?= $action ?> successfully!',
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
              position: 'top-end',
              toast: true
            });
          </script>
      <?php
          unset($_SESSION[$sessionKey]);
        endif;
      }
      ?>
      <!-- Category Modal -->
      <?php $errorModalVisible = !empty($errorMessage); ?>
      <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 <?= $errorModalVisible ? '' : 'hidden'  ?> " id="category-modal">
        <div class="max-w-xl w-full mx-auto rounded-sm shadow-lg p-4 bg-white" id="category-modal-box">
          <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
            <div class="">
              <i class="fa-solid fa-tag text-red-500 rounded-full bg-red-50 p-4"></i>
            </div>
            <h2 class="ext-slate-800 font-bold text-2xl modal-title"> <?= $editMode ? 'Edit Category' : 'Add Category' ?></h2>
          </div>

          <form method="POST" class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-1 gap-2 form text-sm font-medium" id="category-form">

            <div>
              <label for="inputCategoryName" class="block text-sm font-light text-slate-800">Category Name <span class="text-red-600">*</span></label>
              <div class="relative">
                <input
                  type="text"
                  placeholder="Category Name"
                  class="mt-1 block w-full border border-gray-300 rounded-lg px-4 py-3 " id="input-category-name" name="inputCategoryName" value="<?= htmlspecialchars($category['name'] ?? '') ?>">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i class="fa-regular fa-clipboard"></i>
                </div>
              </div>

              <?php if ($errorModalVisible): ?>
                <p class="text-sm text-pink-600 error-message"><?= htmlspecialchars($errorMessage) ?></p>
              <?php else: ?>
                <p class="text-sm text-pink-600 hidden error-message"></p>
              <?php endif; ?>
            </div>

            <input type="hidden" name="categoryId" id="category-id" value="<?= htmlspecialchars($category['id'] ?? 0) ?>">
            <div class="flex items-center gap-4 pt-4">
              <button
                class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
                id="toggle-close"
                type="button">
                Cancel
              </button>
              <button
                type="submit"
                id="add-btn"
                class="bg-red-600 block text-sm font-bold w-full px-4 py-2 text-white rounded-sm hover:bg-red-700 cursor-pointer">
                <?= $editMode ? 'Update Category' : 'Add Category' ?>
              </button>
            </div>

          </form>
        </div>
      </div>


      <!-- CATEGORY TABLE  -->
      <article class="col-span-10 text-sm mt-0">
        <div>
          <div>
            <?php if (empty($categories)): ?>
              <div class="col-span-full text-center py-12">
                <div class="text-slate-400 mb-4">
                  <i class="fa-solid fa-list text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-600 mb-2">No Category Yet</h3>
                <p class="text-slate-500 mb-4">Start by adding your first category.</p>

              </div>
            <?php else: ?>
              <table id="categoryTable" class="display font-light">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($categories as $index => $category): ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars(ucfirst($category['category_name'])) ?></td>
                      <td><?= $category['total_quantity'] > 0 ? $category['total_quantity'] : 0 ?></td>
                      <td class="relative">
                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-option"></i>
                        <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden options">
                          <ul class="text-xs text-slate-700 font-light ">
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer block w-full text-left edit-category-btn" data-id="<?= $category['category_id'] ?>" data-name="<?= htmlspecialchars($category['category_name']) ?>">Edit</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer w-full text-left delete-category-btn" data-name="<?= htmlspecialchars($category['category_name']) ?>" data-id="<?= $category['category_id'] ?>">Delete</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100"><a href="/view" class="cursor-pointer w-full text-left">View</a></li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

        </div>
      </article>
  </section>
</main>








<script src="/assets/js/add-category.js"></script>

<?php require("views/partials/footer.php") ?>