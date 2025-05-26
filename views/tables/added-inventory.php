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
                        <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                          <button class="cursor-pointer block w-full text-left edit-inventory-btn"
                            data-id="<?= $inventory['inventory_id'] ?>"
                            data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                            data-qty="<?= htmlspecialchars($inventory['quantity']) ?>"
                            data-category-id="<?= htmlspecialchars($inventory['category_id']) ?>">
                            Edit
                          </button>
                        </li>
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