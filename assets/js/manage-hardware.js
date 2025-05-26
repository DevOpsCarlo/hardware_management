document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");
  const addInventory = document.querySelector(".add-inventory-btn");

  const menu = document.querySelectorAll(".menu");

  // ADD INVENTORY TAB
  addInventory.addEventListener("click", (e) => {
    let hasError = false;
    const inputBrand = document.querySelector(".input-manufacturer");
    const inputQty = document.querySelector(".input-qty");
    const brandError = document.querySelector(".brand-error");
    const qtyError = document.querySelector(".qty-error");
    const categorySelect = document.querySelector("select[name='category_id']");
    const categoryError = document.querySelector(".category-error");
    brandError.classList.add("hidden");
    categoryError.classList.add("hidden");

    if (inputBrand.value.trim() === "") {
      hasError = true;
      brandError.textContent = "Please enter a manufacturer.";
      brandError.classList.remove("hidden");
    }
    if (
      inputQty.value.trim() === "" ||
      isNaN(inputQty.value) ||
      inputQty.value <= 0
    ) {
      hasError = true;
      qtyError.textContent = "Please enter quantity.";
      qtyError.classList.remove("hidden");
    }
    if (categorySelect.value === "") {
      hasError = true;
      categoryError.textContent = "Please select a category.";
      categoryError.classList.remove("hidden");
    }

    if (hasError) {
      e.preventDefault();
    }
  });

  // INVENTORY DATA TAB
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

      if (tab === "count") {
        inventoryTable.classList.remove("hidden");
        detailedTable.classList.add("hidden");
      } else if (tab === "details") {
        detailedTable.classList.remove("hidden");
        inventoryTable.classList.add("hidden");
      }
    });
  });

  // CLICK HANDLING FOR ELIPSIS
  document
    .querySelector("#inventory-list-table")
    .addEventListener("click", function (e) {
      // Handle ellipsis toggle
      if (e.target.classList.contains("select-menu")) {
        e.stopPropagation();

        // Close all other menus
        document
          .querySelectorAll(".menu")
          .forEach((m) => m.classList.add("hidden"));

        // Toggle current
        const menuElement = e.target.nextElementSibling;
        if (menuElement && menuElement.classList.contains("menu")) {
          menuElement.classList.toggle("hidden");
        }
      }

      //EDIT INVENTORY
      if (e.target.classList.contains("edit-inventory-btn")) {
        e.stopPropagation();

        // Get form elements
        const form = document.querySelector("form");
        const manufacturerInput = form.querySelector(".input-manufacturer");
        const quantityInput = form.querySelector(".input-qty");
        const categorySelect = form.querySelector("select[name='category_id']");
        const inventoryIdInput = form.querySelector("#inventory-id");
        const submitButton = form.querySelector(".add-inventory-btn");

        // Populate values from data-attributes
        const id = e.target.dataset.id;
        const name = e.target.dataset.name;
        const qty = e.target.dataset.qty;
        const categoryId = e.target.dataset.categoryId;

        manufacturerInput.value = name;
        quantityInput.value = qty;
        categorySelect.value = categoryId;
        inventoryIdInput.value = id;

        submitButton.textContent = "Update Inventory";
        form.action = "/manage-hardware";
      }

      // Handle delete inventory
      if (e.target.classList.contains("delete-inventory-btn")) {
        e.stopPropagation();
        const inventoryId = e.target.getAttribute("data-id");
        const inventoryName = e.target.getAttribute("data-name");

        Swal.fire({
          icon: "warning",
          title: "Delete Category?",
          text: `Are you sure you want to delete "${inventoryName}"?`,
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Delete",
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("delete_inventory_id", inventoryId);

            fetch("/manage-hardware", {
              method: "POST",
              body: formData,
            })
              .then((res) => res.text())
              .then(() => {
                Swal.fire({
                  icon: "success",
                  text: `"${inventoryName}" was deleted successfully!`,
                  showConfirmButton: false,
                  timer: 2000,
                  timerProgressBar: true,
                  position: "top-end",
                  toast: true,
                });

                // Remove the row
                e.target.closest("tr").remove();
              })
              .catch(() => {
                Swal.fire("Error", "Could not delete the inventory.", "error");
              });
          }
        });
      }
    });

  document
    .querySelector("#detailed-list-table")
    .addEventListener("click", function (e) {
      // Handle ellipsis toggle
      if (e.target.classList.contains("select-menu")) {
        e.stopPropagation();

        // Close all other menus
        document
          .querySelectorAll(".menu")
          .forEach((m) => m.classList.add("hidden"));

        // Toggle current
        const menuElement = e.target.nextElementSibling;
        if (menuElement && menuElement.classList.contains("menu")) {
          menuElement.classList.toggle("hidden");
        }
      }

      //EDIT INVENTORY
      if (e.target.classList.contains("edit-inventory-btn")) {
        e.stopPropagation();

        // Get form elements
        const form = document.querySelector("form");
        const manufacturerInput = form.querySelector(".input-manufacturer");
        const quantityInput = form.querySelector(".input-qty");
        const categorySelect = form.querySelector("select[name='category_id']");
        const inventoryIdInput = form.querySelector("#inventory-id");
        const submitButton = form.querySelector(".add-inventory-btn");

        // Populate values from data-attributes
        const id = e.target.dataset.id;
        const name = e.target.dataset.name;
        const qty = e.target.dataset.qty;
        const categoryId = e.target.dataset.categoryId;

        manufacturerInput.value = name;
        quantityInput.value = qty;
        categorySelect.value = categoryId;
        inventoryIdInput.value = id;

        submitButton.textContent = "Update Inventory";
        form.action = "/manage-hardware";
      }

      // Handle delete inventory
      if (e.target.classList.contains("delete-inventory-btn")) {
        e.stopPropagation();
        const inventoryId = e.target.getAttribute("data-id");
        const inventoryName = e.target.getAttribute("data-name");

        Swal.fire({
          icon: "warning",
          title: "Delete Category?",
          text: `Are you sure you want to delete "${inventoryName}"?`,
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Delete",
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("delete_inventory_id", inventoryId);

            fetch("/manage-hardware", {
              method: "POST",
              body: formData,
            })
              .then((res) => res.text())
              .then(() => {
                Swal.fire({
                  icon: "success",
                  text: `"${inventoryName}" was deleted successfully!`,
                  showConfirmButton: false,
                  timer: 2000,
                  timerProgressBar: true,
                  position: "top-end",
                  toast: true,
                });

                // Remove the row
                e.target.closest("tr").remove();
              })
              .catch(() => {
                Swal.fire("Error", "Could not delete the inventory.", "error");
              });
          }
        });
      }
    });

  // ✅ CLOSE ALL MENUS ON OUTSIDE CLICK
  document.addEventListener("click", () => {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  });
});
