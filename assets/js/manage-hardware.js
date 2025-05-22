const tabButtons = document.querySelectorAll(".tab-button");
const tabContents = document.querySelectorAll(".tab-content");
const addInventory = document.querySelector(".add-inventory-btn");
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
  });
});

addInventory.addEventListener("click", (e) => {
  let hasError = false;
  const inputBrand = document.querySelector(".input-brand");
  const inputQty = document.querySelector(".input-qty");
  const brandError = document.querySelector(".brand-error");
  const qtyError = document.querySelector(".qty-error");
  brandError.classList.add("hidden");

  if (inputBrand.value.trim() === "") {
    hasError = true;
    brandError.textContent = "Please enter a brand name.";
    brandError.classList.remove("hidden");
  }
  if (inputQty.value.trim() === "") {
    hasError = true;
    qtyError.textContent = "Please enter quantity.";
    qtyError.classList.remove("hidden");
  }

  if (hasError) {
    e.preventDefault();
  }
});
