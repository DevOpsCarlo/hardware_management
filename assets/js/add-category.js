document.addEventListener("DOMContentLoaded", function () {
  const addCategory = document.getElementById("add-category-btn");
  const addBtn = document.getElementById("add-btn");
  const categoryModal = document.getElementById("category-modal");
  const selectedOption = document.querySelectorAll(".select-option");
  const options = document.querySelectorAll(".options");
  const editCategory = document.querySelectorAll(".edit-category-btn");
  const modalTitle = document.querySelector(".modal-title");
  const categoryForm = document.getElementById("category-form");
  const inputCategoryName = document.getElementById("input-category-name");
  const categoryIdInput = document.getElementById("category-id");
  const deleteCategory = document.querySelectorAll(".delete-category-btn");

  // add category button to show modal
  addCategory.addEventListener("click", () => {
    categoryModal.classList.toggle("hidden");
    modalTitle.textContent = "Add Category";
    categoryForm.action = "/add-category";
    addBtn.textContent = "Add Category";
    inputCategoryName.value = "";
    categoryIdInput.value = "";
  });

  // Category modal container
  categoryModal.addEventListener("click", (e) => {
    const categoryModalBox = document.getElementById("category-modal-box");
    if (!categoryModalBox.contains(e.target)) {
      categoryModal.classList.toggle("hidden");
    }
  });

  // add button in category modal form
  addBtn.addEventListener("click", (e) => {
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

  // Elipsis menu
  // for (let i = 0; i < selectedOption.length; i++) {
  //   selectedOption[i].addEventListener("click", (e) => {
  //     e.stopPropagation();
  //     console.log(i);
  //     // Close all other options first
  //     for (let j = 0; j < selectedOption.length; j++) {
  //       if (i !== j) {
  //         // Check if it's not the current one
  //         options[j].classList.add("hidden");
  //       }
  //     }

  //     options[i].classList.toggle("hidden");
  //   });
  // }

  document
    .querySelector("#categoryTable")
    .addEventListener("click", function (e) {
      if (e.target.classList.contains("select-option")) {
        e.stopPropagation();

        const selectedIcon = e.target;

        // Close all other options
        options.forEach((opt) => opt.classList.add("hidden"));

        // Toggle the options for the clicked one
        const optionsMenu = selectedIcon.nextElementSibling;
        if (optionsMenu && optionsMenu.classList.contains("options")) {
          optionsMenu.classList.toggle("hidden");
        }
      }

      // Edit button
      if (e.target.classList.contains("edit-category-btn")) {
        e.stopPropagation();
        const categoryId = e.target.getAttribute("data-id");
        const categoryName = e.target.getAttribute("data-name");

        categoryModal.classList.remove("hidden");
        modalTitle.textContent = "Edit Category";
        addBtn.textContent = "Update Category";
        categoryForm.action = "/add-category";
        inputCategoryName.value = categoryName;
        categoryIdInput.value = categoryId;
      }

      // Delete button
      if (e.target.classList.contains("delete-category-btn")) {
        e.stopPropagation();
        const categoryId = e.target.getAttribute("data-id");
        const categoryName = e.target.getAttribute("data-name");

        Swal.fire({
          icon: "warning",
          title: "Delete Category?",
          text: `Are you sure you want to delete "${categoryName}"?`,
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Delete",
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("delete_category_id", categoryId);

            fetch("/add-category", {
              method: "POST",
              body: formData,
            })
              .then((res) => res.text())
              .then(() => {
                Swal.fire({
                  icon: "success",
                  text: `"${categoryName}" was deleted successfully!`,
                  showConfirmButton: false,
                  timer: 2000,
                  timerProgressBar: true,
                  position: "top-end",
                  toast: true,
                });

                // Remove row
                e.target.closest("tr").remove();
              })
              .catch(() => {
                Swal.fire("Error", "Could not delete the category.", "error");
              });
          }
        });
      }
    });

  // EDIT CATEGORY BTN
  // for (let i = 0; i < editCategory.length; i++) {
  //   editCategory[i].addEventListener("click", (e) => {
  //     const categoryId = e.target.getAttribute("data-id");
  //     const categoryName = e.target.getAttribute("data-name");

  //     categoryModal.classList.toggle("hidden");
  //     modalTitle.textContent = "Edit Category";
  //     addBtn.textContent = "Update Category";
  //     categoryForm.action = "/add-category";
  //     inputCategoryName.value = categoryName;
  //     categoryIdInput.value = categoryId;
  //   });
  // }

  // DELETE CATEGORY BTN

  // document.querySelectorAll(".delete-category-btn").forEach((btn) => {
  //   btn.addEventListener("click", (e) => {
  //     const categoryId = btn.getAttribute("data-id");
  //     const categoryName = btn.getAttribute("data-name");

  //     Swal.fire({
  //       icon: "warning",
  //       title: "Delete Category?",
  //       text: `Are you sure you want to delete "${categoryName}"?`,
  //       showCancelButton: true,
  //       confirmButtonColor: "#d33",
  //       cancelButtonColor: "#3085d6",
  //       confirmButtonText: "Delete",
  //     }).then((result) => {
  //       if (result.isConfirmed) {
  //         const formData = new FormData();
  //         formData.append("delete_category_id", categoryId);

  //         fetch("/add-category", {
  //           method: "POST",
  //           body: formData,
  //         })
  //           .then((res) => res.text())
  //           .then(() => {
  //             Swal.fire({
  //               icon: "success",
  //               text: `"${categoryName}" was deleted successfully!`,
  //               showConfirmButton: false,
  //               timer: 2000,
  //               timerProgressBar: true,
  //               position: "top-end",
  //               toast: true,
  //             });
  //             // Remove row from DOM
  //             btn.closest("tr").remove();
  //           })
  //           .catch(() => {
  //             Swal.fire("Error", "Could not delete the category.", "error");
  //           });
  //       }
  //     });
  //   });
  // });

  // CLOSE ALL MENUS ELIPSIS
  document.addEventListener("click", () => {
    // Close all menus
    for (let j = 0; j < selectedOption.length; j++) {
      options[j].classList.add("hidden");
    }
  });
});
