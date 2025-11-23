document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // DOM Elements
  // ============================================
  const headerCheckbox = document.getElementById("headerCheckbox");
  const assetCheckboxes = document.querySelectorAll(".asset-checkbox");
  
  // Branch assignment elements
  const branchSelect = document.getElementById("branchSelect");
  const toggleBranchModal = document.getElementById("toggle-branch-modal");
  const branchModal = document.getElementById("branch-modal");
  const assignBranchForm = document.getElementById("assignBranchForm");
  const cancelBranchBtn = document.getElementById("cancel-branch-btn");
  
  // Employee assignment elements
  const toggleEmployeeModal = document.getElementById("toggle-employee-modal");
  const employeeModal = document.getElementById("employee-modal");
  const employeeSelect = document.getElementById("employeeSelect");
  const assignEmployeeForm = document.getElementById("assignEmployeeForm");
  const cancelEmployeeBtn = document.getElementById("cancel-employee-btn");
  const employeeModalBranch = document.getElementById("employeeModalBranch");
  const assetBranchId = document.getElementById("assetBranchId");
  const assignToEmployee = document.getElementById("assignToEmployee");

  // File upload elements
  const dropZone = document.getElementById("dropZone");
  const agreementFile = document.getElementById("agreement_file");
  const fileInfo = document.getElementById("fileInfo");
  const uploadedFileName = document.getElementById("uploadedFileName");
  const removeFileBtn = document.getElementById("removeFile");
  const fileName = document.getElementById("fileName");

  // Return to employee elements
  const toggleReturnEmployeeModal = document.getElementById("toggle-return-employee-modal");
  const returnEmployeeModal = document.getElementById("return-employee-modal");
  const returnEmployeeForm = document.getElementById("returnEmployeeForm");
  const cancelReturnEmployeeBtn = document.getElementById("cancel-return-employee-btn");
  
  // Return to branch elements
  const toggleReturnBranchModal = document.getElementById("toggle-return-branch-modal");
  const returnBranchModal = document.getElementById("return-branch-modal");
  const returnBranchForm = document.getElementById("returnBranchForm");
  const cancelReturnBranchBtn = document.getElementById("cancel-return-branch-btn");

  // ============================================
  // FILE UPLOAD HANDLERS
  // ============================================
  function validateFile(file) {
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    const allowedExtensions = ['pdf', 'doc', 'docx'];
    const maxSize = 10 * 1024 * 1024; // 5MB

    const fileExtension = file.name.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Only PDF and DOC files are allowed.'
      });
      return false;
    }

    if (file.size > maxSize) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'File size must not exceed 10MB.'
      });
      return false;
    }

    return true;
  }

  function displayFileName(file) {
    uploadedFileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
    fileInfo.classList.remove('hidden');
    fileName.textContent = '✓ File selected: ' + file.name;
    dropZone.classList.add('bg-emerald-50', 'border-emerald-200');
    dropZone.classList.remove('bg-slate-50', 'border-slate-300');
  }

  // Drop zone events
  dropZone.addEventListener('click', () => agreementFile.click());

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('bg-slate-200');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('bg-slate-200');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('bg-slate-200');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];
      if (validateFile(file)) {
        agreementFile.files = files;
        displayFileName(file);
      }
    }
  });

  // File input change event
  agreementFile.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      const file = e.target.files[0];
      if (validateFile(file)) {
        displayFileName(file);
      }
    }
  });

  // Remove file button
  removeFileBtn.addEventListener('click', () => {
    agreementFile.value = '';
    fileInfo.classList.add('hidden');
    fileName.textContent = 'Drag and drop your PDF or DOC file here, or click to select';
    dropZone.classList.remove('bg-emerald-50', 'border-emerald-200');
    dropZone.classList.add('bg-slate-50', 'border-slate-300');
  });

  // ============================================
  // Update button states based on selections
  // ============================================
  function updateButtonStates() {
    setTimeout(() => {
      const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
      const hasUnassignedAssets = Array.from(checkedAssets).some(
        (asset) => asset.dataset.status === "Available"
      );
      const hasBranchAssignedAssets = Array.from(checkedAssets).some(
        (asset) => asset.dataset.status === "Branch Assigned"
      );
      const hasEmployeeAssignedAssets = Array.from(checkedAssets).some(
        (asset) => asset.dataset.status === "Employee Assigned"
      );
      const hasSelection = checkedAssets.length > 0;

      toggleBranchModal.disabled = !hasUnassignedAssets || !hasSelection;
      toggleBranchModal.classList.toggle("opacity-50", toggleBranchModal.disabled);
      toggleBranchModal.classList.toggle("cursor-not-allowed", toggleBranchModal.disabled);

      toggleEmployeeModal.disabled = !hasBranchAssignedAssets || !hasSelection;
      toggleEmployeeModal.classList.toggle("opacity-50", toggleEmployeeModal.disabled);
      toggleEmployeeModal.classList.toggle("cursor-not-allowed", toggleEmployeeModal.disabled);

      toggleReturnEmployeeModal.disabled = !hasEmployeeAssignedAssets || !hasSelection;
      toggleReturnEmployeeModal.classList.toggle("opacity-50", toggleReturnEmployeeModal.disabled);
      toggleReturnEmployeeModal.classList.toggle("cursor-not-allowed", toggleReturnEmployeeModal.disabled);

      const hasAssignedAssets = hasBranchAssignedAssets || hasEmployeeAssignedAssets;
      toggleReturnBranchModal.disabled = !hasAssignedAssets || !hasSelection;
      toggleReturnBranchModal.classList.toggle("opacity-50", toggleReturnBranchModal.disabled);
      toggleReturnBranchModal.classList.toggle("cursor-not-allowed", toggleReturnBranchModal.disabled);
    }, 10);
  }

  // ============================================
  // Update Select All Checkbox State
  // ============================================
  function updateSelectAllState() {
    const checkedCount = document.querySelectorAll(".asset-checkbox:checked").length;
    const totalCount = assetCheckboxes.length;

    headerCheckbox.checked = checkedCount === totalCount;
    headerCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
  }

  // ============================================
  // Clear all selections
  // ============================================
  function clearSelections() {
    const checkedAssets = document.querySelectorAll(".asset-checkbox:checked");
    checkedAssets.forEach((checkbox) => {
      checkbox.checked = false;
    });

    updateSelectAllState();
    updateButtonStates();
  }

  // ============================================
  // BRANCH ASSIGNMENT HANDLERS
  // ============================================
  toggleBranchModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Available']"
    );
    const container = document.getElementById("selectedBranchAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    branchModal.classList.remove("hidden");
  });

  cancelBranchBtn.addEventListener("click", () => {
    branchModal.classList.add("hidden");
    clearSelections();
  });

  assignBranchForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Available']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Unassigned Assets Selected",
        text: "Please select at least one unassigned asset.",
      });
      return;
    }

    if (branchSelect.value === "") {
      Swal.fire({
        icon: "info",
        title: "Branch Not Selected",
        text: "Please select a branch.",
      });
      return;
    }

    const branchName = branchSelect.options[branchSelect.selectedIndex].text;
    const message = `Are you sure you want to assign ${checkedAssets.length} asset(s) to ${branchName}?`;

    Swal.fire({
      title: "Confirm Branch Assignment",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc2626",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, assign to branch",
    }).then((result) => {
      if (result.isConfirmed) {
        assignBranchForm.submit();
      }
    });
  });

  // ============================================
  // EMPLOYEE ASSIGNMENT HANDLERS
  // ============================================
  toggleEmployeeModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned']"
    );
    const container = document.getElementById("selectedEmployeeAssetsContainer");
    container.innerHTML = "";

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Branch Assigned Assets",
        text: "Please select assets that are assigned to a branch.",
      });
      return;
    }

    const firstAssetRow = checkedAssets[0].closest("tr");
    const branchCell = firstAssetRow.querySelector("td:nth-child(9)");
    const branchName = branchCell.textContent.trim();

    employeeModalBranch.value = branchName;

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    // Reset file upload
    agreementFile.value = '';
    fileInfo.classList.add('hidden');
    fileName.textContent = 'Drag and drop your PDF or DOC file here, or click to select';
    dropZone.classList.remove('bg-emerald-50', 'border-emerald-200');
    dropZone.classList.add('bg-slate-50', 'border-slate-300');

    employeeModal.classList.remove("hidden");
  });

  cancelEmployeeBtn.addEventListener("click", () => {
    employeeModal.classList.add("hidden");
    clearSelections();
  });

  assignEmployeeForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select at least one asset.",
      });
      return;
    }

    if (employeeSelect.value === "") {
      Swal.fire({
        icon: "info",
        title: "Employee Not Selected",
        text: "Please select an employee.",
      });
      return;
    }

    const employeeName = employeeSelect.options[employeeSelect.selectedIndex].text;
    const message = `Are you sure you want to assign ${checkedAssets.length} asset(s) to ${employeeName}?`;

    Swal.fire({
      title: "Confirm Employee Assignment",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#2563eb",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, assign to employee",
    }).then((result) => {
      if (result.isConfirmed) {
        assignEmployeeForm.submit();
      }
    });
  });

  // ============================================
  // RETURN TO BRANCH HANDLERS
  // ============================================
  toggleReturnEmployeeModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Employee Assigned']"
    );
    const container = document.getElementById("selectedReturnEmployeeAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    returnEmployeeModal.classList.remove("hidden");
  });

  cancelReturnEmployeeBtn.addEventListener("click", () => {
    returnEmployeeModal.classList.add("hidden");
    clearSelections();
  });

  returnEmployeeForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Employee Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select assets to return.",
      });
      return;
    }

    const message = `Are you sure you want to return ${checkedAssets.length} asset(s) to the branch pool?`;

    Swal.fire({
      title: "Confirm Return",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ea580c",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, return to branch",
    }).then((result) => {
      if (result.isConfirmed) {
        returnEmployeeForm.submit();
      }
    });
  });

  // ============================================
  // RETURN TO POOL HANDLERS
  // ============================================
  toggleReturnBranchModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned'], .asset-checkbox:checked[data-status='Employee Assigned']"
    );
    const container = document.getElementById("selectedReturnBranchAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    returnBranchModal.classList.remove("hidden");
  });

  cancelReturnBranchBtn.addEventListener("click", () => {
    returnBranchModal.classList.add("hidden");
    clearSelections();
  });

  returnBranchForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned'], .asset-checkbox:checked[data-status='Employee Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select assets to return.",
      });
      return;
    }

    const message = `Are you sure you want to return ${checkedAssets.length} asset(s) to the general pool? This cannot be undone.`;

    Swal.fire({
      title: "Confirm Return to Pool",
      text: message,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ca8a04",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, return to pool",
    }).then((result) => {
      if (result.isConfirmed) {
        returnBranchForm.submit();
      }
    });
  });

  // ============================================
  // HEADER CHECKBOX (SELECT ALL)
  // ============================================
  headerCheckbox.addEventListener("change", function () {
    const isChecked = this.checked;
    assetCheckboxes.forEach((checkbox) => {
      checkbox.checked = isChecked;
    });

    updateSelectAllState();
    updateButtonStates();
  });

  // ============================================
  // INDIVIDUAL CHECKBOX CHANGES
  // ============================================
  assetCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      updateSelectAllState();
      updateButtonStates();
    });
  });

  // ============================================
  // INITIALIZE ON PAGE LOAD
  // ============================================
  updateSelectAllState();
  updateButtonStates();
});