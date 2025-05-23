<?php require("views/partials/head.php"); ?>




<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <?php require("views/banner.php"); ?>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 space-y-5">
      <!-- <div class="col-span-10 mb-2 md:mb-8 ">
        <h2 class="">Manage Inventory</h2>
      </div> -->
      <div class="col-span-10 ">
        <div class="flex text-sm">
          <button class="tab-button px-3 py-1 bg-red-600 text-white shadow active-tab cursor-pointer" data-tab="count">
            Add Inventory
          </button>
          <button class="tab-button px-4 py-2 bg-slate-200 text-slate-500 shadow cursor-pointer" data-tab="details">
            Inventory List
          </button>
        </div>
        <?php
        // Check for success or error messages in the session
        $successTypes = [
          'inventory_added' => 'added',
          'inventory_updated' => 'updated'
        ];

        foreach ($successTypes as $sessionKey => $action) {
          if (!empty($_SESSION[$sessionKey])):
            $inventoryMessage = htmlspecialchars($_SESSION[$sessionKey], ENT_QUOTES, 'UTF-8');
        ?>
            <script>
              Swal.fire({
                icon: 'success',
                text: '<?= $inventoryMessage ?>',
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

        if (!empty($_SESSION['inventory_error'])):
          $inventoryErrorMessage = htmlspecialchars($_SESSION['inventory_error'], ENT_QUOTES, 'UTF-8');
          ?>
          <script>
            Swal.fire({
              icon: 'error',
              text: '<?= $inventoryErrorMessage ?>',
              showConfirmButton: true,
              position: 'top-end'
            });
          </script>
        <?php
          unset($_SESSION['inventory_error']);
        endif;
        ?>
        <!-- ADD INVENTORY FORM -->
        <div class="tab-content block md:col-span-2 lg:col-span-10 text-sm" id="count">
          <form class="space-y-4 bg-white p-6 rounded shadow text-slate-800 grid grid-cols-2 gap-2 " action="/manage-hardware" method="POST">
            <div>
              <?php if (!empty($categories)): ?>
                <select class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" name="category_id">
                  <option value="" disabled selected>Select Category</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars(ucfirst($category['name'])) ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="category-error text-pink-600 text-sm hidden font-light"></span>

              <?php else: ?>
                <select class="mt-1 block w-full border border-slate-300 rounded px-3 py-2 bg-slate-200" disabled>
                  <option class="">No categories found</option>
                </select>
              <?php endif; ?>
            </div>

            <div>
              <input type="text" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 input-manufacturer" placeholder="Manufacturer" name="input-manufacturer" />
              <span class="brand-error text-pink-600 text-sm hidden font-light"></span>
            </div>

            <div>
              <input type="number" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 input-qty" placeholder="Quantity" name="input-qty" />
              <span class="qty-error text-pink-600 text-sm hidden font-light"></span>
            </div>

            <div class="flex flex-col items-end">
              <button type="submit" class="bg-red-500 w-1/4 px-3 py-2 mt-1 text-white rounded hover:bg-red-600 add-inventory-btn cursor-pointer ">
                Add Inventory
              </button>
            </div>

          </form>
        </div>

        <!-- Detailed Entry Form -->
        <!-- <div class="tab-content hidden text-sm" id="details">
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
        </div> -->
      </div>

      <!-- INVENTORY TABLE LIST -->
      <article class="col-span-10 text-sm mt-4 font-light" id="inventory-list-table">
        <div>
          <h4 class="text-slate-700 font-semibold text-base">Added inventory</h4>
          <div>
            <table id="inventoryTable" class="display">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Manufacturer</th>
                  <th>Category</th>
                  <th>Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($inventories)): ?>
                  <?php foreach ($inventories as $index => $inventory): ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'])) ?> </td>
                      <td><?= htmlspecialchars(ucfirst($inventory['category_name'])) ?></td>
                      <td><?= htmlspecialchars($inventory['quantity']) ?></td>
                      <td class="relative">
                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                        <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                          <ul class="text-xs text-slate-700 font-light ">
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer block w-full text-left edit-inventory-btn" data-id="<?= $inventory['inventory_id'] ?>" data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>">Edit</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer w-full text-left delete-inventory-btn" data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>" data-id="<?= $inventory['inventory_id'] ?>">Delete</button></li>
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

      <!-- All Inventory List -->
      <article class="col-span-10 text-sm hidden font-light" id="detailed-list-table">
        <div>
          <h4 class="text-slate-700 font-semibold text-base">All inventory list</h4>
          <div>
            <table id="detailedTable" class="display">
              <thead>
                <tr>
                  <th>Item #</th>
                  <th>Manufacturer</th>
                  <th>Category</th>
                  <th>Model</th>
                  <th>Serial #</th>
                  <th>Asset #</th>
                  <th>Assigned to</th>
                  <th>Condition</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="">
                <?php
                $itemCounter = 1;
                foreach ($inventories as $inventory): ?>
                  <?php
                  $manufacturer = $inventory['manufacturer'];
                  $quantity = $inventory['quantity'];
                  $categoryName = $inventory['category_name'];

                  for ($i = 1; $i < $quantity; $i++):
                  ?>
                    <tr class="">
                      <td><?= $itemCounter++ ?></td>
                      <td><?= ucfirst($manufacturer) ?></td>
                      <td><?= ucfirst($categoryName) ?></td>
                      <th>Empty</th>
                      <th>Empty</th>
                      <th>Empty</th>
                      <th>Empty</th>
                      <th>Empty</th>
                      <td class="relative">
                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-option"></i>
                        <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden options">
                          <ul class="text-xs text-slate-700 font-light ">
                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer block w-full text-left edit-inventory-btn" data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>" item-number="<?= $itemCounter - 1 ?>">Assign asset</button></li>

                            <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer w-full text-left delete-inventory-btn" data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>" item-number="<?= $itemCounter - 1 ?>">Delete</button></li>
                            <li class="px-4 py-2 hover:bg-slate-100"><a href="/view" class="cursor-pointer w-full text-left">View</a></li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </article>
  </section>
</main>












<script src="assets/js/manage-hardware.js"></script>
<?php require("views/partials/footer.php"); ?>