<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <!-- require("views/banner.php");  -->
    <article class="py-2 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 space-y-5  bg-red-800">
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
    <div class="flex justify-end my-4">
      <button class="text-red-700 border px-2 py-1 text-sm hover:bg-red-700 hover:text-white rounded-sm mr-2 cursor-pointer" id="toggle-add-inventory-form"> + Add inventory</button>
    </div>

    <!-- ADD INVENTORY FORM -->


    <!-- <div class="tab-content block md:col-span-2 lg:col-span-10 text-sm rounded shadow" id="count"> -->
    <!-- Updated Inventory Form Modal -->
    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-xs flex items-center justify-center z-50 hidden" id="inventory-form-modal">
      <div class="w-full max-w-7xl bg-white col-span-4 mx-auto rounded-sm p-4 shadow-lg" id="inventory-form">
        <!-- Dynamic Title -->
        <h2 class="text-slate-800 font-bold px-6 pt-4 text-xl" id="form-title">Add Inventory</h2>

        <form class="space-y-4 bg-white p-6 text-slate-800 grid grid-cols-2 gap-2 form text-sm font-medium"
          action="/manage-hardware" method="POST" enctype="multipart/form-data" id="inventory-form-element">

          <!-- Hidden field for inventory ID (for edit mode) -->
          <input type="hidden" name="inventory_id" id="inventory-id">

          <!-- Category -->
          <div>
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
          <div>
            <label for="input-manufacturer" class="block">Manufacturer</label>
            <input type="text"
              class="mt-1 block w-full border border-gray-300 rounded p-2 input-manufacturer"
              placeholder="e.g Lenovo"
              name="input-manufacturer"
              id="input-manufacturer" />
            <span class="brand-error text-pink-600 text-sm hidden font-light"></span>
          </div>

          <!-- Model -->
          <div>
            <label for="input-model" class="block">Model</label>
            <input type="text"
              class="mt-1 block w-full border border-gray-300 rounded p-2"
              placeholder="e.g ideapad slim3i"
              name="input-model"
              id="input-model">
          </div>

          <!-- Purchase Date -->
          <div>
            <label for="purchase-date" class="block">Purchase Date</label>
            <input type="date"
              class="block w-full border border-gray-300 rounded p-2"
              name="purchase-date"
              id="purchase-date">
          </div>

          <!-- Quantity -->
          <div>
            <label for="input-qty" class="block">Quantity</label>
            <input type="number"
              class="mt-1 block w-full border border-gray-300 rounded p-2 input-qty"
              placeholder="e.g 5"
              name="input-qty"
              id="input-qty" />
            <span class="qty-error text-pink-600 text-sm hidden font-light"></span>
          </div>

          <!-- Warranty Years -->
          <div>
            <label for="input-warranty" class="block">Warranty (Years)</label>
            <input type="number"
              class="mt-1 block w-full border border-gray-300 rounded p-2"
              placeholder="e.g 2"
              name="input-warranty"
              id="input-warranty">
          </div>

          <!-- Inventory Photo -->
          <div>
            <label for="photo" class="block">Photo</label>
            <input type="file"
              class="block w-full border border-gray-300 rounded p-2 cursor-pointer"
              name="photo"
              id="photo">
            <!-- Display current photo when editing -->

          </div>

          <div id="current-photo" class=" hidden">
            <p class="text-sm text-gray-600">Current photo:</p>
            <img id="current-photo-img" src="" alt="Current inventory photo" class="w-20 h-20 object-cover rounded">
          </div>


          <!-- Submit Button -->
          <div class="grid col-span-2 justify-end">
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
    <article class="col-span-10 text-sm mt-4 font-light" id="inventory-list-table">
      <div>
        <div>
          <table id="inventoryTable" class="display">
            <thead>
              <tr>
                <th>No.</th>
                <th>Image</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <!-- <th>Purchase Date</th> -->
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
                    <td class="w-1/12  h-1/12 object-contain"> <img src="<?= htmlspecialchars(ucfirst($inventory['photo'])) ?>" alt=""> </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'])) ?> </td>
                    <td><?= htmlspecialchars(ucfirst($inventory['model'])) ?> </td>
                    <!-- $purchaseDate = $inventory['purchase_date'] ?? null;
                    $date = null;

                    if ($purchaseDate && DateTime::createFromFormat('Y-m-d', $purchaseDate) !== false) {
                      $date = DateTime::createFromFormat('Y-m-d', $purchaseDate);
                    }
                    <td><$date ? htmlspecialchars($date->format('F j, Y')) : 'N/A' </td>
                     -->
                    <td><?= htmlspecialchars(ucfirst($inventory['category_name'])) ?></td>
                    <td><?= htmlspecialchars($inventory['quantity']) ?></td>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light ">
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left edit-inventory-btn"
                              data-id="<?= $inventory['id'] ?>"
                              data-category-id="<?= $inventory['category_id'] ?>"
                              data-manufacturer="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-model="<?= htmlspecialchars($inventory['model']) ?>"
                              data-quantity="<?= $inventory['quantity'] ?>"
                              data-warranty-years="<?= $inventory['warranty_years'] ?>"
                              data-photo="<?= htmlspecialchars($inventory['photo']) ?>">
                              Edit
                            </button>
                            <!-- data-purchase-date=" $inventory['purchase_date'] " -->

                          </li>
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer w-full text-left delete-inventory-btn" data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>" data-id="<?= $inventory['id'] ?>">Delete</button></li>
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











<script src="/assets/js/add-inventory.js"></script>
<!-- <script src="/assets/js/manage-hardware.js"></script> -->
<?php require("views/partials/footer.php"); ?>