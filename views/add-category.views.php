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
    <?php require("views/banner.php"); ?>
    <article class="mt-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="flex items-center justify-between col-span-1 md:col-span-2 lg:col-span-10 mb-8">
        <h2 class="">Add Categories</h2>
        <button class="flex items-center text-sm bg-red-500 py-1 px-3 text-white gap-2 hover:bg-red-600 cursor-pointer font-light" id="add-category-btn">
          <i class="fa-thin fa-plus"></i>
          <span class="font-light">Add Category</span>
        </button>
      </div>

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
        <div class="w-full max-w-lg bg-slate-100 col-span-4 mx-auto rounded-sm p-7 shadow-lg" id="category-modal-box">
          <h2 class="text-slate-800 font-bold text-2xl mb-4 modal-title"> <?= $editMode ? 'Edit Category' : 'Add Category' ?></h2>

          <?php if ($errorModalVisible): ?>
            <p class="text-sm text-pink-600 mb-4 error-message"><?= htmlspecialchars($errorMessage) ?></p>
          <?php else: ?>
            <p class="text-sm text-pink-600 mb-4 hidden error-message"></p>
          <?php endif; ?>
          <form method="POST" class="flex flex-col gap-4" id="category-form">
            <input
              type="text"
              placeholder="Category Name"
              class="w-full border-2 rounded-sm text-sm bg-slate-100 border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-skyblue-500 focus:border-transparent transition-all " id="input-category-name" name="inputCategoryName" value="<?= htmlspecialchars($category['name'] ?? '') ?>">
            <input type="hidden" name="categoryId" id="category-id" value="<?= htmlspecialchars($category['id'] ?? 0) ?>">
            <button
              type="submit"
              id="add-btn"
              class="w-full bg-red-500 text-white rounded-sm text-base font-bold py-3 hover:bg-red-600 active:bg-red-700 transition-all focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 cursor-pointer">
              <?= $editMode ? 'Update Category' : 'Add Category' ?>
            </button>
          </form>
        </div>
      </div>


      <!-- CATEGORY TABLE  -->
      <article class="col-span-10 text-sm mt-0">
        <div>
          <h4 class="text-slate-700 font-semibold text-base">Category Lists:</h4>
          <div>
            <table id="categoryTable" class="display font-light">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Category</th>
                  <th>Created At</th>
                  <th>Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $index => $category): ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars(ucfirst($category['category_name'])) ?></td>
                      <td><?= date("M d, Y", strtotime($category['created_at'])) ?></td>
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
                <?php else: ?>
                  <tr class="col-span-5">No categories found.</tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </article>
  </section>
</main>








<script src="assets/js/add-category.js"></script>

<?php require("views/partials/footer.php") ?>