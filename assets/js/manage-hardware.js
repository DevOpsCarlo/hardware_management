// "use strict";

// document.addEventListener("DOMContentLoaded", function () {
//   const tabButtons = document.querySelectorAll(".tab-button");
//   const tabContents = document.querySelectorAll(".tab-content");
//   const addInventory = document.querySelector(".add-inventory-btn");

//   const menu = document.querySelectorAll(".menu");

//   // ADD INVENTORY TAB
//   addInventory.addEventListener("click", (e) => {
//     let hasError = false;
//     const inputBrand = document.querySelector(".input-manufacturer");
//     const inputQty = document.querySelector(".input-qty");
//     const brandError = document.querySelector(".brand-error");
//     const qtyError = document.querySelector(".qty-error");
//     const categorySelect = document.querySelector("select[name='category_id']");
//     const categoryError = document.querySelector(".category-error");
//     brandError.classList.add("hidden");
//     categoryError.classList.add("hidden");

//     if (inputBrand.value.trim() === "") {
//       hasError = true;
//       brandError.textContent = "Please enter a manufacturer.";
//       brandError.classList.remove("hidden");
//     }
//     if (
//       inputQty.value.trim() === "" ||
//       isNaN(inputQty.value) ||
//       inputQty.value <= 0
//     ) {
//       hasError = true;
//       qtyError.textContent = "Please enter quantity.";
//       qtyError.classList.remove("hidden");
//     }
//     if (categorySelect.value === "") {
//       hasError = true;
//       categoryError.textContent = "Please select a category.";
//       categoryError.classList.remove("hidden");
//     }

//     if (hasError) {
//       e.preventDefault();
//     }
//   });

//   // INVENTORY DATA TAB
//   tabButtons.forEach((button) => {
//     button.addEventListener("click", () => {
//       const tab = button.getAttribute("data-tab");

//       // Update tab buttons
//       tabButtons.forEach((btn) => {
//         btn.classList.remove("bg-red-600", "text-white", "active-tab");
//         btn.classList.add("bg-slate-200", "text-slate-500");
//       });
//       button.classList.add("bg-red-600", "text-white", "active-tab");
//       button.classList.remove("bg-slate-200", "text-slate-500");

//       // Show/hide content
//       tabContents.forEach((content) => {
//         content.classList.toggle("hidden", content.id !== tab);
//         content.classList.toggle("block", content.id === tab);
//       });

//       const inventoryTable = document.getElementById("inventory-list-table");
//       const detailedTable = document.getElementById("detailed-list-table");

//       if (tab === "count") {
//         inventoryTable.classList.remove("hidden");
//         detailedTable.classList.add("hidden");
//       } else if (tab === "details") {
//         detailedTable.classList.remove("hidden");
//         inventoryTable.classList.add("hidden");
//       }
//     });
//   });

//   // CLICK HANDLING FOR ELIPSIS
//   document
//     .querySelector("#inventory-list-table")
//     .addEventListener("click", function (e) {
//       // Handle ellipsis toggle
//       if (e.target.classList.contains("select-menu")) {
//         e.stopPropagation();

//         // Close all other menus
//         document
//           .querySelectorAll(".menu")
//           .forEach((m) => m.classList.add("hidden"));

//         // Toggle current
//         const menuElement = e.target.nextElementSibling;
//         if (menuElement && menuElement.classList.contains("menu")) {
//           menuElement.classList.toggle("hidden");
//         }
//       }

//       //EDIT INVENTORY
//       if (e.target.classList.contains("edit-inventory-btn")) {
//         e.stopPropagation();

//         // Get form elements
//         const form = document.querySelector("form");
//         const manufacturerInput = form.querySelector(".input-manufacturer");
//         const quantityInput = form.querySelector(".input-qty");
//         const categorySelect = form.querySelector("select[name='category_id']");
//         const inventoryIdInput = form.querySelector("#inventory-id");
//         const submitButton = form.querySelector(".add-inventory-btn");

