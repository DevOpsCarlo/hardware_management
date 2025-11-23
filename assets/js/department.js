document.addEventListener("DOMContentLoaded", function () {
  const departmentModal = document.getElementById("department-modal");
  const toggleDepartmentModal = document.getElementById(
    "toggle-department-modal"
  );
  const addFirstDepartment = document.getElementById("add-first-department");
  const departmentModalCancelBtn = document.getElementById(
    "department-modal-cancel-btn"
  );
  const departmentModalForm = document.getElementById("department-modal-form");
  const departmentModalTitle = document.getElementById(
    "department-modal-title"
  );
  const departmentModalAddBtn = document.getElementById(
    "department-modal-add-btn"
  );

  // Form fields
  const departmentIdField = document.getElementById("department-id");
  const inputDepartment = document.getElementById("input-department");
  const inputDepartmentHead = document.getElementById("input-department-head");

  // Open modal for adding new department
  function openAddDepartmentModal() {
    departmentModalTitle.textContent = "Add New Department";
    departmentModalAddBtn.textContent = "Add Department";
    departmentIdField.value = "0";
    inputDepartment.value = "";
    inputDepartmentHead.value = "";
    departmentModal.classList.remove("hidden");
  }

  // Close modal
  function closeDepartmentModal() {
    departmentModal.classList.add("hidden");
  }

  // Event listeners for opening modal
  if (toggleDepartmentModal) {
    toggleDepartmentModal.addEventListener("click", openAddDepartmentModal);
  }

  if (addFirstDepartment) {
    addFirstDepartment.addEventListener("click", openAddDepartmentModal);
  }

  // Event listener for closing modal
  if (departmentModalCancelBtn) {
    departmentModalCancelBtn.addEventListener("click", closeDepartmentModal);
  }

  // Close modal when clicking outside
  departmentModal.addEventListener("click", function (e) {
    if (e.target === departmentModal) {
      closeDepartmentModal();
    }
  });

  // Department menu functionality
  const departmentMenuTriggers = document.querySelectorAll(
    ".department-menu-trigger"
  );

  departmentMenuTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      const departmentId = this.dataset.departmentId;
      const menu = document.getElementById(`department-menu-${departmentId}`);

      // Close all other menus
      document.querySelectorAll(".department-menu").forEach((m) => {
        if (m !== menu) {
          m.classList.add("hidden");
        }
      });

      // Toggle current menu
      menu.classList.toggle("hidden");
    });
  });

  // Close menus when clicking outside
  document.addEventListener("click", function () {
    document.querySelectorAll(".department-menu").forEach((menu) => {
      menu.classList.add("hidden");
    });
  });

  // Edit department functionality
  const editDepartmentBtns = document.querySelectorAll(".edit-department-btn");

  editDepartmentBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const departmentId = this.dataset.departmentId;
      const departmentName = this.dataset.departmentName;
      const departmentHead = this.dataset.departmentHead;

      // Populate modal with current values
      departmentModalTitle.textContent = "Edit Department";
      departmentModalAddBtn.textContent = "Update Department";
      departmentIdField.value = departmentId;
      inputDepartment.value = departmentName;
      inputDepartmentHead.value = departmentHead;
       

      // Show modal
      departmentModal.classList.remove("hidden");

      // Close the menu
      document
        .getElementById(`department-menu-${departmentId}`)
        .classList.add("hidden");
    });
  });

  // Delete department functionality
  const deleteDepartmentBtns = document.querySelectorAll(
    ".delete-department-btn"
  );

  deleteDepartmentBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const departmentId = this.dataset.departmentId;
      const departmentName = this.dataset.departmentName;
      // const departmentHead = this.dataset.departmentHead;

      // Close the menu
      document
        .getElementById(`department-menu-${departmentId}`)
        .classList.add("hidden");

      // Show confirmation dialog
      Swal.fire({
        title: "Are you sure?",
        text: `Do you want to delete the "${departmentName}" department?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Delete",
      }).then((result) => {
        if (result.isConfirmed) {
          // Create a form to submit the deletion
          const form = document.createElement("form");
          form.method = "POST";
          form.style.display = "none";

          const input = document.createElement("input");
          input.type = "hidden";
          input.name = "delete_department_id";
          input.value = departmentId;

          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });

  // Form validation
  if (departmentModalForm) {
    departmentModalForm.addEventListener("submit", function (e) {
      const departmentName = inputDepartment.value.trim();

      if (!departmentName) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "Validation Error",
          text: "Department name is required.",
          timer: 3000,
        });
        inputDepartment.focus();
      }
    });
  }
});
