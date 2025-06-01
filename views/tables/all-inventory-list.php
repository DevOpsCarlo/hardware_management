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
                <th>Asset no.</th>
                <th>Model</th>
                <th>Serial no.</th>
                <th>Status</th>
                <th>Condition</th>
                <th>Action</th>
              </tr>
            </thead>
            <?php $itemCounter = 1; ?>
            <tbody class="text-sm font-light">
              <?php foreach ($inventories as $inventory): ?>
                <?php
                $inventoryAssets = $assetsByInventory[$inventory['inventory_id']] ?? [];
                $quantity = $inventory['quantity'];

                // Loop $quantity times to always show that many rows
                for ($i = 0; $i < $quantity; $i++):
                  $asset = $inventoryAssets[$i] ?? null;  // Get asset if exists, else null
                ?>
                  <tr class="text-xs font-light">
                    <td><?= $itemCounter++ ?></td>
                    <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'])) ?></td>
                    <td><?= !empty($asset['item_type']) ? htmlspecialchars(ucfirst($asset['item_type'])) : 'Empty'  ?></td>
                    <th class="text-xs font-light"><?= !empty($asset['asset_number']) ? htmlspecialchars(ucfirst($asset['asset_number'])) : 'Empty' ?> </th>
                    <th class="text-xs font-light"><?= !empty($asset['model']) ? htmlspecialchars(ucfirst($asset['model'])) : "Empty" ?></th>
                    <th class="text-xs font-light"><?= !empty($asset['serial_number']) ? htmlspecialchars(ucfirst($asset['serial_number'])) : "Empty" ?></th>
                    <th class="text-xs font-light"><?= !empty($asset['status']) ? htmlspecialchars(ucfirst($asset['status'])) : "Empty" ?> </th>
                    <th class="text-xs font-light"><?= !empty($asset['conditions']) ? htmlspecialchars(ucfirst($asset['conditions'])) : "Empty" ?></th>
                    <td class="relative">
                      <i class="fa-solid fa-ellipsis-vertical cursor-pointer select-menu"></i>
                      <div class="absolute top-3 left-5 mt-2 w-20 bg-white border rounded shadow group-hover:block z-10 hidden menu">
                        <ul class="text-xs text-slate-700 font-light ">
                          <!-- ASSIGN ASSET BTN -->
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer block w-full text-left assign-asset-btn"
                              data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>"
                              data-item-number="<?= $itemCounter - 1 ?>"

                              data-name="<?= htmlspecialchars($inventory['manufacturer']) ?>"
                              data-category-id="<?= htmlspecialchars($inventory['category_id']) ?>"
                              data-category-option="<?= htmlspecialchars($inventory['category_name']) ?>">
                              Assign asset
                            </button>
                          </li>
                          <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                            <button class="cursor-pointer w-full text-left delete-inventory-btn"
                              data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>"
                              item-number="<?= $itemCounter - 1 ?>">
                              Delete
                            </button>
                          </li>
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



    <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="assign-asset-modal">
      <div class="w-full max-w-5xl bg-slate-100 col-span-4 mx-auto rounded-sm p-7 shadow-lg" id="category-modal-box">
        <h2 class="text-slate-800 font-bold text-2xl mb-2 modal-title">Hardware details</h2>

        <!-- Simplified form since we're getting manufacturer and category from inventory table -->
        <form class="text-sm font-light col-span-10 grid gap-2" id="assign-asset-form" method="POST" action="/manage-hardware" enctype="multipart/form-data">
          <div class="hidden">
            <input type="text" name="item_number" id="modal-item-number">
            <input type="text" name="inventory_id" id="modal-inventory-id">
          </div>

          <figure class="border border-slate-200 p-4 grid gap-2">
            <h4 class="mb-2 text-slate-600 text-lg font-semibold" id="figure-title"></h4>

            <div class="flex items-center justify-center gap-1">
              <div class="w-full flex flex-col gap-1">
                <label class="text-xs font-bold" for="">Manufacturer</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" type="text" name="manufacturer" id="modal-manufacturer" readonly>
              </div>
              <div class="w-full flex flex-col gap-1">
                <label class="text-xs font-bold" for="">Equipment</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" name="category_display" id="modal-category-select" readonly>
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Asset No.</label>
                <input class="w-full border rounded px-3 py-1 bg-slate-200" disabled name="asset-number" id="modal-asset-number" placeholder="Will be auto-generated">
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Model</label>
                <input type="text" name="input-model" class="w-full border rounded px-3 py-1" placeholder="Enter model">
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Serial No.</label>
                <input type="text" name="input-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter serial number">
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">IP Address</label>
                <input type="text" name="input-ip-address" class="w-full border rounded px-3 py-1" placeholder="Enter IP address (optional)">
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Date of purchase</label>
                <input type="date" name="date-purchase" class="w-full border rounded px-3 py-1">
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Warranty date</label>
                <input type="date" name="warranty-date" class="w-full border rounded px-3 py-1">
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Status</label>
                <select class="w-full border rounded px-3 py-1" name="status">
                  <option value="Available">Available</option>
                  <option value="Assigned">Assigned</option>
                  <option value="Under Maintenance">Under Maintenance</option>
                </select>
              </div>

              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="">Condition</label>
                <select name="condition" class="w-full border rounded px-3 py-1">
                  <option value="New">New</option>
                  <option value="Good">Good</option>
                  <option value="Fair">Fair</option>
                  <option value="Poor">Poor</option>
                </select>
              </div>
            </div>

            <div class="flex items-center justify-center gap-1">
              <div class="flex flex-col gap-1 w-full">
                <label class="text-xs font-bold" for="asset-photo">Upload Photo</label>
                <input type="file" id="asset-photo" name="asset_photo" accept="image/*" class="w-1/2 border rounded px-3 py-1 cursor-pointer block">
                <div class="w-1/4">
                  <img id="photo-preview" class="mt-2 rounded border w-1/4 h-1/4 object-cover hidden" />
                </div>
              </div>
            </div>
          </figure>

          <figure class="border border-slate-200 p-4 grid gap-1 hidden" id="laptop-charger-section">
            <input type="hidden" name="asset-type" id="asset-type" value="laptop-charger">
            <h4 class="mb-2 text-slate-600 text-lg font-semibold">Laptop charger info</h4>
            <div class="flex items-center gap-2">
              <div class="flex flex-col gap-1 w-full">
                <label for="">Manufacturer</label>
                <input type="text" name="charger-model" class="w-full border rounded px-3 py-1" id="model-charger" placeholder="Enter charger model">
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label for="">Asset No.</label>
                <input type="text" name="charger-asset-number" class="w-full border rounded px-3 py-1 bg-slate-200" disabled id="charger-asset-number" readonly placeholder="Will be auto-generated">
              </div>
            </div>

            <div class="flex items-center justify-between gap-2 mt-2">
              <div class="flex flex-col gap-1 w-full">
                <label for="">Serial No.</label>
                <input type="text" name="charger-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter charger serial no.">
              </div>
              <div class="flex flex-col gap-1 w-full">
                <label for="">Condition</label>
                <select name="charger-condition" class="w-full border rounded px-3 py-1">
                  <option value="New">New</option>
                  <option value="Good">Good</option>
                  <option value="Fair">Fair</option>
                  <option value="Poor">Poor</option>
                </select>
              </div>
            </div>
          </figure>

          <button type="submit" class="mt-4 bg-red-500 hover:bg-red-600 cursor-pointer font-bold text-sm text-white px-4 py-2 rounded" name="assigned-btn">Assign Asset</button>
        </form>
      </div>
    </div>