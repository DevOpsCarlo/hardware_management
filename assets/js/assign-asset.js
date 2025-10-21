// document.addEventListener("DOMContentLoaded", function () {
//   const headerCheckbox = document.getElementById("headerCheckbox");
//   const assetCheckboxes = document.querySelectorAll(".asset-checkbox");
//   const employeeSelect = document.getElementById("employeeSelect");
//   const assignBtn = document.getElementById("assign-btn");
//   const form = document.getElementById("assignAssetForm");
//   const closeModal = document.getElementById("cancel-btn");
//   const modal = document.getElementById("modal");
//   const toggleModal = document.getElementById("toggle-modal");

//   // Function to update toggle modal state
//   function updateToggleModalState() {
//     // Use a small timeout to ensure DOM is fully updated
//     setTimeout(() => {
//       const checkedAssets = document.querySelectorAll(
//         ".asset-checkbox:checked"
//       );
//       const hasAssignedAsset = Array.from(checkedAssets).some(
//         (asset) => asset.dataset.status === "Assigned"
//       );

//       console.log("Checked assets:", checkedAssets.length);
//       console.log("Has assigned asset:", hasAssignedAsset);

//       if (hasAssignedAsset) {
//         toggleModal.disabled = true;
//         toggleModal.classList.add("opacity-50", "cursor-not-allowed");
//         console.log("Toggle modal disabled");
//       } else {
//         toggleModal.disabled = false;
//         toggleModal.classList.remove("opacity-50", "cursor-not-allowed");
//         console.log("Toggle modal enabled");
//       }
//     }, 10);
//   }

//   closeModal.addEventListener("click", () => {
//     modal.classList.add("hidden");
//     const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
//     checkedAssets.forEach((checkbox) => {
//       checkbox.checked = false;
//     });

//     // Clear the selected assets container
//     const container = document.getElementById("selectedAssetsContainer");
//     container.innerHTML = "";

//     // Update all states
//     updateSelectAllState();
//     updateButtonStates();
//     updateToggleModalState();
//   });

//   toggleModal.addEventListener("click", () => {
//     const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
//     const container = document.getElementById("selectedAssetsContainer");
//     container.innerHTML = ""; // Clear previous selections

//     checkedAssets.forEach((asset) => {
//       const input = document.createElement("input");
//       input.type = "hidden";
//       input.name = "asset_ids[]";
//       input.value = asset.value;
//       container.appendChild(input);
//     });

//     // Show the modal
//     document.getElementById("modal").classList.remove("hidden");
//   });

//   // Handle individual checkbox changes with both click and change events
//   assetCheckboxes.forEach((checkbox) => {
//     // Use both change and click events to catch all scenarios
//     checkbox.addEventListener("change", handleCheckboxChange);
//     checkbox.addEventListener("click", handleCheckboxChange);
//   });

//   function handleCheckboxChange(e) {
//     console.log(
//       "Checkbox changed:",
//       e.target.value,
//       "Status:",
//       e.target.dataset.status,
//       "Checked:",
//       e.target.checked
//     );

//     // Update all states
//     updateSelectAllState();
//     updateButtonStates();
//     updateToggleModalState();
//   }

//   // Handle header checkbox (select all)
//   headerCheckbox.addEventListener("change", function () {
//     const isChecked = this.checked;
//     assetCheckboxes.forEach((checkbox) => {
//       checkbox.checked = isChecked;
//     });

//     updateSelectAllState();
//     updateButtonStates();
//     updateToggleModalState();
//   });

//   // Update select all checkbox state
//   function updateSelectAllState() {
//     const checkedCount = document.querySelectorAll(
//       ".asset-checkbox:checked"
//     ).length;
//     const totalCount = assetCheckboxes.length;

//     headerCheckbox.checked = checkedCount === totalCount;
//     headerCheckbox.indeterminate =
//       checkedCount > 0 && checkedCount < totalCount;
//   }

//   // Update button states based on selections
//   function updateButtonStates() {
//     const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
//     const hasSelection = checkedAssets.length > 0;
//     const hasEmployee = employeeSelect.value !== "";

//     console.log(
//       "Checked assets count:",
//       checkedAssets.length,
//       "Has employee:",
//       hasEmployee
//     );
//     assignBtn.disabled = !hasSelection || !hasEmployee;

