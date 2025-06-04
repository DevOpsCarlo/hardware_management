<?php require("views/partials/head.php"); ?>

<main class="grid grid-cols-1 sm:grid-cols-12 min-h-screen">
  <?php require("views/sidebar.php"); ?>
  <section class="col-span-12 md:col-span-10">
    <?php require("views/banner.php"); ?>

    <article class="my-6 px-6 text-2xl font-bold text-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 space-y-5">
      <?php
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
      <?php
      // Handle success messages
      if (!empty($_SESSION['inventory_success'])):
        $inventorySuccessMessage = htmlspecialchars($_SESSION['inventory_success'], ENT_QUOTES, 'UTF-8');
      ?>
        <script>
          Swal.fire({
            icon: 'success',
            text: '<?= $inventorySuccessMessage ?>',
            showConfirmButton: true,
            position: 'center',
            timer: 3000,
            timerProgressBar: true
          });
        </script>
      <?php
        unset($_SESSION['inventory_success']);
      endif;
      ?>
      <div class="col-span-10 ">
        <div class="flex text-sm">
          <!-- <button class="tab-button px-3 py-1 bg-red-600 text-white shadow active-tab cursor-pointer" data-tab="count"> -->
          <a href="/manage-hardware" class="px-3 py-1 bg-slate-200 text-slate-400 shadow cursor-pointer <?= urlIs('/manage-hardware') ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-400'  ?>">
            Add Inventory
          </a>
          <!-- </button> -->
          <!-- <button class="tab-button px-4 py-2 bg-slate-200 text-slate-500 shadow cursor-pointer" data-tab="details"> -->
          <a href="/manage-hardware/inventory-list" class=" px-3 py-1 shadow cursor-pointer <?= urlIs('/manage-hardware/inventory-list') ? 'bg-red-600 text-white' : ' bg-slate-200 text-slate-400' ?> ">
            Inventory List
          </a>
          <!-- </button> -->
        </div>

    </article>

    <!-- All Inventory List -->
    <?php require("views/tables/all-inventory-list.php"); ?>

  </section>
</main>




<script src="/assets/js/manage-hardware.js"></script>
<?php require("views/partials/footer.php"); ?>