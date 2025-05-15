<?php require("views/partials/head.php"); ?>


<main class="grid grid-cols-12 min-h-screen">
  <aside class="col-span-2 space-y-8 bg-slate-900 text-white dark:text-slate-400 dark:bg-slate-900 hidden md:block">
    <div class="flex flex-col justify-center items-center border-b-1 border-w-1/2 border-slate-100">
      <img src="assets/images/toprank-logo.png" alt="" class="w-3/4">
      <p class="mb-4 text-white font-semibold ">Hardware Management</p>
    </div>
    <nav class="flex flex-col space-y-10 px-4 text-base">
      <div class="flex flex-col space-y-1 font-bold sm:space-y-0 md:space-y-4 tracking-wide">
        <a href="" class="sidebar-link">Dashboard</a>
        <a href="" class="sidebar-link">Hardware</a>
        <a href="" class="sidebar-link">Users</a>
        <a href="" class="sidebar-link">Assignments</a>
        <a href="" class="sidebar-link">Maintenance Request</a>
        <a href="" class="sidebar-link">Reports Logs</a>
      </div>
      <a href="/logout" class="sidebar-link font-bold tracking-wide">Logout</a>
    </nav>
  </aside>
  <section class="col-span-10"></section>
</main>






<?php require("views/partials/footer.php"); ?>