//         // Populate values from data-attributes
//         const id = e.target.dataset.id;
//         const name = e.target.dataset.name;
//         const qty = e.target.dataset.qty;
//         const categoryId = e.target.dataset.categoryId;

//         manufacturerInput.value = name;
//         quantityInput.value = qty;
//         categorySelect.value = categoryId;
//         inventoryIdInput.value = id;

//         submitButton.textContent = "Update Inventory";
//         form.action = "/manage-hardware";
//       }

//       // Handle delete inventory
//       if (e.target.classList.contains("delete-inventory-btn")) {
//         e.stopPropagation();
//         const inventoryId = e.target.getAttribute("data-id");
//         const inventoryName = e.target.getAttribute("data-name");

//         Swal.fire({
//           icon: "warning",
//           title: "Delete Category?",
//           text: `Are you sure you want to delete "${inventoryName}"?`,
//           showCancelButton: true,
//           confirmButtonColor: "#d33",
//           cancelButtonColor: "#3085d6",
//           confirmButtonText: "Delete",
//         }).then((result) => {
//           if (result.isConfirmed) {
//             const formData = new FormData();
//             formData.append("delete_inventory_id", inventoryId);

//             fetch("/manage-hardware", {
//               method: "POST",
//               body: formData,
//             })
//               .then((res) => res.text())
//               .then(() => {
//                 Swal.fire({
//                   icon: "success",
//                   text: `"${inventoryName}" was deleted successfully!`,
//                   showConfirmButton: false,
//                   timer: 2000,
//                   timerProgressBar: true,
//                   position: "top-end",
//                   toast: true,
//                 });

//                 // Remove the row
//                 e.target.closest("tr").remove();
//               })
//               .catch(() => {
//                 Swal.fire("Error", "Could not delete the inventory.", "error");
//               });
//           }
//         });
//       }
//     });

//   document
//     .querySelector("#detailed-list-table")
//     .addEventListener("click", function (e) {
//       // ASSIGN ASSET INVENTORY
//       if (e.target.classList.contains("assign-asset-btn")) {
//         e.stopPropagation();

//         const modal = document.getElementById("assign-asset-modal");
//         const itemNumberInput = document.getElementById("modal-item-number");
//         const inventoryIdInput = document.getElementById("modal-inventory-id");
//         const manufacturerInput = document.getElementById("modal-manufacturer");
//         const categorySelect = document.getElementById("modal-category-select");
//         const figureTitle = document.getElementById("figure-title");
//         const modelChargerInput = document.getElementById("model-charger");
//         const chargerSection = document.getElementById(
//           "laptop-charger-section"
//         );
//         const chargerAssetNumberInput = document.getElementById(
//           "charger-asset-number"
//         );
//         const assetNumberInput = document.getElementById("modal-asset-number");

//         const itemNumber = e.target.getAttribute("data-item-number");
//         const inventoryId = e.target.getAttribute("data-id");
//         const manufacturer = e.target.getAttribute("data-name");
//         const categoryId = e.target.getAttribute("data-category-id").trim();
//         const categoryName = e.target
//           .getAttribute("data-category-option")
//           .trim();

//         itemNumberInput.value = itemNumber;
//         inventoryIdInput.value = inventoryId;
//         manufacturerInput.value = manufacturer;
//         modelChargerInput.value = manufacturer;

//         const firstLetter = categoryName.charAt(0);
//         const firstLetterCap = firstLetter.toUpperCase();
//         const remainingLetter = categoryName.slice(1);
//         const upperCategoryName = firstLetterCap + remainingLetter;

//         const assetPhotoInput = document.getElementById("asset-photo");
//         if (!assetPhotoInput.dataset.listenerAdded) {
//           assetPhotoInput.addEventListener("change", function (e) {
//             const preview = document.getElementById("photo-preview");
//             const file = e.target.files[0];

