<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <?php require("views/banner.php"); ?>
    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 space-y-5">

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
          <form class="space-y-4 bg-white p-6 rounded shadow text-slate-800 grid grid-cols-2 gap-2 form " action="/manage-hardware" method="POST">
            <input type="hidden" name="inventory_id" id="inventory-id">
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
      </div>

      <!-- INVENTORY TABLE LIST -->
      <?php require("views/tables/added-inventory.php"); ?>

      <!-- All Inventory List -->
      <?php require("views/tables/all-inventory-list.php"); ?>
  </section>
</main>












<script src="assets/js/manage-hardware.js"></script>
<?php require("views/partials/footer.php"); ?>