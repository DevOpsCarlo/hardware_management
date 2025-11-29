<aside class="col-span-2 space-y-8 bg-[#101828] text-white dark:text-slate-400 shadow hidden md:block">
  <div class="flex flex-col justify-center items-center border-b-1 border-w-1/2 border-slate-100 py-11">
    <p class="text-slate-200 font-bold text-xl text-center w-3/4">Asset Inventory Management</p>
  </div>
  <nav class="flex flex-col space-y-10 px-4 text-base">
    <div class="flex flex-col space-y-1 font-bold sm:space-y-0 md:space-y-4 tracking-wide">
      <a href="/dashboard" class="sidebar-link <?= urlIs("/dashboard") ? 'active' : '' ?> flex items-center gap-x-2"> <i class="fa-solid fa-house"></i>
        <p>Dashboard</p>
      </a>

      <!-- Inventory -->
      <div class="">
        <!-- Menu toggle -->
        <!-- <div class="flex justify-between items-center toggle-submenu cursor-pointer sidebar-link">
          <a class="">Inventory</a>
          <i class="fa-solid fa-chevron-down mr-5 icon"></i>
        </div> -->
        <!-- Submenu -->
        <!-- <div class="flex flex-col px-3 gap-2 mt-4 <?= urlContains("/add-category") || urlContains("/manage-hardware") || urlContains("/manage-hardware.inventory-list") || urlContains("/hardwares") ? "" : "hidden" ?> submenu">
          <a href="/manage-hardware/add-category" class="sidebar-link <?= urlIs("/manage-hardware/add-inventory") || urlIs("/manage-hardware/add-category") || urlIs("/manage-hardware/add-asset") || urlIs("/manage-hardware/assign-asset") ? "active" : "" ?>">Manage Hardware</a>
          <a href="" class="sidebar-link <?= urlIs("/hardwares") ? "active" : "" ?>">Hardwares</a>
        </div> -->
        <div class="flex flex-col space-y-1 font-bold sm:space-y-0 md:space-y-4 tracking-wide <?= urlContains("/manage-hardware") ?> ">
          <a href="/manage-hardware/add-category" class="sidebar-link <?= urlContains("/manage-hardware") ? "active" : "" ?>  flex items-center gap-x-2">
            <i class="fa-solid fa-boxes-stacked"></i>
            <p>Manage Hardware</p>
          </a>
        </div>
      </div>

      <a href="/employee" class="sidebar-link <?= urlIs("/employee") ? "active" : "" ?> flex items-center gap-x-2">
        <i class="fa-solid fa-users"></i>
        <p>Employee</p>
      </a>
      <a href="/branch" class="sidebar-link <?= urlContains("/branch") ? "active" : "" ?> flex items-center gap-x-2">
        <i class="fa-solid fa-building"></i>
        <p>Branch</p>
      </a>


      <!-- Management -->
      <div class="">
        <!-- Menu toggle -->
        <!-- <div class="flex justify-between items-center toggle-submenu cursor-pointer sidebar-link">
          <a class="">Management</a>
          <i class="fa-solid fa-chevron-down mr-5 icon"></i>
        </div> -->
        <!-- Submenu -->
        <div class="flex flex-col px-3 gap-2 mt-4 <?= urlContains("/employee") || urlContains("/branch") ? "" : "hidden" ?> submenu">
          <!-- <a href="/employee" class="sidebar-link <?= urlIs("/employee") ? "active" : "" ?> ">Employee</a> -->
          <!-- <a href="/branch" class="sidebar-link <?= urlIs("/branch") ? "active" : "" ?>">Branch</a> -->
          <!-- <a href="" class="sidebar-link">Assignments</a>
          <a href="" class="sidebar-link">Maintenance Request</a>
          <a href="" class="sidebar-link">Reports Logs</a> -->
        </div>
      </div>

    </div>

    <!-- Logout -->
    <a href="/logout" class="sidebar-link font-bold tracking-wide">Logout</a>
  </nav>
</aside>