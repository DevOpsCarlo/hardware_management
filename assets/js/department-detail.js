
// document.addEventListener("DOMContentLoaded", function () {
//   const toggleModal = document.getElementById("toggle-modal");
//   const closeModal = document.getElementById("modal-cancel-btn");
//   const modal = document.getElementById("modal");
//   const modalAddBtn = document.getElementById("modal-add-btn");

//   // Modal toggle functionality
//   if (toggleModal) {
//     toggleModal.addEventListener("click", () => {
//       modal.classList.remove("hidden");
//     });
//   }

//   if (closeModal) {
//     closeModal.addEventListener("click", () => {
//       modal.classList.add("hidden");
//     });
//   }

//   // Close modal when clicking outside
//   if (modal) {
//     modal.addEventListener("click", (e) => {
//       if (e.target === modal) {
//         modal.classList.add("hidden");
//       }
//     });
//   }

//   // Close all dropdown menus
//   function closeAllMenus() {
//     document
//       .querySelectorAll(".menu")
//       .forEach((m) => m.classList.add("hidden"));
//   }

//   // Handle dropdown menu toggles
//   document.addEventListener("click", function (e) {
//     if (e.target.classList.contains("select-menu")) {
//       e.stopPropagation();
//       // Close all other menus
//       document.querySelectorAll(".menu").forEach((menu) => {
//         if (menu !== e.target.nextElementSibling) {
//           menu.classList.add("hidden");
//         }
//       });

//       // Toggle current menu
//       const menu = e.target.nextElementSibling;
//       if (menu && menu.classList.contains("menu")) {
//         menu.classList.toggle("hidden");
//       }
//     } else if (!e.target.closest(".menu")) {
//       // Close all menus when clicking outside
//       closeAllMenus();
//     }
//   });

//   // Handle department employee table actions
//   const departmentEmployeeTable = document.querySelector(
//     "#departmentEmployeeTable"
//   );
//   if (departmentEmployeeTable) {
//     departmentEmployeeTable.addEventListener("click", function (e) {
//       handleDepartmentEmployeeActions(e);
//     });
//   }

//   function handleDepartmentEmployeeActions(e) {
//     // Get current page URL parts for proper routing

//     const currentPath = window.location.pathname;
//     console.log(currentPath);
//     const pathParts = currentPath.split("/");
//     const branchName = pathParts[2]; // /branch/[branch_name]/[department_name]
//     const departmentName = pathParts[3];

//     // Get employee data from button attributes
//     const employeeId = e.target.getAttribute("data-employee-id");
//     const employeeName = e.target.getAttribute("data-employee-name");

//     // Delete button handler
//     if (e.target.classList.contains("delete-btn")) {
//       e.stopPropagation();

//       Swal.fire({
//         icon: "warning",
//         title: "Remove Employee?",
//         text: `Are you sure you want to remove "${employeeName}" from this department?`,
//         showCancelButton: true,
//         confirmButtonColor: "#d33",
//         cancelButtonColor: "#3085d6",
//         confirmButtonText: "Remove",
//         cancelButtonText: "Cancel",
//       }).then((result) => {
//         if (result.isConfirmed) {
//           const formData = new FormData();
//           formData.append("delete_employee_id", employeeId);

//           // Send request to current department page
//           fetch(currentPath, {
//             method: "POST",
//             body: formData,
//           })
//             .then((response) => {
//               if (response.ok) {
//                 Swal.fire({
//                   icon: "success",
//                   text: `"${employeeName}" removed successfully!`,
//                   showConfirmButton: false,
//                   timer: 2000,
//                   timerProgressBar: true,
//                   position: "top-end",
//                   toast: true,
//                 });

//                 // Remove the row from table
//                 e.target.closest("tr").remove();

//                 // Refresh the page after a short delay to update the data
//                 setTimeout(() => {
//                   window.location.reload();
//                 }, 1500);
//               } else {
//                 throw new Error("Server error");
//               }
//             })
//             .catch((error) => {
//               console.error("Error:", error);
//               Swal.fire({
//                 icon: "error",
//                 title: "Error",
//                 text: "Could not remove the employee. Please try again.",
//               });
//             });
//         }
//       });
//     }

//     // Edit button handler (if you want to implement editing)
//     if (e.target.classList.contains("edit-btn")) {
//       e.stopPropagation();

