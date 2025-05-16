<?php require("views/partials/head.php"); ?>


<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 space-y-7">
    <header class="flex justify-between items-center pr-4 shadow-sm">
      <div class="flex items-center ">
        <img src="assets/images/toprank-logo.png" alt="" class="w-1/12">
        <div class="flex flex-col gap-0">
          <h4 class="text-slate-400">Company</h4>
          <h2 class="text-slate-600 font-bold">TOPRANK</h2>
        </div>
      </div>
      <div>
        <button class="flex items-center bg-red-500 hover:bg-red-600 text-white w-full rounded-sm font-bold text-sm py-3 px-4 cursor-pointer">
          <i class="fa-solid fa-plus"></i>
          <p class="whitespace-nowrap ml-2">Add Hardware</p>
        </button>
      </div>
    </header>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <h2 class="col-span-1 md:col-span-2 lg:col-span-4">Hardware Management Dashboard</h2>
      <a href="">
        <div class="shadow-sm p-2 sm:p-4 md:p-6 lg:p-8 rounded-sm flex items-center gap-4 flex-col lg:flex-row">
          <div class="bg-slate-50 rounded-full p-4">
            <i class="fa-solid fa-server"></i>
          </div>
          <div>
            <h3 class="text-sm text-slate-500 whitespace-nowrap md:whitespace-normal">Total Hardware</h3>
            <p class="font-bold text-center lg:text-start">20</p>
          </div>
        </div>
      </a>

      <a href="">
        <div class="shadow-sm rounded-sm p-2 sm:p-4 md:p-6 lg:p-8 flex items-center gap-4 flex-col lg:flex-row">
          <div class="bg-slate-50 rounded-full p-4">
            <i class="fa-solid fa-chalkboard-user"></i>
          </div>
          <div>
            <h5 class="text-sm text-slate-500">Assigned</h5>
            <p class="font-bold text-center lg:text-start">12</p>
          </div>
          <div>
          </div>
        </div>
      </a>

      <a href="">
        <div class="shadow-sm rounded-sm p-2 sm:p-4 md:p-6 lg:p-8 flex items-center gap-4 flex-col lg:flex-row">
          <div class="bg-slate-50 rounded-full p-4">
            <i class="fa-solid fa-computer"></i>
          </div>
          <div>
            <h5 class="text-sm text-slate-500 whitespace-nowrap lg:whitespace-normal">Available Device</h5>
            <p class="font-bold text-center lg:text-start">60</p>
          </div>
        </div>
      </a>

      <a href="">
        <div class="shadow-sm rounded-sm p-2 sm:p-4 md:p-6 lg:p-8 flex items-center gap-4 flex-col lg:flex-row">
          <div class="bg-slate-50 rounded-full p-4">
            <i class="fa-solid fa-screwdriver-wrench"></i>
          </div>
          <div class="flex-col lg:flex-row">
            <h5 class="text-slate-500 text-sm whitespace-nowrap md:whitespace-normal">Under Maintenance</h5>
            <p class="font-bold text-center lg:text-start">4</p>
          </div>
        </div>
      </a>
    </article>

    <article class="my-6 px-6">
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






<?php require("views/partials/footer.php"); ?>