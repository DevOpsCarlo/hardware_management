     <?php
      $laptopChargerAsset = null;
      foreach ($assets as $asset) {
        if (strtolower($asset['item_type']) === 'laptop charger') {
          $laptopChargerAsset = $asset;
          break;
        }
      }
      ?>


     <?php
      $category = $_SESSION['inventory_form_data'] ?? [];
      $editMode = $_SESSION['inventory_edit_mode'] ?? false;
      $isEditMode = !empty($asset['asset_number']) || !empty($asset['model']);

      unset($_SESSION['category_form_data'], $_SESSION['category_edit_mode']);
      ?>


     <article class="col-span-10 text-sm font-light" id="detailed-list-table">
       <div>
         <h4 class="text-slate-700 font-semibold text-base">All inventory list</h4>
         <div>
           <table id="detailedTable" class="display text-left">
             <thead>
               <tr class="">
                 <th>No</th>
                 <th>Manufacturer</th>
                 <th>Category</th>
                 <th>image</th>
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
                  // Get all assets for this inventory
                  $allInventoryAssets = $assetsByInventory[$inventory['inventory_id']] ?? [];

                  // Separate laptops and chargers
                  $laptopAssets = [];
                  $chargerAssets = [];

                  foreach ($allInventoryAssets as $asset) {
                    if (empty($asset['related_laptop_id'])) {
                      // This is a main asset (laptop, mouse, etc.)
                      $laptopAssets[] = $asset;
                    } else {
                      // This is a charger, group by related laptop ID
                      $chargerAssets[$asset['related_laptop_id']] = $asset;
                    }
                  }

                  $quantity = $inventory['quantity'];

                  // Loop through the quantity, but only show main assets (not chargers separately)
                  for ($i = 0; $i < $quantity; $i++):
                    $asset = $laptopAssets[$i] ?? null;
                    // If this is a laptop, get its related charger
                    $relatedCharger = null;
                    if ($asset && $asset['category_code'] === '01') { // assuming you have category_code
                      $relatedCharger = $chargerAssets[$asset['id']] ?? null;
                    }
                  ?>
                   <tr class="text-xs font-light text-left">
                     <td><?= $itemCounter++ ?></td>
                     <td><?= htmlspecialchars(ucfirst($inventory['manufacturer'])) ?></td>
                     <td><?= htmlspecialchars($inventory['manufacturer'] ?? 'Empty')  ?></td>
                     <td class="w-1/12  h-1/12 object-contain"> <img src="/<?= !empty($asset['asset_photo']) ? htmlspecialchars(ucfirst($asset['asset_photo'])) : ''  ?>" alt="" class="w-1/2"> <?= !empty($asset['asset_photo']) ? '' : 'Empty'  ?></td>
                     <td><?= !empty($asset['asset_number']) ? htmlspecialchars(ucfirst($asset['asset_number'])) : 'Empty' ?> </td>
                     <td><?= !empty($asset['model']) ? htmlspecialchars(ucfirst($asset['model'])) : "Empty" ?></td>
                     <td><?= !empty($asset['serial_number']) ? htmlspecialchars(ucfirst($asset['serial_number'])) : "Empty" ?></td>
                     <td><?= !empty($asset['status']) ? htmlspecialchars(ucfirst($asset['status'])) : "Empty" ?> </td>
                     <td><?= !empty($asset['conditions']) ? htmlspecialchars(ucfirst($asset['conditions'])) : "Empty" ?></td>
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
                               data-category-option="<?= htmlspecialchars($inventory['category_name']) ?>"
                               data-asset-number="<?= htmlspecialchars($asset['asset_number'] ?? '') ?>"
                               data-model="<?= htmlspecialchars($asset['model'] ?? '') ?>"
                               data-serial-number="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>"
                               data-ip-address="<?= htmlspecialchars($asset['ip_address'] ?? '') ?>"
                               data-status="<?= htmlspecialchars($asset['status'] ?? '') ?>"
                               data-conditions="<?= htmlspecialchars($asset['conditions'] ?? '') ?>"
                               data-dateof-purchase="<?= htmlspecialchars($asset['date_of_purchase'] ?? '') ?>"
                               data-warranty-date="<?= htmlspecialchars($asset['warranty_date'] ?? '') ?>"
                               data-asset-photo="<?= htmlspecialchars($asset['asset_photo'] ?? '') ?>"
                               data-asset-id="<?= htmlspecialchars($asset['id'] ?? 0) ?>"
                               <?php if ($relatedCharger): ?>
                               data-charger-id="<?= htmlspecialchars($relatedCharger['id'] ?? 0) ?>"
                               data-charger-asset-number="<?= htmlspecialchars($relatedCharger['asset_number']) ?>"
                               data-charger-model="<?= htmlspecialchars($relatedCharger['model']) ?>"
                               data-charger-serial-number="<?= htmlspecialchars($relatedCharger['serial_number'] ?? "") ?>"
                               data-charger-conditions="<?= htmlspecialchars($relatedCharger['conditions']) ?>"
                               data-charger-status="<?= htmlspecialchars($relatedCharger['status']) ?>"
                               <?php endif; ?>>
                               Assign asset
                             </button>

                           </li>
                           <li class="px-4 py-2 hover:bg-slate-100 border-b-1">
                             <button class="cursor-pointer w-full text-left delete-asset-btn"
                               data-id="<?= htmlspecialchars($inventory['inventory_id']) ?>"
                               data-asset-id="<?= htmlspecialchars($asset['id'] ?? 0) ?>"
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



     <div class="fixed inset-0 top-0 left-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 <?= (!empty($_SESSION['form_errors'])) ? '' : 'hidden' ?>" id="assign-asset-modal">
       <div class="w-full max-w-5xl bg-slate-100 col-span-4 mx-auto rounded-sm p-7 shadow-lg" id="category-modal-box">
         <h2 class="text-slate-800 font-bold text-2xl mb-2 modal-title">Hardware details</h2>
         <form class="text-sm font-light col-span-10 grid gap-2" id="assign-asset-form" method="POST" action="/manage-hardware/inventory-list" enctype="multipart/form-data">
           <div class="hidden">
             <input type="text" name="item_number" id="modal-item-number">
             <input type="text" name="inventory_id" id="modal-inventory-id">
             <input type="hidden" name="asset-id" value="<?= htmlspecialchars($asset['id'] ?? 0) ?>" id="asset-id">
           </div>

           <figure class="border border-slate-200 p-4 grid gap-2">
             <h4 class="mb-2 text-slate-600 text-lg font-semibold" id="figure-title"></h4>
             <span id="has-error" class="text-sm text-pink-600"></span>
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
                 <input class="w-full border rounded px-3 py-1 bg-slate-200" readonly name="asset-number" id="modal-asset-number" value="<?= htmlspecialchars($asset['asset_number']) ?>">
               </div>

               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Model</label>
                 <input type="text" name="input-model" class="w-full border rounded px-3 py-1" placeholder="Enter model" id="input-model" value="<?= htmlspecialchars($asset['model'] ?? '') ?>">

               </div>
             </div>

             <div class="flex items-center justify-center gap-1">
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Serial No.</label>
                 <div class="flex w-full">
                   <input type="text" name="input-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter serial number" id="input-serial-number" value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
                   <?php if (!empty($_SESSION['form_errors']['serial'])): ?>
                     <p class="text-pink-600 text-xs w-full"><?= htmlspecialchars($_SESSION['form_errors']['serial']) ?></p>
                   <?php endif; ?>
                 </div>

               </div>
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">IP Address</label>
                 <div class="flex w-full">
                   <input type="text" name="input-ip-address" class="w-full border rounded px-3 py-1" placeholder="Enter IP address (optional)" id="input-ip-address" value="<?= htmlspecialchars($asset['ip_address'] ?? '') ?>">
                   <?php if (!empty($_SESSION['form_errors']['ip'])): ?>
                     <p class="text-pink-600 text-xs"><?= htmlspecialchars($_SESSION['form_errors']['ip']) ?></p>
                   <?php endif; ?>
                 </div>
               </div>
             </div>

             <div class="flex items-center justify-center gap-1">
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Date of purchase</label>
                 <input type="date" name="date-purchase" class="w-full border rounded px-3 py-1" value="<?= isset($asset['date_purchase']) ? htmlspecialchars(date('Y-m-d', strtotime($asset['date_of_purchase']))) : '' ?>" id="date-purchase">
               </div>
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Warranty date</label>
                 <input type="date" name="warranty-date" class="w-full border rounded px-3 py-1" value="<?= isset($asset['date_purchase']) ? htmlspecialchars(date('Y-m-d', strtotime($asset['warranty_date']))) : '' ?>" id="warranty-date">
               </div>
             </div>

             <div class="flex items-center justify-center gap-1">
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Status</label>
                 <?php
                  $statusOptions = ['Available', 'Assigned', 'Under Maintenance'];
                  $currentStatus = $asset['status'] ?? '';
                  ?>
                 <select class="w-full border rounded px-3 py-1" name="status" id="select-status">
                   <?php foreach ($statusOptions as $status): ?>
                     <option value="<?= htmlspecialchars($status) ?>" <?= $currentStatus === $status ? 'selected' : '' ?>>
                       <?= htmlspecialchars($status) ?>
                     </option>
                   <?php endforeach; ?>
                 </select>
               </div>

               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="">Condition</label>
                 <?php
                  $conditionOptions = ['New', 'Good', 'Fair', 'Poor'];
                  $currentCondition = $asset['conditions'] ?? '';
                  ?>
                 <select name="condition" class="w-full border rounded px-3 py-1" id="select-conditions">
                   <?php foreach ($conditionOptions as $condition): ?>
                     <option value="<?= htmlspecialchars($condition) ?>" <?= $currentCondition === $condition ? 'selected' : '' ?>>
                       <?= htmlspecialchars($condition) ?>
                     </option>
                   <?php endforeach; ?>
                 </select>
               </div>
             </div>

             <div class="flex items-center justify-center gap-1">
               <div class="flex flex-col gap-1 w-full">
                 <label class="text-xs font-bold" for="asset-photo">Upload Photo</label>
                 <div class="flex items-center gap-4">
                   <div class="w-full">
                     <input type="file" id="asset-photo" name="asset_photo" accept="image/*" class="w-full border rounded px-3 py-1 cursor-pointer block">
                   </div>
                   <div class="w-full">
                     <?php if (!empty($asset['asset_photo'])): ?>
                       <img src="<?= htmlspecialchars($asset['asset_photo']) ?>" id="photo-preview" class="rounded border w-1/12 h-1/12 object-cover" />
                     <?php else: ?>
                       <img id="photo-preview" class="rounded border w-1/12 h-1/12 object-cover hidden" />
                     <?php endif; ?>
                   </div>
                 </div>

               </div>
             </div>
           </figure>

           <figure class="border border-slate-200 p-4 grid gap-1  <?= $laptopChargerAsset ? '' : 'hidden' ?>" id="laptop-charger-section">

             <input type="hidden" name="asset-type" id="asset-type" value="laptop charger">
             <input type="hidden" name="charger-id" value="<?= htmlspecialchars($relatedCharger['id'] ?? 0) ?>" id="charger-id">

             <h4 class="mb-2 text-slate-600 text-lg font-semibold">Laptop charger info</h4>
             <div class="flex items-center gap-2">
               <div class="flex flex-col gap-1 w-full">
                 <label for="">Manufacturer</label>
                 <input type="text" name="charger-model" class="w-full border rounded px-3 py-1" id="model-charger" placeholder="Enter charger model" readonly>
               </div>
               <div class="flex flex-col gap-1 w-full">
                 <label for="">Asset No.</label>
                 <input type="text" name="charger-asset-number" class="w-full border rounded px-3 py-1 bg-slate-200" id="charger-asset-number" value="<?= htmlspecialchars($laptopChargerAsset['asset_number'] ?? '') ?>" readonly>
               </div>
             </div>

             <div class="flex items-center justify-between gap-2 mt-2">
               <div class="flex flex-col gap-1 w-full">
                 <label>Serial No.</label>
                 <input type="text" name="charger-serial-number" class="w-full border rounded px-3 py-1" placeholder="Enter charger serial no." id="charger-serial-number">
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

           <button type="submit" class="mt-4 bg-red-500 hover:bg-red-600 cursor-pointer font-bold text-sm text-white px-4 py-2 rounded" name="assigned-btn" id="form-assign-asset">Assign Asset</button>
         </form>
       </div>
     </div>
     <?php unset($_SESSION['form_errors']); ?>