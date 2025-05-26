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
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light ">
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1"><button class="cursor-pointer block w-full text-left edit-inventory-btn" data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>" item-number="<?= $itemCounter - 1 ?>" data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>">Assign asset</button></li>

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