//             if (file) {
//               preview.src = URL.createObjectURL(file);
//               preview.classList.remove("hidden"); // likely should *show*, not hide
//             } else {
//               preview.src = "";
//               preview.classList.add("hidden");
//             }
//           });
//           assetPhotoInput.dataset.listenerAdded = "true";
//         }
//         // Generating Asset Number based on the category
//         let assetPrefix = "TRA";
//         let categoryCode = "01"; // Default code for laptops
//         let assetSuffix = "0001"; // Default starting asset number

//         if (categoryName.toLowerCase() === "laptop") {
//           categoryCode = "01";
//         } else if (categoryName.toLowerCase() === "laptop_charger") {
//           categoryCode = "01";
//           assetSuffix = "0001C"; // Adding "C" for charger category
//         } else if (categoryName.toLowerCase() === "desktop_monitor") {
//           categoryCode = "03";
//           assetSuffix = "0001";
//         } else if (categoryName.toLowerCase() === "printer") {
//           categoryCode = "05";
//           assetSuffix = "0001";
//         }
//         assetNumberInput.value = `${assetPrefix}-${categoryCode}-${assetSuffix}`;

//         if (categoryName.toLowerCase() === "laptop") {
//           chargerSection.classList.remove("hidden");
//           const laptopAssetNumber = assetNumberInput.value; // Assume itemNumber is the laptop's asset number (e.g., TRA-01-0001)
//           const chargerAssetNumber = laptopAssetNumber + "C"; // Append 'C' for charger

//           chargerAssetNumberInput.value = chargerAssetNumber;

//           // Set the hidden input to indicate this is a charger asset
//           document.getElementById("asset-type").value = "laptop-charger";
//         } else {
//           chargerSection.classList.add("hidden");
//         }

//         categorySelect.value = upperCategoryName;
//         console.log(categorySelect);
//         modal.style.display = "flex"; // Show modal
//         figureTitle.textContent = `${manufacturer} ${categoryName.toLowerCase()}  info`;
//       }

//       // Handle ellipsis toggle
//       if (e.target.classList.contains("select-menu")) {
//         e.stopPropagation();

//         // Close all other menus
//         document
//           .querySelectorAll(".menu")
//           .forEach((m) => m.classList.add("hidden"));

//         // Toggle current
//         const menuElement = e.target.nextElementSibling;
//         if (menuElement && menuElement.classList.contains("menu")) {
//           menuElement.classList.toggle("hidden");
//         }
//       }

//       // Handle delete inventory
//       if (e.target.classList.contains("delete-inventory-btn")) {
//         e.stopPropagation();
//         const inventoryId = e.target.getAttribute("data-id");
//         const inventoryName = e.target.getAttribute("data-name");

//         Swal.fire({
//           icon: "warning",
//           title: "Delete Category?",
//           text: `Are you sure you want to delete "${inventoryName}"?`,
//           showCancelButton: true,
//           confirmButtonColor: "#d33",
//           cancelButtonColor: "#3085d6",
//           confirmButtonText: "Delete",
//         }).then((result) => {
//           if (result.isConfirmed) {
//             const formData = new FormData();
//             formData.append("delete_inventory_id", inventoryId);

//             fetch("/manage-hardware", {
//               method: "POST",
//               body: formData,
//             })
//               .then((res) => res.text())
//               .then(() => {
//                 Swal.fire({
//                   icon: "success",
//                   text: `"${inventoryName}" was deleted successfully!`,
//                   showConfirmButton: false,
//                   timer: 2000,
//                   timerProgressBar: true,
//                   position: "top-end",
//                   toast: true,
//                 });

//                 // Remove the row
//                 e.target.closest("tr").remove();
//               })
//               .catch(() => {
//                 Swal.fire("Error", "Could not delete the inventory.", "error");
//               });
//           }
//         });
//       }
//     });

//   // ✅ CLOSE ALL MENUS ON OUTSIDE CLICK
//   document.addEventListener("click", () => {
//     document
//       .querySelectorAll(".menu")
//       .forEach((m) => m.classList.add("hidden"));
//   });

