<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <!-- require("views/banner.php");  -->
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-6 space-y-5  bg-red-800">
      <!-- <div class="col-span-10 "> -->
      <?php require("views/banner.php") ?>
    </article>
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
          position: 'center'
        });
      </script>
    <?php
      unset($_SESSION['inventory_error']);
    endif;
    ?>

    <!-- ADD INVENTORY BUTTON -->
    <div class="flex justify-between my-4">
      <div>
        <h2 class="text-2xl font-extrabold text-red-700 ml-2">Inventory List</h2>
      </div>
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-6 cursor-pointer" id="toggle-add-inventory-form"> + Add inventory</button>
    </div>

    <!-- ADD INVENTORY FORM -->

    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="inventory-form-modal">
      <div class="w-full max-w-4xl bg-white col-span-4 mx-auto rounded-sm p-4 shadow-lg" id="inventory-form">
        <!-- Dynamic Title -->
        <div class="flex items-center gap-4 border-b-2 border-slate-100 pb-5">
          <div class="">
            <i class="fa-solid fa-cubes text-red-500 rounded-full bg-red-50 p-4"></i>
          </div>
          <h2 class="text-slate-800 font-bold text-2xl" id="form-title">Add Inventory</h2>
        </div>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-2 form text-sm font-medium"
          action="/manage-hardware/add-inventory" method="POST" enctype="multipart/form-data" id="inventory-form-element">

          <!-- Hidden field for inventory ID (for edit mode) -->
          <input type="hidden" name="inventory_id" id="inventory-id" value="<?= htmlspecialchars($inventory['id']) ?>">

          <div class="col-span-10 flex gap-4">
            <!-- Category -->
            <div class="w-full">
              <label for="category-id" class="block">Category</label>
              <?php if (!empty($categories)): ?>
                <select class="mt-1 block w-full border border-gray-300 rounded p-2"
                  name="category_id" id="category-id">
                  <option value="" disabled selected>Select Category</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars(ucfirst($category['name'])) ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="category-error text-pink-600 text-sm hidden font-light"></span>
              <?php else: ?>
                <select class="mt-1 block w-full border border-slate-300 rounded p-2 bg-slate-200" disabled>
                  <option class="">No categories found</option>
                </select>
              <?php endif; ?>
            </div>

            <!-- Manufacturer -->
            <div class="w-full">

              <label for="input-manufacturer" class="block">Manufacturer</label>
              <div class="relative">

                <input type="text"
                  class="mt-1 block w-full border border-gray-300 rounded p-2 input-manufacturer"
                  placeholder="e.g., Lenovo"
                  name="input-manufacturer"
                  id="input-manufacturer" />
                <span class="brand-error text-pink-600 text-sm hidden font-light"></span>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i class="fa-solid fa-building"></i>
                </div>
              </div>

            </div>

          </div>


          <div class="col-span-10 flex gap-4">
            <!-- Model -->
            <div class="w-full">
              <label for="input-model" class="block">Model</label>
              <div class="relative">
                <input type="text"
                  class="mt-1 block w-full border border-gray-300 rounded p-2"
                  placeholder="e.g., ideapad slim3i"
                  name="input-model"
                  id="input-model">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i class="fa-solid fa-tag"></i>
                </div>
              </div>
            </div>

            <!-- Purchase Date -->
            <div class="w-full">
              <label for="purchase-date" class="block">Purchase Date</label>
              <input type="date"
                class="mt-1 block w-full border border-gray-300 rounded p-2 purchase-date"
                name="purchase-date"
                id="purchase-date">
            </div>

          </div>


          <div class="col-span-10 flex gap-4">
            <!-- Quantity -->
            <div class="w-full">
              <label for="input-qty" class="block">Quantity</label>
              <div class="relative">

                <input type="number"
                  class="mt-1 block w-full border border-gray-300 rounded p-2 input-qty"
                  placeholder="e.g., 5"
                  name="input-qty"
                  id="input-qty" />
                <span class="qty-error text-pink-600 text-sm hidden font-light"></span>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">

                  <i class="fa-solid fa-hashtag"></i>
                </div>
              </div>

            </div>

            <!-- Warranty Years -->
            <div class="w-full">
              <label for="input-warranty" class="block">Warranty (Years)</label>
              <div class="relative">

                <input type="number"
                  class="mt-1 block w-full border border-gray-300 rounded p-2"
                  placeholder="e.g., 2"
                  name="input-warranty"
                  id="input-warranty">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i class="fa-solid fa-calendar"></i>
                </div>
              </div>

            </div>
          </div>
          <!-- Inventory Photo -->
          <div>
            <label for="photo" class="block">Photo</label>
            <div class="relative">
              <input type="file"
                class="block w-full border border-gray-300 rounded p-2 cursor-pointer"
                name="photo"
                id="photo">
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-image"></i>
              </div>
            </div>
          </div>

          <div id="current-photo" class=" hidden">
            <p class="text-sm text-gray-600">Current photo:</p>
            <img id="current-photo-img" src="" alt="Current inventory photo" class="w-20 h-20 object-cover rounded">
          </div>


          <!-- Submit Button -->
          <div class="col-span-10 flex gap-4 pt-4">
            <button
              class="border border-slate-300 block text-sm w-full px-4 py-2 text-slate-700 rounded-sm hover:bg-slate-50 cursor-pointer"
              id="inventory-cancel-btn">
              Cancel
            </button>
            <button type="submit"
              class="bg-red-600 block w-full px-2 py-1 text-white rounded-sm hover:bg-red-700 add-inventory-btn cursor-pointer"
              name="add-inventory-btn"
              id="submit-btn">
              Add Inventory
            </button>
          </div>


        </form>
      </div>
    </div>


    <!-- Inventory table -->
    <article class="col-span-10 text-sm mt-4 font-light px-2" id="inventory-list-table">
      <div>
        <div>
          <?php if (empty($inventories)): ?>
            <div class="col-span-full text-center py-12">
              <div class="text-slate-400 mb-4">
                <i class="fa-solid fa-box text-6xl"></i>
              </div>
              <h3 class="text-lg font-medium text-slate-600 mb-2">No Inventory Yet</h3>
              <p class="text-slate-500 mb-4">Start by adding your first inventory.</p>

            </div>
          <?php else: ?>
            <table id="inventoryTable" class="display">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Image</th>
                  <!-- <th>Manufacturer</th>
                  <th>Model</th> -->
                  <th>Model</th>
                  <th>Category</th>
                  <th>Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>

                <?php foreach ($inventories as $index => $inventory): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="w-1/12 h-1/12 object-contain">
                      <?php
                      // Ensure photos field is not null or empty before calling explode
                      $photos = !empty($inventory['photos']) ? explode(',', $inventory['photos']) : [];
                      $photo = !empty($photos) ? $photos[0] : ''; // Use the first photo if available
                      ?>
                      <?php if ($photo): ?>
                        <img src="/<?= htmlspecialchars(ucfirst($photo)) ?>" alt="" class="w-8/12">
                      <?php else: ?>
                        <img src="/uploads/default-photo/laptop-charger.jpg" alt="No image available" class="w-8/12">
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'])) ?> / <?= htmlspecialchars(ucfirst($inventory['model'])) ?> </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['category_name'] ?? "Laptop charger")) ?></td>
                    <td><?= htmlspecialchars($inventory['total_quantity']) ?></td>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light">
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left edit-inventory-btn"
                              data-manufacturer="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-category-id=<?= htmlspecialchars($inventory['category_id'])  ?>
                              data-model="<?= htmlspecialchars($inventory['model']) ?>"
                              data-quantity="<?= htmlspecialchars($inventory['total_quantity']) ?>"
                              data-category-name="<?= htmlspecialchars($inventory['category_name']) ?>"
                              data-id="<?= htmlspecialchars($inventory['inventory_id'] ?? 0) ?>"
                              data-purchase-date="<?= htmlspecialchars($inventory['purchase_date']) ?>"
                              data-warranty-years="<?= htmlspecialchars($inventory['warranty_years']) ?>"
                              data-photo="/<?= htmlspecialchars($inventory['photos']) ?>">
                              Edit
                            </button>
                          </li>
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer w-full text-left delete-inventory-btn"
                              data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-id="<?= $inventory['inventory_id'] ?>">
                              Delete
                            </button>
                          </li>
                          <li class="px-4 py-2 hover:bg-slate-100">
                            <a href="/view" class="cursor-pointer w-full text-left">View</a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

        </div>
      </div>
    </article>


  </section>
</main>











<script src="/assets/js/add-inventory.js"></script>
<!-- <script src="/assets/js/manage-hardware.js"></script> -->
<?php require("views/partials/footer.php"); ?>