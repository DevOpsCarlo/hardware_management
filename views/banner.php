  <!-- <header class="flex justify-between items-center pr-4 shadow-sm">
    <div class="flex items-center ">
      <img src="/assets/images/toprank-logo.png" alt="" class="w-1/12">
      <div class="flex flex-col gap-0">
        <h4 class="text-slate-400">Company</h4>
        <h2 class="text-slate-600 font-bold">TOPRANK</h2>
      </div>
    </div>
    <div>
      <button class="flex items-center bg-red-500 hover:bg-red-600 text-white w-full *:text-sm py-2 px-3 cursor-pointer">
        <i class="fa-thin fa-plus"></i>
        <p class="whitespace-nowrap ml-2">Add Hardware</p>
      </button>
    </div>
  </header> -->

  <div class="flex items-center text-sm ">
    <a href="/manage-hardware/add-category" class="w-full px-3 py-2 cursor-pointer <?= urlIs("/manage-hardware/add-category") ? 'text-white shadow border-b-2 border-white' : 'text-red-400' ?>">
      Add Category
    </a>
    <a href="/manage-hardware/add-inventory" class="w-full px-3 py-2 cursor-pointer <?= urlIs("/manage-hardware/add-inventory") ? 'text-white shadow border-b-2 border-white' : 'text-red-400' ?>">
      Add Inventory
    </a>
    <a href="/manage-hardware/add-asset" class="w-full px-3 py-2 cursor-pointer <?= urlIs('/manage-hardware/add-asset') ? 'text-white shadow border-b-2 border-white' : 'text-red-400' ?>">
      Add Asset
    </a>
    <a href="/manage-hardware/assign-asset" class="w-full px-3 py-2 cursor-pointer <?= urlIs('/manage-hardware/assign-asset') ? 'text-white shadow border-b-2 border-white' : 'text-red-400' ?>">
      Assign Asset
    </a>
  </div>