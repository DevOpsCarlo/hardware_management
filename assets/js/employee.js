document.addEventListener("DOMContentLoaded", function () {
  const toggleModal = document.getElementById("toggle-modal");
  const closeModal = document.getElementById("modal-cancel-btn");
  const modal = document.getElementById("modal");
  const modalAddBtn = document.getElementById("modal-add-btn");

  toggleModal.addEventListener("click", () => {
    modal.classList.remove("hidden");
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
  });

  modalAddBtn.addEventListener("click", (e) => {
    const inputEmployeeName = document.getElementById("input-employee-name");
    const inputValidation = document.getElementById("input-validation");
    inputValidation.classList.add("hidden");
    let hasError = false;

    if (inputEmployeeName.value.trim() === "") {
      hasError = true;
      inputValidation.textContent = "Please enter employee name";
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

  function handleEmployeeTableActions(e) {
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

  const employeeTable = document.querySelector("#employee-table");
  if (employeeTable) {
    employeeTable.addEventListener("click", function (e) {
      handleEmployeeTableActions(e);
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

document
  .querySelector("#employeeTable")
  .addEventListener("click", function (e) {
    // fetch data when click
    const id = e.target.getAttribute("data-id");
    const employeeName = e.target.getAttribute("data-employee-name");
    const employeeId = e.target.getAttribute("data-employee-id");
    const selectedStatus = e.target.getAttribute("data-option-status");

    const modalTitle = document.getElementById("modal-title");
    const modal = document.getElementById("modal");
    const modalAddBtn = document.getElementById("modal-add-btn");
    const modalForm = document.getElementById("modal-form");
    const inputId = document.getElementById("id");
    const inputEmployeeName = document.getElementById("input-employee-name");
    const inputEmployeeId = document.getElementById("input-employee-id");
    const optionStatus = document.getElementById("option-status");

    // Edit button
    if (e.target.classList.contains("edit-btn")) {
      e.stopPropagation();

      modal.classList.remove("hidden");
      modalTitle.textContent = "Edit Employee";
      modalAddBtn.textContent = "Update Employee";
      modalForm.action = "/employee";
      inputEmployeeName.value = employeeName;
      inputEmployeeId.value = employeeId;
      optionStatus.value = selectedStatus;
      inputId.value = id;
    }

    // Delete button
    if (e.target.classList.contains("delete-btn")) {
      e.stopPropagation();

      Swal.fire({
        icon: "warning",
        title: "Delete Category?",
        text: `Are you sure you want to delete "${employeeName}"?`,
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Delete",
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("delete_employee_id", id);

          fetch("/employee", {
            method: "POST",
            body: formData,
          })
            .then((res) => res.text())
            .then(() => {
              Swal.fire({
                icon: "success",
                text: `"${employeeName}" was deleted successfully!`,
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
