<aside class="col-span-2 space-y-8 bg-slate-900 text-white dark:text-slate-400 dark:bg-slate-900 hidden md:block">
  <div class="flex flex-col justify-center items-center border-b-1 border-w-1/2 border-slate-100 py-11">
    <!-- <img src="assets/images/toprank-logo.png" alt="" class="w-3/4"> -->
    <p class="text-slate-200 font-semibold text-2xl text-center w-full">Hardware Inventory Management</p>
  </div>
  <nav class="flex flex-col space-y-10 px-4 text-base">
    <div class="flex flex-col space-y-1 font-bold sm:space-y-0 md:space-y-4 tracking-wide">
      <a href="" class="sidebar-link">Dashboard</a>

      <!-- Inventory -->
      <div class="">
        <!-- Menu toggle -->
        <div class="flex justify-between items-center toggle-submenu cursor-pointer sidebar-link">
          <a class="">Inventory</a>
          <i class="fa-solid fa-chevron-down mr-5 icon"></i>
        </div>
        <!-- Submenu -->
        <div class="flex flex-col px-3 gap-2 mt-4 <?= urlContains("/add-category") || urlContains("/add-hardware") || urlContains("/hardwares") ? "" : "hidden" ?> submenu">
          <a href="/add-category" class="sidebar-link">Add Category</a>
          <a href="" class="sidebar-link">Add Hardware</a>
          <a href="" class="sidebar-link">Hardwares</a>
        </div>
      </div>

      <!-- Management -->
      <div class="">
        <div class="flex justify-between items-center toggle-submenu cursor-pointer sidebar-link">
          <a class="">Management</a>
          <i class="fa-solid fa-chevron-down mr-5 icon"></i>
        </div>
        <div class="flex flex-col px-3 gap-2 mt-4 hidden submenu">
          <a href="" class="sidebar-link">Users</a>
          <a href="" class="sidebar-link">Assignments</a>
          <a href="" class="sidebar-link">Maintenance Request</a>
          <a href="" class="sidebar-link">Reports Logs</a>
        </div>
      </div>

    </div>

    <!-- Logout -->
    <a href="/logout" class="sidebar-link font-bold tracking-wide">Logout</a>
  </nav>
</aside>