//       // For now, just show that edit functionality needs to be implemented
//       Swal.fire({
//         icon: "info",
//         title: "Edit Employee",
//         text: "Edit functionality will be implemented here",
//         confirmButtonText: "OK",
//       });

//       // TODO: Implement edit functionality
//       // You can add a modal for editing employee details here
//     }
//   }

//   // Handle "Add First Employee" buttons
//   const addFirstEmployeeButtons = document.querySelectorAll(
//     "#add-first-employee, #add-first-department"
//   );
//   addFirstEmployeeButtons.forEach((button) => {
//     button.addEventListener("click", () => {
//       if (modal) {
//         modal.classList.remove("hidden");
//       }
//     });
//   });

//   // Handle employee selection in modal
//   const employeeCheckboxes = document.querySelectorAll(".employee-checkbox");
//   const addEmployeeBtn = document.getElementById("modal-add-btn");

//   // Enable/disable submit button based on checkbox selection
//   function updateSubmitButton() {
//     const selectedCheckboxes = document.querySelectorAll(
//       ".employee-checkbox:checked"
//     );
//     if (addEmployeeBtn) {
//       addEmployeeBtn.disabled = selectedCheckboxes.length === 0;
//       addEmployeeBtn.classList.toggle(
//         "opacity-50",
//         selectedCheckboxes.length === 0
//       );
//     }
//   }

//   // Add event listeners to checkboxes
//   employeeCheckboxes.forEach((checkbox) => {
//     checkbox.addEventListener("change", updateSubmitButton);
//   });

//   // Initial check
//   updateSubmitButton();

//   // Initialize DataTables if available
//   //   if (typeof $ !== "undefined" && $.fn.DataTable) {
//   //     // Initialize main table
//   //     if (document.getElementById("departmentEmployeeTable")) {
//   //       $("#departmentEmployeeTable").DataTable({
//   //         responsive: true,
//   //         pageLength: 10,
//   //         language: {
//   //           search: "Search employees:",
//   //           lengthMenu: "Show _MENU_ employees per page",
//   //           info: "Showing _START_ to _END_ of _TOTAL_ employees",
//   //           paginate: {
//   //             previous: "Previous",
//   //             next: "Next",
//   //           },
//   //         },
//   //       });
//   //     }

//   //     // Initialize modal table
//   //     if (document.getElementById("addEmployeeTable")) {
//   //       $("#addEmployeeTable").DataTable({
//   //         responsive: true,
//   //         pageLength: 5,
//   //         searching: true,
//   //         paging: true,
//   //         language: {
//   //           search: "Search available employees:",
//   //           lengthMenu: "Show _MENU_ employees per page",
//   //           info: "Showing _START_ to _END_ of _TOTAL_ employees",
//   //         },
//   //       });
//   //     }
//   //   }
// });

