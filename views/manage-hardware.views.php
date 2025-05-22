<?php require("views/partials/head.php"); ?>




<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10 space-y-7">
    <?php require("views/banner.php"); ?>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 space-y-5">
      <div class="col-span-10 mb-2 md:mb-8 ">
        <h2 class="">Manage Inventory</h2>
      </div>
      <div class="col-span-10 ">
        <div class="flex text-sm">
          <button class="tab-button px-3 py-1 bg-red-600 text-white shadow active-tab cursor-pointer" data-tab="count">
            Inventory Count
          </button>
          <button class="tab-button px-4 py-2 bg-slate-200 text-slate-500 shadow cursor-pointer" data-tab="details">
            Detailed Entry
          </button>
        </div>
        <div class="tab-content block md:col-span-2 lg:col-span-10 text-sm" id="count">
          <form class="space-y-4 bg-white p-6 rounded shadow text-slate-800 " action="/manage-hardware" method="POST">
            <div>
              <?php if (!empty($categories)): ?>
                <select class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                  <?php foreach ($categories as $category): ?>
                    <option><?= htmlspecialchars(ucfirst($category['name'])) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <select class="mt-1 block w-full border border-slate-300 rounded px-3 py-2 bg-slate-200" disabled>
                  <option class="">No categories found</option>
                </select>
              <?php endif; ?>
            </div>

            <div>
              <input type="text" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 input-brand" placeholder="Brand" name="input-brand" />
              <span class="brand-error text-pink-600 text-sm hidden"></span>
            </div>

            <div>
              <input type="number" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 input-qty" placeholder="Quantity" name="input-qty" />
              <span class="qty-error text-pink-600 text-sm hidden"></span>
            </div>

            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 flex justify-self-end add-inventory-btn cursor-pointer">
              Add Inventory
            </button>
          </form>
        </div>

        <!-- Detailed Entry Form -->
        <div class="tab-content hidden text-sm" id="details">
          <form class="space-y-4 bg-white p-6 rounded shadow">
            <div>
              <select class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                <option>Select category</option>
              </select>
            </div>

            <div>
              <input type="text" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>

            <div>
              <input type="text" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>

            <div>
              <input type="text" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>

            <div>
              <select class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                <option>Available</option>
                <option>In Use</option>
                <option>Under Repair</option>
              </select>
            </div>

            <button type="submit" class=" flex justify-self-end bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 cursor-pointer ">
              Add Hardware
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
                  <th>#</th>
                  <th>name</th>
                  <th>Created At</th>
                  <th>Count</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $index => $category): ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars(ucfirst($category['name'])) ?></td>
                      <td><?= date("M d, Y", strtotime($category['created_at'])) ?></td>
                      <td>2</td>
                      <td class="relative">
                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-option"></i>
                        <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden options">
                          <ul class="text-xs text-slate-700 font-light ">
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer block w-full text-left edit-category-btn" data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">Edit</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer w-full text-left delete-category-btn" data-name="<?= htmlspecialchars($category['name']) ?>" data-id="<?= $category['id'] ?>">Delete</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100"><a href="/view" class="cursor-pointer w-full text-left">View</a></li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </article>
  </section>
</main>












<script src="assets/js/manage-hardware.js"></script>
<?php require("views/partials/footer.php"); ?>