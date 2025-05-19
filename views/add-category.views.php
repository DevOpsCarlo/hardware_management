<?php require("views/partials/head.php"); ?>




<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 space-y-7">
    <?php require("views/banner.php"); ?>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="flex items-center justify-between col-span-1 md:col-span-2 lg:col-span-10">
        <h2 class="">Add Categories</h2>
        <button class="flex text-sm items-center bg-red-500 rounded-sm py-2 px-3 text-white gap-2 hover:bg-red-600 cursor-pointer ">
          <i class="fa-solid fa-plus"></i>
          <span>Add Category</span>
        </button>
      </div>


      <article class="col-span-10 text-sm">
        <div>
          <h4 class="text-slate-700 font-semibold">Recent Assignments</h4>
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