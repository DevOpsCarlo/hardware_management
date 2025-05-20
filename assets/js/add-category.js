document.addEventListener("DOMContentLoaded", function () {
  const addCategory = document.getElementById("add-category-btn");
  const addBtn = document.getElementById("add-btn");
  const addCategoryModal = document.getElementById("add-category-modal");

  addCategory.addEventListener("click", () => {
    addCategoryModal.classList.toggle("hidden");
  });

  addCategoryModal.addEventListener("click", (e) => {
    const categoryModalBox = document.getElementById("category-modal-box");
    if (!categoryModalBox.contains(e.target)) {
      addCategoryModal.classList.toggle("hidden");
    }
  });

  addBtn.addEventListener("click", (e) => {
    const inputCategoryName = document.getElementById("input-category-name");
    const isEmptyCategoryName = inputCategoryName.value.trim() === "";
    const errorMessage = document.querySelector(".error-message");
    errorMessage.classList.add("hidden");
    let hasError = false;

    if (isEmptyCategoryName) {
      hasError = true;
      errorMessage.textContent = "⚠️ Please enter a category name. ";
      errorMessage.classList.remove("hidden");
    }
    if (hasError) {
      e.preventDefault();
    }
  });
});
