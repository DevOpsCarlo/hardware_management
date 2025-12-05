document.addEventListener("DOMContentLoaded", function () {
  const toggleModal = document.getElementById("toggle-modal");
  const closeModal = document.getElementById("modal-cancel-btn");
  const modal = document.getElementById("modal");
  const modalAddBtn = document.getElementById("modal-add-btn");

  toggleModal.addEventListener("click", () => {
    modal.classList.remove("hidden");
    const inputBranch = document.getElementById("input-branch");
    inputBranch.focus();
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
  });

  modalAddBtn.addEventListener("click", (e) => {
    const inputBranch = document.getElementById("input-branch");
    const inputValidation = document.getElementById("input-validation");
    inputValidation.classList.add("hidden");
    let hasError = false;

    if (inputBranch.value.trim() === "") {
      hasError = true;
      inputValidation.textContent = "Please enter branch name";
      inputValidation.classList.remove("hidden");
    }
    if (hasError) {
      e.preventDefault();
    }
  });

  function closeAllMenus() {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  }

  function handleBranchTableActions(e) {
    // Handle ellipsis toggle
    if (e.target.classList.contains("select-menu")) {
      e.stopPropagation();
      closeAllMenus();

      const menuElement = e.target.nextElementSibling;
      if (menuElement && menuElement.classList.contains("menu")) {
        menuElement.classList.toggle("hidden");
      }
    }
  }

  const branchTable = document.querySelector("#branch-table");
  if (branchTable) {
    branchTable.addEventListener("click", function (e) {
      handleBranchTableActions(e);
    });
  }
});

// Handle menu toggles
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("select-menu")) {
    // Close all other menus
    document.querySelectorAll(".menu").forEach((menu) => {
      if (menu !== e.target.nextElementSibling) {
        menu.classList.add("hidden");
      }
    });

    // Toggle current menu
    const menu = e.target.nextElementSibling;
    menu.classList.toggle("hidden");
  } else if (!e.target.closest(".menu")) {
    // Close all menus when clicking outside
    document.querySelectorAll(".menu").forEach((menu) => {
      menu.classList.add("hidden");
    });
  }
});

document.querySelector("#branchTable").addEventListener("click", function (e) {
  // fetch data when click
  const branchId = e.target.getAttribute("data-branch-id");
  const branchName = e.target.getAttribute("data-branch-name");

  const modalTitle = document.getElementById("modal-title");
  const modal = document.getElementById("modal");
  const modalAddBtn = document.getElementById("modal-add-btn");
  const modalForm = document.getElementById("modal-form");
  const inputBranchId = document.getElementById("branch-id");
  const inputBranch = document.getElementById("input-branch");

  // Edit button
  if (e.target.classList.contains("edit-btn")) {
    e.stopPropagation();

    modal.classList.remove("hidden");
    modalTitle.textContent = "Edit Branch";
    modalAddBtn.textContent = "Update Branch";
    modalForm.action = "/branch";
    inputBranch.value = branchName;
    inputBranchManager.value = branchManager;
    inputBranchId.value = branchId;
  }

  // Delete button
  if (e.target.classList.contains("delete-btn")) {
    e.stopPropagation();

    Swal.fire({
      icon: "warning",
      title: "Delete Category?",
      text: `Are you sure you want to delete "${branchName}"?`,
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Delete",
    }).then((result) => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append("delete_branch_id", branchId);

        fetch("/branch", {
          method: "POST",
          body: formData,
        })
          .then((res) => res.text())
          .then(() => {
            Swal.fire({
              icon: "success",
              text: `"${branchName}" was deleted successfully!`,
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

document.querySelector("#branchTable").addEventListener("click", function (e) {
  // fetch data when click
  const branchId = e.target.getAttribute("data-branch-id");
  const branchName = e.target.getAttribute("data-branch-name");
  const clickedRow = e.target.closest("tr");
  if (
    clickedRow &&
    !e.target.classList.contains("edit-btn") &&
    !e.target.classList.contains("delete-btn") &&
    !e.target.classList.contains("select-menu") &&
    !e.target.closest(".menu")
  ) {
    const branchNameCell = clickedRow.querySelector("td:nth-child(1)");
    if (branchNameCell) {
      const branchNameText = branchNameCell.textContent.trim();
      if (branchNameText && branchNameText !== "Empty") {
        window.location.href = `/branch/${encodeURIComponent(branchNameText)}`;
      }
    }
    return; 
  }

    });

  