//     // Update button text with count
//     if (hasSelection) {
//       assignBtn.textContent = `Assign ${checkedAssets.length} Asset(s)`;
//     } else {
//       assignBtn.textContent = "Assign Selected Assets";
//     }
//   }

//   // Handle employee selection change
//   employeeSelect.addEventListener("change", updateButtonStates);

//   form.addEventListener("submit", function (e) {
//     e.preventDefault(); // Always prevent default first

//     const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");

//     if (checkedAssets.length === 0) {
//       Swal.fire({
//         icon: "info",
//         title: "No Assets Selected",
//         text: "Please select at least one asset.",
//       });
//       return;
//     }

//     if (e.submitter.name === "assign_assets" && employeeSelect.value === "") {
//       Swal.fire({
//         icon: "info",
//         title: "Employee Not Selected",
//         text: "Please select an employee before assigning assets.",
//       });
//       return;
//     }

//     const action =
//       e.submitter.name === "assign_assets"
//         ? "assign"
//         : "remove assignments for";
//     const message = `Are you sure you want to ${action} ${checkedAssets.length} asset(s)?`;

//     Swal.fire({
//       title: "Confirm Action",
//       text: message,
//       icon: "warning",
//       showCancelButton: true,
//       confirmButtonColor: "#3085d6",
//       cancelButtonColor: "#d33",
//       confirmButtonText: "Yes, proceed",
//     }).then((result) => {
//       if (result.isConfirmed) {
//         form.submit();
//       } else {
//         console.log("User cancelled the action.");
//       }
//     });
//   });

//   // Initialize states on page load
//   updateSelectAllState();
//   updateButtonStates();
//   updateToggleModalState();
// });