//   document
//     .getElementById("assign-asset-modal")
//     .addEventListener("click", function (e) {
//       if (e.target.id === "assign-asset-modal") {
//         e.currentTarget.style.display = "none";
//       }
//     });
// });

"use strict";

document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");
  const addInventory = document.querySelector(".add-inventory-btn");

  // ADD INVENTORY TAB VALIDATION
  if (addInventory) {
    addInventory.addEventListener("click", (e) => {
      let hasError = false;
      const inputBrand = document.querySelector(".input-manufacturer");
      const inputQty = document.querySelector(".input-qty");
      const brandError = document.querySelector(".brand-error");
      const qtyError = document.querySelector(".qty-error");
      const categorySelect = document.querySelector(
        "select[name='category_id']"
      );
      const categoryError = document.querySelector(".category-error");

      // Clear previous errors
      if (brandError) brandError.classList.add("hidden");
      if (qtyError) qtyError.classList.add("hidden");
      if (categoryError) categoryError.classList.add("hidden");

      // Validate manufacturer
      if (!inputBrand || inputBrand.value.trim() === "") {
        hasError = true;
        if (brandError) {
          brandError.textContent = "Please enter a manufacturer.";
          brandError.classList.remove("hidden");
        }
      }

      // Validate quantity
      if (
        !inputQty ||
        inputQty.value.trim() === "" ||
        isNaN(inputQty.value) ||
        inputQty.value <= 0
      ) {
        hasError = true;
        if (qtyError) {
          qtyError.textContent = "Please enter a valid quantity.";
          qtyError.classList.remove("hidden");
        }
      }

      // Validate category
      if (!categorySelect || categorySelect.value === "") {
        hasError = true;
        if (categoryError) {
          categoryError.textContent = "Please select a category.";
          categoryError.classList.remove("hidden");
        }
      }

      if (hasError) {
        e.preventDefault();
      }
    });
  }

  // TAB SWITCHING FUNCTIONALITY
  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const tab = button.getAttribute("data-tab");

      // Update tab buttons
      tabButtons.forEach((btn) => {
        btn.classList.remove("bg-red-600", "text-white", "active-tab");
        btn.classList.add("bg-slate-200", "text-slate-500");
      });
      button.classList.add("bg-red-600", "text-white", "active-tab");
      button.classList.remove("bg-slate-200", "text-slate-500");

      // Show/hide content
      tabContents.forEach((content) => {
        content.classList.toggle("hidden", content.id !== tab);
        content.classList.toggle("block", content.id === tab);
      });

      const inventoryTable = document.getElementById("inventory-list-table");
      const detailedTable = document.getElementById("detailed-list-table");

      if (tab === "count" && inventoryTable && detailedTable) {
        inventoryTable.classList.remove("hidden");
        detailedTable.classList.add("hidden");
      } else if (tab === "details" && inventoryTable && detailedTable) {
        detailedTable.classList.remove("hidden");
        inventoryTable.classList.add("hidden");
      }
    });
  });

  // INVENTORY TABLE EVENT HANDLING
  const inventoryTable = document.querySelector("#inventory-list-table");
  if (inventoryTable) {
    inventoryTable.addEventListener("click", function (e) {
      handleTableActions(e);
    });
  }

  // DETAILED TABLE EVENT HANDLING
  const detailedTable = document.querySelector("#detailed-list-table");
  if (detailedTable) {
    detailedTable.addEventListener("click", function (e) {
      handleDetailedTableActions(e);
    });
  }

  // HANDLE INVENTORY TABLE ACTIONS
  function handleTableActions(e) {
    // Handle ellipsis toggle
    if (e.target.classList.contains("select-menu")) {
      e.stopPropagation();
      closeAllMenus();

      const menuElement = e.target.nextElementSibling;
      if (menuElement && menuElement.classList.contains("menu")) {
        menuElement.classList.toggle("hidden");
      }
    }

    // EDIT INVENTORY
    if (e.target.classList.contains("edit-inventory-btn")) {
      e.stopPropagation();
      editInventory(e.target);
    }

    // DELETE INVENTORY
    if (e.target.classList.contains("delete-inventory-btn")) {
      e.stopPropagation();
      deleteInventory(e.target);
    }
  }

  // HANDLE DETAILED TABLE ACTIONS
  function handleDetailedTableActions(e) {
    // Handle ellipsis toggle
    if (e.target.classList.contains("select-menu")) {
      e.stopPropagation();
      closeAllMenus();

      const menuElement = e.target.nextElementSibling;
      if (menuElement && menuElement.classList.contains("menu")) {
        menuElement.classList.toggle("hidden");
      }
    }

    // ASSIGN ASSET
    if (e.target.classList.contains("assign-asset-btn")) {
      e.stopPropagation();
      openAssignAssetModal(e.target);
    }

    // DELETE INVENTORY
    if (e.target.classList.contains("delete-inventory-btn")) {
      e.stopPropagation();
      deleteInventory(e.target);
    }
  }

  // EDIT INVENTORY FUNCTION
  function editInventory(target) {
    const form = document.querySelector("form");
    if (!form) return;

    const manufacturerInput = form.querySelector(".input-manufacturer");
    const quantityInput = form.querySelector(".input-qty");
    const categorySelect = form.querySelector("select[name='category_id']");
    const inventoryIdInput = form.querySelector("#inventory-id");
    const submitButton = form.querySelector(".add-inventory-btn");

    if (
      !manufacturerInput ||
      !quantityInput ||
      !categorySelect ||
      !inventoryIdInput ||
      !submitButton
    ) {
      console.error("Required form elements not found");
      return;
    }

    // Populate values from data-attributes
    const id = target.dataset.id;
    const name = target.dataset.name;
    const qty = target.dataset.qty;
    const categoryId = target.dataset.categoryId;

    manufacturerInput.value = name || "";
    quantityInput.value = qty || "";
    categorySelect.value = categoryId || "";
    inventoryIdInput.value = id || "";

    submitButton.textContent = "Update Inventory";
    form.action = "/manage-hardware";
  }

  // DELETE INVENTORY FUNCTION
  function deleteInventory(target) {
    const inventoryId = target.getAttribute("data-id");
    const inventoryName = target.getAttribute("data-name");

    if (!inventoryId || !inventoryName) {
      console.error("Missing inventory data");
      return;
    }

    // Check if SweetAlert is available
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "warning",
        title: "Delete Inventory?",
        text: `Are you sure you want to delete "${inventoryName}"?`,
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Delete",
      }).then((result) => {
        if (result.isConfirmed) {
          performDelete(inventoryId, inventoryName, target);
        }
      });
    } else {
      // Fallback to native confirm
      if (confirm(`Are you sure you want to delete "${inventoryName}"?`)) {
        performDelete(inventoryId, inventoryName, target);
      }
    }
  }

  // PERFORM DELETE OPERATION
  function performDelete(inventoryId, inventoryName, target) {
    const formData = new FormData();
    formData.append("delete_inventory_id", inventoryId);

    fetch("/manage-hardware", {
      method: "POST",
      body: formData,
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error("Network response was not ok");
        }
        return res.text();
      })
      .then(() => {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "success",
            text: `"${inventoryName}" was deleted successfully!`,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            position: "top-end",
            toast: true,
          });
        }

        // Remove the row
        const row = target.closest("tr");
        if (row) {
          row.remove();
        }
      })
      .catch((error) => {
        console.error("Delete error:", error);
        if (typeof Swal !== "undefined") {
          Swal.fire("Error", "Could not delete the inventory.", "error");
        } else {
          alert("Could not delete the inventory.");
        }
      });
  }

  // OPEN ASSIGN ASSET MODAL
  // function openAssignAssetModal(target) {
  //   const modal = document.getElementById("assign-asset-modal");
  //   if (!modal) return;

  //   const itemNumberInput = document.getElementById("modal-item-number");
  //   const inventoryIdInput = document.getElementById("modal-inventory-id");
  //   const manufacturerInput = document.getElementById("modal-manufacturer");
  //   const categorySelect = document.getElementById("modal-category-select");
  //   const figureTitle = document.getElementById("figure-title");
  //   const modelChargerInput = document.getElementById("model-charger");
  //   const chargerSection = document.getElementById("laptop-charger-section");
  //   const chargerAssetNumberInput = document.getElementById(
  //     "charger-asset-number"
  //   );
  //   const assetNumberInput = document.getElementById("modal-asset-number");

  //   // Get data from button attributes
  //   const itemNumber = target.getAttribute("data-item-number");
  //   const inventoryId = target.getAttribute("data-id");
  //   const manufacturer = target.getAttribute("data-name");
  //   const categoryId = target.getAttribute("data-category-id");
  //   const categoryName = target.getAttribute("data-category-option");

  //   // Populate form fields
  //   if (itemNumberInput) itemNumberInput.value = itemNumber || "";
  //   if (inventoryIdInput) inventoryIdInput.value = inventoryId || "";
  //   if (manufacturerInput) manufacturerInput.value = manufacturer || "";
  //   if (modelChargerInput) modelChargerInput.value = manufacturer || "";

  //   // Format category name
  //   const formattedCategoryName = categoryName
  //     ? categoryName.charAt(0).toUpperCase() +
  //       categoryName.slice(1).toLowerCase()
  //     : "";

  //   // Setup asset photo preview
  //   const assetPhotoInput = document.getElementById("asset-photo");
  //   if (assetPhotoInput && !assetPhotoInput.dataset.listenerAdded) {
  //     assetPhotoInput.addEventListener("change", function (e) {
  //       const preview = document.getElementById("photo-preview");
  //       const file = e.target.files[0];

  //       if (file && preview) {
  //         preview.src = URL.createObjectURL(file);
  //         preview.classList.remove("hidden");
  //       } else if (preview) {
  //         preview.src = "";
  //         preview.classList.add("hidden");
  //       }
  //     });
  //     assetPhotoInput.dataset.listenerAdded = "true";
  //   }

  //   // Generate Asset Number based on category
  //   let categoryCode = "01"; // Default code
  //   let assetSuffix = "0001";

  //   if (categoryName) {
  //     const lowerCategoryName = categoryName.toLowerCase();
  //     switch (lowerCategoryName) {
  //       case "laptop":
  //         categoryCode = "01";
  //         break;
  //       case "laptop_charger":
  //         categoryCode = "01";
  //         assetSuffix = "0001C";
  //         break;
  //       case "desktop_monitor":
  //         categoryCode = "03";
  //         break;
  //       case "printer":
  //         categoryCode = "05";
  //         break;
  //     }
  //   }

  //   const assetNumber = `TRA-${categoryCode}-${assetSuffix}`;
  //   if (assetNumberInput) assetNumberInput.value = assetNumber;

  //   // Handle laptop charger section
  //   if (chargerSection && chargerAssetNumberInput) {
  //     if (categoryName && categoryName.toLowerCase() === "laptop") {
  //       chargerSection.classList.remove("hidden");
  //       chargerAssetNumberInput.value = assetNumber + "C";

  //       const assetTypeInput = document.getElementById("asset-type");
  //       if (assetTypeInput) assetTypeInput.value = "laptop-charger";
  //     } else {
  //       chargerSection.classList.add("hidden");
  //     }
  //   }

  //   if (categorySelect) categorySelect.value = formattedCategoryName;
  //   if (figureTitle)
  //     figureTitle.textContent = `${manufacturer || ""} ${
  //       categoryName ? categoryName.toLowerCase() : ""
  //     } info`;

  //   // Show modal
  //   modal.style.display = "flex";
  // }

  async function openAssignAssetModal(target) {
    const modal = document.getElementById("assign-asset-modal");
    if (!modal) return;

    const itemNumberInput = document.getElementById("modal-item-number");
    const inventoryIdInput = document.getElementById("modal-inventory-id");
    const manufacturerInput = document.getElementById("modal-manufacturer");
    const categorySelect = document.getElementById("modal-category-select");
    const figureTitle = document.getElementById("figure-title");
    const modelChargerInput = document.getElementById("model-charger");
    const chargerSection = document.getElementById("laptop-charger-section");
    const chargerAssetNumberInput = document.getElementById(
      "charger-asset-number"
    );
    const assetNumberInput = document.getElementById("modal-asset-number");

    // Get data from button attributes
    const itemNumber = target.getAttribute("data-item-number");
    const inventoryId = target.getAttribute("data-id");
    const manufacturer = target.getAttribute("data-name");
    const categoryId = target.getAttribute("data-category-id");
    const categoryName = target.getAttribute("data-category-option");

    // Populate form fields
    if (itemNumberInput) itemNumberInput.value = itemNumber || "";
    if (inventoryIdInput) inventoryIdInput.value = inventoryId || "";
    if (manufacturerInput) manufacturerInput.value = manufacturer || "";
    if (modelChargerInput) modelChargerInput.value = manufacturer || "";

    // Format category name
    const formattedCategoryName = categoryName
      ? categoryName.charAt(0).toUpperCase() +
        categoryName.slice(1).toLowerCase()
      : "";

    // Setup asset photo preview (same as before)
    const assetPhotoInput = document.getElementById("asset-photo");
    if (assetPhotoInput && !assetPhotoInput.dataset.listenerAdded) {
      assetPhotoInput.addEventListener("change", function (e) {
        const preview = document.getElementById("photo-preview");
        const file = e.target.files[0];

        if (file && preview) {
          preview.src = URL.createObjectURL(file);
          preview.classList.remove("hidden");
        } else if (preview) {
          preview.src = "";
          preview.classList.add("hidden");
        }
      });
      assetPhotoInput.dataset.listenerAdded = "true";
    }

    // 🔥 FETCH NEXT ASSET NUMBER FROM SERVER
    try {
      const response = await fetch(
        `/manage-hardware?get_next_asset_number=1&category_name=${encodeURIComponent(
          categoryName
        )}`
      );
      const data = await response.json();

      if (assetNumberInput) {
        assetNumberInput.value = data.asset_number;
      }

      // Handle laptop charger section
      if (chargerSection && chargerAssetNumberInput) {
        if (categoryName && categoryName.toLowerCase() === "laptop") {
          chargerSection.classList.remove("hidden");

          // Fetch next charger asset number
          const chargerResponse = await fetch(
            `/manage-hardware?get_next_asset_number=1&category_name=laptop_charger`
          );
          const chargerData = await chargerResponse.json();
          chargerAssetNumberInput.value = chargerData.asset_number;

          const assetTypeInput = document.getElementById("asset-type");
          if (assetTypeInput) assetTypeInput.value = "laptop-charger";
        } else {
          chargerSection.classList.add("hidden");
        }
      }
    } catch (error) {
      console.error("Error fetching asset number:", error);
      // Fallback to default if API fails
      if (assetNumberInput) assetNumberInput.value = "TRA-01-0001";
    }

    if (categorySelect) categorySelect.value = formattedCategoryName;
    if (figureTitle)
      figureTitle.textContent = `${manufacturer || ""} ${
        categoryName ? categoryName.toLowerCase() : ""
      } info`;

    // Show modal
    modal.style.display = "flex";
  }

  // CLOSE ALL MENUS
  function closeAllMenus() {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  }

  // CLOSE MENUS ON OUTSIDE CLICK
  document.addEventListener("click", closeAllMenus);

  // MODAL CLOSE FUNCTIONALITY
  const assignAssetModal = document.getElementById("assign-asset-modal");
  if (assignAssetModal) {
    assignAssetModal.addEventListener("click", function (e) {
      if (e.target.id === "assign-asset-modal") {
        e.currentTarget.style.display = "none";
      }
    });

    // Add close button functionality if needed
    const closeBtn = assignAssetModal.querySelector(".close-modal-btn");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        assignAssetModal.style.display = "none";
      });
    }
  }
});
