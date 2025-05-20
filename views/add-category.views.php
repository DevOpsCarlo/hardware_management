<?php require("views/partials/head.php"); ?>



<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 space-y-7">
    <?php require("views/banner.php"); ?>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="flex items-center justify-between col-span-1 md:col-span-2 lg:col-span-10 mb-8">
        <h2 class="">Add Categories</h2>
        <button class="flex items-center text-sm bg-red-500 py-1 px-3 text-white gap-2 hover:bg-red-600 cursor-pointer font-light" id="add-category-btn">
          <i class="fa-thin fa-plus"></i>
          <span class="font-light">Add Category</span>
        </button>
      </div>

      <!-- Success Message Category added  -->
      <?php if (isset($_SESSION['category_added'])): ?>
        <script>
          Toastify({
            text: "✅ <?= htmlspecialchars($_SESSION['category_added']) ?> category added successfully!",
            duration: 1000,
            gravity: "top",
            position: "right",
            backgroundColor: "#22c55e",
            color: "#fff",
            borderRadius: "4px",
            stopOnFocus: true
          }).showToast();
        </script>
        <?php unset($_SESSION['category_added']); ?>
      <?php endif; ?>

      <!-- Add category Modal -->
      <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 <?= !empty($errorMessage) ? "" : "hidden"  ?> " id="add-category-modal">
        <div class="w-full max-w-lg bg-slate-100 col-span-4 mx-auto rounded-sm p-7 shadow-lg" id="category-modal-box">
          <h2 class="text-slate-800 font-bold text-2xl mb-4">Add Category</h2>

          <?php if (!empty($errorMessage)): ?>
            <p class="text-sm text-pink-600 mb-4 error-message"><?= htmlspecialchars($errorMessage) ?></p>
          <?php else: ?>
            <p class="text-sm text-pink-600 mb-4 hidden error-message"></p>
          <?php endif; ?>
          <form action="/add-category" method="POST" class="flex flex-col gap-4">
            <input
              type="text"
              placeholder="Category Name"
              class="w-full border-2 rounded-sm text-sm bg-slate-100 border-slate-300 p-3 focus:outline-none focus:ring-2 focus:ring-skyblue-500 focus:border-transparent transition-all " id="input-category-name" name="inputCategoryName">
            <button
              type="submit"
              id="add-btn"
              class="w-full bg-red-500 text-white rounded-sm text-base font-bold py-3 hover:bg-red-600 active:bg-red-700 transition-all focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 cursor-pointer">
              Add Category
            </button>
          </form>
        </div>
      </div>

      <article class="col-span-10 text-sm mt-8">
        <div>
          <h4 class="text-slate-700 font-semibold text-base">Category Lists:</h4>
          <div>
            <table id="myTable" class="display">
              <thead>
                <tr>
                  <th>Hardware</th>
                  <th>Assigned To</th>
                  <th>Department</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </article>
  </section>
</main>









<?php require("views/partials/footer.php") ?>