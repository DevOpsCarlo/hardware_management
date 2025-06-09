"use strict";

document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");
  const addInventory = document.querySelector(".add-inventory-btn");
  const formAssignBtn = document.getElementById("form-assign-asset");

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

    // DELETE ASSET
    if (e.target.classList.contaienr("delete-asset-btn")) {
      e.stopPropagation();
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

  function deleteAsset(target) {
    const inventoryId = target.getAttribute("data-id");
    const assetId = target.getAttribute("data-asset-id");
    console.log("inventory id:", inventoryId);
    console.log("asset id: ", assetId);
    if (!inventoryId || !assetId) {
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
  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
  const chargerSerialInput = document.getElementById("charger-serial-number");
  const chargerConditionSelect = document.querySelector(
    'select[name="charger-condition"]'
  );
  function clearChargerFields() {
    if (chargerSerialInput) chargerSerialInput.value = "";
    if (chargerConditionSelect) chargerConditionSelect.value = "New";
  }
  // OPEN ASSIGN ASSET MODAL

  async function openAssignAssetModal(target) {
    const modal = document.getElementById("assign-asset-modal");
    if (!modal) return;
    const chargerModelInput = document.getElementById("model-charger");

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
    const assetIdInput = document.getElementById("asset-id");
    const chargerIdInput = document.getElementById("charger-id");

    const assetNumberInput = document.getElementById("modal-asset-number");
    const inputModel = document.getElementById("input-model");
    const inputSerialNumber = document.getElementById("input-serial-number");
    const inputIpAddress = document.getElementById("input-ip-address");
    const inputDatePurchase = document.getElementById("date-purchase");
    const inputWarrantyDate = document.getElementById("warranty-date");
    const selectStatus = document.getElementById("select-status");
    const selectConditions = document.getElementById("select-conditions");
    // const assetId = document.getElementById("asset-id");
    // Get data from button attributes
    const itemNumber = target.getAttribute("data-item-number");
    const inventoryId = target.getAttribute("data-id");
    const manufacturer = target.getAttribute("data-name");
    const categoryId = target.getAttribute("data-category-id");
    const categoryName = target.getAttribute("data-category-option");
    const assetNumber = target.getAttribute("data-asset-number");
    const model = target.getAttribute("data-model");
    const serialNumber = target.getAttribute("data-serial-number");
    const ipAddress = target.getAttribute("data-ip-address");
    const status = target.getAttribute("data-status");
    const conditions = target.getAttribute("data-conditions");
    const dateOfPurchase = target.getAttribute("data-dateof-purchase");
    const warrantyDate = target.getAttribute("data-warranty-date");
    const assetId = target.getAttribute("data-asset-id");
    // Get charger data from button attributes
    const chargerId = target.getAttribute("data-charger-id");
    const chargerAssetNumber = target.getAttribute("data-charger-asset-number");
    const chargerModel = target.getAttribute("data-charger-model");
    const chargerSerialNumber = target.getAttribute(
      "data-charger-serial-number"
    );
    const chargerConditions = target.getAttribute("data-charger-conditions");
    const chargerStatus = target.getAttribute("data-charger-status");

    if (assetId != 0) {
      formAssignBtn.textContent = "Update Asset";
    } else {
      formAssignBtn.textContent = "Assign Asset";
    }
    // Populate form fields
    if (assetIdInput) assetIdInput.value = assetId || "";
    if (chargerIdInput) chargerIdInput.value = chargerId || "";
    if (itemNumberInput) itemNumberInput.value = itemNumber || "";
    if (inventoryIdInput) inventoryIdInput.value = inventoryId || "";
    if (manufacturerInput) manufacturerInput.value = manufacturer || "";
    if (modelChargerInput) modelChargerInput.value = manufacturer || "";
    if (inputModel) inputModel.value = capitalizeFirstLetter(model) || "";
    if (inputSerialNumber)
      inputSerialNumber.value = serialNumber.toUpperCase() || "";
    if (inputIpAddress) inputIpAddress.value = ipAddress || "";
    // if (assetNumberInput) assetNumberInput.value = assetNumber || "";
    if (inputDatePurchase) inputDatePurchase.value = dateOfPurchase || "";
    if (inputWarrantyDate) inputWarrantyDate.value = warrantyDate || "";
    if (selectStatus) selectStatus.value = status || "Available";
    if (selectConditions) selectConditions.value = conditions || "New";

    // Format category name
    const formattedCategoryName = categoryName
      ? categoryName.charAt(0).toUpperCase() +
        categoryName.slice(1).toLowerCase()
      : "";

    // Setup asset photo preview (same as before)
    const assetPhotoInput = document.getElementById("asset-photo");
    const photoPreview = document.getElementById("photo-preview");
    // 📸 Load existing asset photo from button's data attribute
    const existingPhotoUrl = target.getAttribute("data-asset-photo");
    if (photoPreview) {
      if (existingPhotoUrl) {
        photoPreview.src = `/${existingPhotoUrl}`;

        photoPreview.classList.remove("hidden");
      } else {
        photoPreview.src = "";
        photoPreview.classList.add("hidden");
      }
    }

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
    const existingAssetNumber = target.getAttribute("data-asset-number");
    // if (chargerSection) {
    if (categoryName && categoryName.toLowerCase() === "laptop") {
      chargerSection.classList.remove("hidden");

      // Populate charger fields if data exists
      if (chargerId) {
        if (chargerAssetNumberInput)
          chargerAssetNumberInput.value = chargerAssetNumber;

        if (chargerModelInput) chargerModelInput.value = manufacturer;
        if (chargerSerialInput) chargerSerialInput.value = chargerSerialNumber;

        if (chargerConditionSelect)
          chargerConditionSelect.value = chargerConditions;
      } else {
        // Creating new laptop - fetch next charger asset number
        try {
          const chargerResponse = await fetch(
            `/manage-hardware/inventory-list?get_next_asset_number=1&category_name=${encodeURIComponent(
              "laptop charger"
            )}`
          );
          const chargerData = await chargerResponse.json();
          if (chargerAssetNumberInput)
            chargerAssetNumberInput.value = chargerData.asset_number;
        } catch (error) {
          console.error("Error fetching charger asset number:", error);
        }
      }

      const assetTypeInput = document.getElementById("asset-type");
      if (assetTypeInput) assetTypeInput.value = "laptop charger";
    } else {
      chargerSection.classList.add("hidden");
    }
    // }
    if (existingAssetNumber) {
      // Use existing asset number from row data
      if (assetNumberInput) {
        assetNumberInput.value = existingAssetNumber;
      }
    } else {
      //  FETCH NEXT ASSET NUMBER FROM SERVER
      clearChargerFields();
      try {
        const response = await fetch(
          `/manage-hardware/inventory-list?get_next_asset_number=1&category_name=${encodeURIComponent(
            categoryName
          )}`
        );
        const data = await response.json();

        if (assetNumberInput) {
          assetNumberInput.value = data.asset_number;
        }
      } catch (error) {
        console.error("Error fetching asset number:", error);
        // Fallback to default if API fails
        if (assetNumberInput) assetNumberInput.value = "TRA-01-0001";
      }
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

  formAssignBtn.addEventListener("click", (e) => {
    let hasError = false;
    const inputModel = document.getElementById("input-model");
    const inputSerialNumber = document.getElementById("input-serial-number");
    let hasErrorText = document.getElementById("has-error");

    if (inputModel.value.trim() === "") {
      hasError = true;
      hasErrorText.textContent = "Please fill all the required fields *";
      inputSerialNumber.classList.add("bg-red-50");
    }
    if (inputSerialNumber.value.trim() === "") {
      hasError = true;
      hasErrorText.textContent = "Please fill all the required fields *";
      inputSerialNumber.classList.add("bg-red-50");
    }
    if (hasError) {
      e.preventDefault();
    }
  });
});