document.addEventListener("DOMContentLoaded", function () {
  const toggleModal = document.getElementById("toggle-modal");
  const closeModal = document.getElementById("modal-cancel-btn");
  const modal = document.getElementById("modal");
  const modalAddBtn = document.getElementById("modal-add-btn");

  // Modal toggle functionality
  if (toggleModal) {
    toggleModal.addEventListener("click", () => {
      modal.classList.remove("hidden");
    });
  }

  if (closeModal) {
    closeModal.addEventListener("click", () => {
      modal.classList.add("hidden");
    });
  }

  // Close modal when clicking outside
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("hidden");
      }
    });
  }

  // Close all dropdown menus
  function closeAllMenus() {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  }

  // Handle dropdown menu toggles
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("select-menu")) {
      e.stopPropagation();
      // Close all other menus
      document.querySelectorAll(".menu").forEach((menu) => {
        if (menu !== e.target.nextElementSibling) {
          menu.classList.add("hidden");
        }
      });

      // Toggle current menu
      const menu = e.target.nextElementSibling;
      if (menu && menu.classList.contains("menu")) {
        menu.classList.toggle("hidden");
      }
    } else if (!e.target.closest(".menu")) {
      // Close all menus when clicking outside
      closeAllMenus();
    }
  });

  // Handle department employee table actions
  const departmentEmployeeTable = document.querySelector(
    "#departmentEmployeeTable"
  );
  if (departmentEmployeeTable) {
    departmentEmployeeTable.addEventListener("click", function (e) {
      handleDepartmentEmployeeActions(e);
    });
  }

  function handleDepartmentEmployeeActions(e) {
    // Get current page URL parts for proper routing

    const currentPath = window.location.pathname;
    console.log(currentPath);
    const pathParts = currentPath.split("/");
    const branchName = pathParts[2]; // /branch/[branch_name]/[department_name]
    const departmentName = pathParts[3];

    // Get employee data from button attributes
    const employeeId = e.target.getAttribute("data-employee-id");
    const employeeName = e.target.getAttribute("data-employee-name");

    // Delete button handler
    if (e.target.classList.contains("delete-btn")) {
      e.stopPropagation();

      Swal.fire({
        icon: "warning",
        title: "Remove Employee?",
        text: `Are you sure you want to remove "${employeeName}" from this department?`,
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Remove",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("delete_employee_id", employeeId);

          // Send request to current department page
          fetch(currentPath, {
            method: "POST",
            body: formData,
          })
            .then((response) => {
              if (response.ok) {
                Swal.fire({
                  icon: "success",
                  text: `"${employeeName}" removed successfully!`,
                  showConfirmButton: false,
                  timer: 2000,
                  timerProgressBar: true,
                  position: "top-end",
                  toast: true,
                });

                // Remove the row from table
                e.target.closest("tr").remove();

                // Refresh the page after a short delay to update the data
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                throw new Error("Server error");
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Could not remove the employee. Please try again.",
              });
            });
        }
      });
    }

    // Edit button handler (if you want to implement editing)
    if (e.target.classList.contains("edit-btn")) {
      e.stopPropagation();

      // For now, just show that edit functionality needs to be implemented
      Swal.fire({
        icon: "info",
        title: "Edit Employee",
        text: "Edit functionality will be implemented here",
        confirmButtonText: "OK",
      });

      // TODO: Implement edit functionality
      // You can add a modal for editing employee details here
    }
  }

  // Handle "Add First Employee" buttons
  const addFirstEmployeeButtons = document.querySelectorAll(
    "#add-first-employee, #add-first-department"
  );
  addFirstEmployeeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      if (modal) {
        modal.classList.remove("hidden");
      }
    });
  });

  // Handle employee selection in modal - FIX: Use event delegation for pagination
  const employeeModalForm = document.getElementById("employee-modal-form");
  
  if (employeeModalForm) {
    employeeModalForm.addEventListener("change", function(e) {
      if (e.target.classList.contains("employee-checkbox")) {
        updateSubmitButton();
      }
    });
  }

  // Enable/disable submit button based on checkbox selection
  function updateSubmitButton() {
    const selectedCheckboxes = document.querySelectorAll(
      ".employee-checkbox:checked"
    );
    if (modalAddBtn) {
      modalAddBtn.disabled = selectedCheckboxes.length === 0;
      modalAddBtn.classList.toggle(
        "opacity-50",
        selectedCheckboxes.length === 0
      );
    }
  }

  // Initial check
  updateSubmitButton();

  // Initialize DataTables if available
  // if (typeof $ !== "undefined" && $.fn.DataTable) {
  //   // Initialize main table
  //   if (document.getElementById("departmentEmployeeTable")) {
  //     $("#departmentEmployeeTable").DataTable({
  //       responsive: true,
  //       pageLength: 10,
  //       language: {
  //         search: "Search employees:",
  //         lengthMenu: "Show _MENU_ employees per page",
  //         info: "Showing _START_ to _END_ of _TOTAL_ employees",
  //         paginate: {
  //           previous: "Previous",
  //           next: "Next",
  //         },
  //       },
  //     });
  //   }

    // Initialize modal table
  //   if (document.getElementById("addEmployeeTable")) {
  //     $("#addEmployeeTable").DataTable({
  //       responsive: true,
  //       pageLength: 5,
  //       searching: true,
  //       paging: true,
  //       language: {
  //         search: "Search available employees:",
  //         lengthMenu: "Show _MENU_ employees per page",
  //         info: "Showing _START_ to _END_ of _TOTAL_ employees",
  //       },
  //     });
  //   }
  // }
});

// Helper function to get URL parameters
// function getUrlParameter(name) {
//   name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
//   const regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
//   const results = regex.exec(location.search);
//   return results === null
//     ? ""
//     : decodeURIComponent(results[1].replace(/\+/g, " "));
// }
