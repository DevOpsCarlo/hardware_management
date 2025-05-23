document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");
  const addInventory = document.querySelector(".add-inventory-btn");
  const selectedMenu = document.querySelectorAll(".select-menu");
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

  // ELIPSIS MENU - ADDEN INVENTORY TABLE
  for (let i = 0; i < selectedMenu.length; i++) {
    selectedMenu[i].addEventListener("click", (e) => {
      e.stopPropagation();
      // CLOSE OTHER SELECTED MENU FIRST
      for (let j = 0; j < selectedMenu.length; j++) {
        if (i !== j) {
          menu[j].classList.add("hidden");
        }
      }
      menu[i].classList.toggle("hidden");
    });
  }

  // DELETE INVENTORY ROW

  document.querySelectorAll(".delete-inventory-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const inventoryId = btn.getAttribute("data-id");
      const inventoryName = btn.getAttribute("data-name");

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
              // Remove row from DOM
              btn.closest("tr").remove();
            })
            .catch(() => {
              Swal.fire("Error", "Could not delete the inventory.", "error");
            });
        }
      });
    });
  });

  // CLOSE ALL MENUS ELIPSIS
  document.addEventListener("click", () => {
    // Close all menus
    for (let j = 0; j < selectedMenu.length; j++) {
      menu[j].classList.add("hidden");
    }
  });
});