document.addEventListener("DOMContentLoaded", function () {
  const headerCheckbox = document.getElementById("headerCheckbox");
  const assetCheckboxes = document.querySelectorAll(".asset-checkbox");
  const employeeSelect = document.getElementById("employeeSelect");
  const assignBtn = document.getElementById("assign-btn");
  const unassignBtn = document.getElementById("unassign-btn");
  const form = document.getElementById("assignAssetForm");
  const unassignForm = document.getElementById("unassignAssetForm");
  const closeModal = document.getElementById("cancel-btn");
  const closeUnassignModal = document.getElementById("cancel-unassign-btn");
  const modal = document.getElementById("modal");
  const unassignModal = document.getElementById("unassign-modal");
  const toggleModal = document.getElementById("toggle-modal");
  const toggleUnassignModal = document.getElementById("toggle-unassign-modal");

  // Function to update toggle modal states
  function updateToggleModalState() {
    setTimeout(() => {
      const checkedAssets = document.querySelectorAll(
        ".asset-checkbox:checked"
      );
      const hasAssignedAsset = Array.from(checkedAssets).some(
        (asset) => asset.dataset.status === "Assigned"
      );
      const hasAvailableAsset = Array.from(checkedAssets).some(
        (asset) => asset.dataset.status === "Available"
      );
      const hasSelection = checkedAssets.length > 0;

      console.log("Checked assets:", checkedAssets.length);
      console.log("Has assigned asset:", hasAssignedAsset);
      console.log("Has available asset:", hasAvailableAsset);

      // Update Assign button
      if (hasAssignedAsset || !hasSelection) {
        toggleModal.disabled = true;
        toggleModal.classList.add("opacity-50", "cursor-not-allowed");
      } else {
        toggleModal.disabled = false;
        toggleModal.classList.remove("opacity-50", "cursor-not-allowed");
      }

      // Update Unassign button
      if (hasAvailableAsset || !hasSelection) {
        toggleUnassignModal.disabled = true;
        toggleUnassignModal.classList.add("opacity-50", "cursor-not-allowed");
      } else {
        toggleUnassignModal.disabled = false;
        toggleUnassignModal.classList.remove(
          "opacity-50",
          "cursor-not-allowed"
        );
      }
    }, 10);
  }

  // Close assign modal
  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
    clearSelections();
  });

  // Close unassign modal
  closeUnassignModal.addEventListener("click", () => {
    unassignModal.classList.add("hidden");
    clearSelections();
  });

  // Function to clear all selections and update states
  function clearSelections() {
    const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
    checkedAssets.forEach((checkbox) => {
      checkbox.checked = false;
    });

    // Clear containers
    const container = document.getElementById("selectedAssetsContainer");
    const unassignContainer = document.getElementById(
      "selectedUnassignAssetsContainer"
    );
    if (container) container.innerHTML = "";
    if (unassignContainer) unassignContainer.innerHTML = "";

    // Update all states
    updateSelectAllState();
    updateButtonStates();
    updateToggleModalState();
  }

  // Toggle assign modal
  toggleModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Available']"
    );
    const container = document.getElementById("selectedAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    modal.classList.remove("hidden");
  });

  // Toggle unassign modal
  toggleUnassignModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Assigned']"
    );
    const container = document.getElementById(
      "selectedUnassignAssetsContainer"
    );
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    unassignModal.classList.remove("hidden");
  });

  // Handle individual checkbox changes
  assetCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", handleCheckboxChange);
    checkbox.addEventListener("click", handleCheckboxChange);
  });

  function handleCheckboxChange(e) {
    console.log(
      "Checkbox changed:",
      e.target.value,
      "Status:",
      e.target.dataset.status,
      "Checked:",
      e.target.checked
    );

    updateSelectAllState();
    updateButtonStates();
    updateToggleModalState();
  }

  // Handle header checkbox (select all)
  headerCheckbox.addEventListener("change", function () {
    const isChecked = this.checked;
    assetCheckboxes.forEach((checkbox) => {
      checkbox.checked = isChecked;
    });

    updateSelectAllState();
    updateButtonStates();
    updateToggleModalState();
  });

  // Update select all checkbox state
  function updateSelectAllState() {
    const checkedCount = document.querySelectorAll(
      ".asset-checkbox:checked"
    ).length;
    const totalCount = assetCheckboxes.length;

    headerCheckbox.checked = checkedCount === totalCount;
    headerCheckbox.indeterminate =
      checkedCount > 0 && checkedCount < totalCount;
  }

  // Update button states based on selections
  function updateButtonStates() {
    const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
    const hasSelection = checkedAssets.length > 0;
    const hasEmployee = employeeSelect.value !== "";
    const assignableAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Available']"
    );
    const unassignableAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Assigned']"
    );

    console.log(
      "Checked assets count:",
      checkedAssets.length,
      "Has employee:",
      hasEmployee
    );

    // Update assign button
    assignBtn.disabled = assignableAssets.length === 0 || !hasEmployee;
    if (assignableAssets.length > 0) {
      assignBtn.textContent = `Assign ${assignableAssets.length} Asset(s)`;
    } else {
      assignBtn.textContent = "Assign Selected Assets";
    }

    // Update unassign button
    if (unassignableAssets.length > 0) {
      unassignBtn.textContent = `Unassign ${unassignableAssets.length} Asset(s)`;
    } else {
      unassignBtn.textContent = "Unassign Selected Assets";
    }
  }

  // Handle employee selection change
  employeeSelect.addEventListener("change", updateButtonStates);

  // Handle assign form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Available']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Available Assets Selected",
        text: "Please select at least one available asset to assign.",
      });
      return;
    }

    if (employeeSelect.value === "") {
      Swal.fire({
        icon: "info",
        title: "Employee Not Selected",
        text: "Please select an employee before assigning assets.",
      });
      return;
    }

    const message = `Are you sure you want to assign ${checkedAssets.length} asset(s) to the selected employee?`;

    Swal.fire({
      title: "Confirm Assignment",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, assign",
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Handle unassign form submission
  unassignForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assigned Assets Selected",
        text: "Please select at least one assigned asset to unassign.",
      });
      return;
    }

    const message = `Are you sure you want to unassign ${checkedAssets.length} asset(s)? This will remove the current employee assignment and set the status to 'Available'.`;

    Swal.fire({
      title: "Confirm Unassignment",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#f59e0b",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, unassign",
    }).then((result) => {
      if (result.isConfirmed) {
        unassignForm.submit();
      }
    });
  });

  // Initialize states on page load
  updateSelectAllState();
  updateButtonStates();
  updateToggleModalState();
});
