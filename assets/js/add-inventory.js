"use strict";

document.addEventListener("DOMContentLoaded", function () {
  // Function for edit, delete
  function populateEditForm(inventoryData) {
    document.getElementById("form-title").textContent = "Edit Inventory";
    document.getElementById("submit-btn").textContent = "Update Inventory";
    document.getElementById("inventory-form-modal").classList.remove("hidden");
    // Populate form fields
    document.getElementById("inventory-id").value = inventoryData.id;
    document.getElementById("category-id").value = inventoryData.category_id;
    document.getElementById("input-manufacturer").value =
      inventoryData.manufacturer;
    document.getElementById("input-model").value = inventoryData.model;
    document.getElementById("purchase-date").value =
      inventoryData.purchase_date;
    document.getElementById("input-qty").value = inventoryData.quantity;
    document.getElementById("input-warranty").value =
      inventoryData.warranty_years;

    // Show current photo if exists
    if (inventoryData.photo) {
      document.getElementById("current-photo").classList.remove("hidden");
      document.getElementById("current-photo-img").src = inventoryData.photo;
    }
  }

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

  function performDelete(inventoryId, inventoryName, target) {
    const formData = new FormData();
    formData.append("delete_inventory_id", inventoryId);

    fetch("/manage-hardware/add-inventory", {
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

  const inventoryTable = document.getElementById("inventory-list-table");
  if (inventoryTable) {
    inventoryTable.addEventListener("click", function (e) {
      handleTableActions(e);
    });
  }

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
      closeAllMenus();

      // Get inventory data from data attributes
      const inventoryData = {
        id: e.target.dataset.id || e.target.getAttribute("data-id"),
        category_id:
          e.target.dataset.categoryId ||
          e.target.getAttribute("data-category-id"),
        manufacturer:
          e.target.dataset.manufacturer ||
          e.target.getAttribute("data-manufacturer"),
        model: e.target.dataset.model || e.target.getAttribute("data-model"),
        purchase_date:
          e.target.dataset.purchaseDate ||
          e.target.getAttribute("data-purchase-date"),
        quantity:
          e.target.dataset.quantity || e.target.getAttribute("data-quantity"),
        warranty_years:
          e.target.dataset.warrantyYears ||
          e.target.getAttribute("data-warranty-years"),
        photo: e.target.dataset.photo || e.target.getAttribute("data-photo"),
      };

      console.log("Inventory data:", inventoryData); // Debug log
      populateEditForm(inventoryData);
    }

    // Delete inventory
    if (e.target.classList.contains("delete-inventory-btn")) {
      e.stopPropagation();
      deleteInventory(e.target);
    }
  }

  // CLOSE ALL MENUS
  function closeAllMenus() {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  }

  // CLOSE MENUS ON OUTSIDE CLICK
  document.addEventListener("click", closeAllMenus);

  const toggleInventoryForm = document.getElementById(
    "toggle-add-inventory-form"
  );
  const inventoryModalForm = document.getElementById("inventory-form-modal");

  // Open inventory form
  toggleInventoryForm.addEventListener("click", () => {
    inventoryModalForm.classList.toggle("hidden");
  });

  // Close inventory form when click outside the form
  inventoryModalForm.addEventListener("click", (e) => {
    const inventoryForm = document.getElementById("inventory-form");
    if (!inventoryForm.contains(e.target)) {
      inventoryModalForm.classList.toggle("hidden");
    }
  });
});
