
document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // DOM ELEMENTS
  // ============================================
  const headerCheckbox = document.getElementById("headerCheckbox");
  
  // Modal elements
  const assignDeptModal = document.getElementById("assign-dept-modal");
  const assignEmployeeDeptModal = document.getElementById("assign-employee-dept-modal");
  const moveBranchModal = document.getElementById("move-branch-modal");
  const returnEmployeeModal = document.getElementById("return-employee-modal");

  // Button elements
  const toggleDeptModal = document.getElementById("toggle-dept-modal");
  const toggleEmployeeModal = document.getElementById("toggle-employee-modal");
  const toggleMoveBranchModal = document.getElementById("toggle-move-branch-modal");
  const toggleReturnEmployeeModal = document.getElementById("toggle-return-employee-modal");

  // Form elements
  const assignDeptForm = document.getElementById("assignDeptForm");
  const assignEmployeeDeptForm = document.getElementById("assignEmployeeDeptForm");
  const moveBranchForm = document.getElementById("moveBranchForm");
  const returnEmployeeForm = document.getElementById("returnEmployeeForm");

  // Select elements
  const departmentSelect = document.getElementById("departmentSelect");
  const employeeDeptSelect = document.getElementById("employeeDeptSelect");

  // File upload elements
  const dropZoneDept = document.getElementById("dropZoneDept");
  const agreementFileDept = document.getElementById("agreement_file_dept");
  const fileInfoDept = document.getElementById("fileInfoDept");
  const uploadedFileNameDept = document.getElementById("uploadedFileNameDept");
  const removeFileDeptBtn = document.getElementById("removeFileDept");
  const fileNameDept = document.getElementById("fileNameDept");

  // ============================================
  // FILE UPLOAD HANDLERS
  // ============================================
  function validateFile(file) {
    const allowedExtensions = ['pdf', 'doc', 'docx'];
    const maxSize = 10 * 1024 * 1024;

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
    uploadedFileNameDept.textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
    fileInfoDept.classList.remove('hidden');
    fileNameDept.textContent = '✓ File selected: ' + file.name;
    dropZoneDept.classList.add('bg-emerald-50', 'border-emerald-200');
    dropZoneDept.classList.remove('bg-slate-50', 'border-slate-300');
  }

  function resetFileUpload() {
    agreementFileDept.value = '';
    fileInfoDept.classList.add('hidden');
    fileNameDept.textContent = 'Drag and drop your PDF or DOC file here, or click to select';
    dropZoneDept.classList.remove('bg-emerald-50', 'border-emerald-200');
    dropZoneDept.classList.add('bg-slate-50', 'border-slate-300');
  }

  // Drop zone events
  dropZoneDept.addEventListener('click', () => agreementFileDept.click());

  dropZoneDept.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZoneDept.classList.add('bg-slate-200');
  });

  dropZoneDept.addEventListener('dragleave', () => {
    dropZoneDept.classList.remove('bg-slate-200');
  });

  dropZoneDept.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZoneDept.classList.remove('bg-slate-200');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];
      if (validateFile(file)) {
        agreementFileDept.files = files;
        displayFileName(file);
      }
    }
  });

  agreementFileDept.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      const file = e.target.files[0];
      if (validateFile(file)) {
        displayFileName(file);
      }
    }
  });

  removeFileDeptBtn.addEventListener('click', resetFileUpload);

  // ============================================
  // HELPER FUNCTIONS - Get all checkboxes dynamically
  // ============================================
  function getAllCheckboxes() {
    return document.querySelectorAll(".asset-checkbox");
  }

  function getCheckedCheckboxes() {
    return document.querySelectorAll(".asset-checkbox:checked");
  }

  // ============================================
  // UPDATE BUTTON STATES
  // ============================================
  function updateButtonStates() {
    const checkedAssets = getCheckedCheckboxes();
    
    const hasBranchAssets = Array.from(checkedAssets).some(
      (asset) => asset.dataset.status === "Branch Assigned"
    );
    const hasDeptAssets = Array.from(checkedAssets).some(
      (asset) => asset.dataset.status === "Department Assigned"
    );
    const hasEmployeeAssets = Array.from(checkedAssets).some(
      (asset) => asset.dataset.status === "Employee Assigned"
    );

    console.log('updateButtonStates:', {
      checkedCount: checkedAssets.length,
      hasBranchAssets,
      hasDeptAssets,
      hasEmployeeAssets
    });

    toggleDeptModal.disabled = !hasBranchAssets || checkedAssets.length === 0;
    toggleDeptModal.classList.toggle("opacity-50", toggleDeptModal.disabled);
    toggleDeptModal.classList.toggle("cursor-not-allowed", toggleDeptModal.disabled);

    toggleEmployeeModal.disabled = !hasDeptAssets || checkedAssets.length === 0;
    toggleEmployeeModal.classList.toggle("opacity-50", toggleEmployeeModal.disabled);
    toggleEmployeeModal.classList.toggle("cursor-not-allowed", toggleEmployeeModal.disabled);

    toggleMoveBranchModal.disabled = !hasDeptAssets || checkedAssets.length === 0;
    toggleMoveBranchModal.classList.toggle("opacity-50", toggleMoveBranchModal.disabled);
    toggleMoveBranchModal.classList.toggle("cursor-not-allowed", toggleMoveBranchModal.disabled);

    toggleReturnEmployeeModal.disabled = !hasEmployeeAssets || checkedAssets.length === 0;
    toggleReturnEmployeeModal.classList.toggle("opacity-50", toggleReturnEmployeeModal.disabled);
    toggleReturnEmployeeModal.classList.toggle("cursor-not-allowed", toggleReturnEmployeeModal.disabled);
  }

  // ============================================
  // UPDATE SELECT ALL STATE
  // ============================================
  function updateSelectAllState() {
    const checkedCount = getCheckedCheckboxes().length;
    const totalCount = getAllCheckboxes().length;

    headerCheckbox.checked = checkedCount === totalCount && totalCount > 0;
    headerCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
  }

  // ============================================
  // CLEAR SELECTIONS
  // ============================================
  function clearSelections() {
    getAllCheckboxes().forEach((checkbox) => {
      checkbox.checked = false;
    });
    updateSelectAllState();
    updateButtonStates();
  }

  // ============================================
  // SELECT ALL FUNCTIONALITY
  // ============================================
  headerCheckbox.addEventListener("change", function () {
    getAllCheckboxes().forEach((checkbox) => {
      checkbox.checked = this.checked;
    });
    updateSelectAllState();
    updateButtonStates();
  });

  // ============================================
  // EVENT DELEGATION FOR DYNAMIC CHECKBOXES
  // ============================================
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("asset-checkbox")) {
      console.log('Checkbox changed:', e.target.value);
      updateSelectAllState();
      updateButtonStates();
    }
  }, true); // Use capture phase for DataTables compatibility

  // ============================================
  // ASSIGN TO DEPARTMENT HANDLERS
  // ============================================
  toggleDeptModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select assets to assign to department.",
      });
      return;
    }

    const container = document.getElementById("selectedDeptAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    document.getElementById("deptAssetsCount").textContent = checkedAssets.length;
    assignDeptModal.classList.remove("hidden");
  });

  document.getElementById("cancel-dept-btn").addEventListener("click", () => {
    assignDeptModal.classList.add("hidden");
    clearSelections();
  });

  assignDeptForm.addEventListener("submit", function (e) {
    e.preventDefault();

    if (departmentSelect.value === "") {
      Swal.fire({
        icon: "info",
        title: "Department Not Selected",
        text: "Please select a department.",
      });
      return;
    }

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Branch Assigned']"
    );
    const deptName = departmentSelect.options[departmentSelect.selectedIndex].text;

    Swal.fire({
      title: "Confirm Assignment",
      text: `Are you sure you want to assign ${checkedAssets.length} asset(s) to ${deptName}?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#2563eb",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, assign",
    }).then((result) => {
      if (result.isConfirmed) {
        assignDeptForm.submit();
      }
    });
  });

  // ============================================
  // ASSIGN TO EMPLOYEE HANDLERS
  // ============================================
  toggleEmployeeModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Department Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select assets to assign to employee.",
      });
      return;
    }

    // Get the first asset's department
    const firstAssetRow = checkedAssets[0].closest("tr");
    const deptCell = firstAssetRow.querySelector("td:nth-child(6)");
    const deptName = deptCell.textContent.trim();
    const deptId = firstAssetRow.querySelector('.single-assign-to-employee-btn')?.dataset.departmentId || 
                  firstAssetRow.querySelector('.single-move-to-branch-btn')?.closest('tr')?.querySelector('[data-department-id]')?.dataset.departmentId;

    // Check if all selected assets are from the same department
    const allSameDept = Array.from(checkedAssets).every(asset => {
      const row = asset.closest("tr");
      const cell = row.querySelector("td:nth-child(6)");
      return cell.textContent.trim() === deptName;
    });

    if (!allSameDept) {
      Swal.fire({
        icon: "warning",
        title: "Different Departments",
        text: "All selected assets must be from the same department.",
      });
      return;
    }

    const container = document.getElementById("selectedEmployeeDeptAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    // Get department ID from the first asset row
    const firstBtn = firstAssetRow.querySelector('[data-department-id]');
    const departmentId = firstBtn?.dataset.departmentId;

    document.getElementById("deptModalDept").value = deptName;
    document.getElementById("deptModalDeptId").value = departmentId;
    document.getElementById("employeeAssetsCount").textContent = checkedAssets.length;
    console.log(departmentId);

    resetFileUpload();
    assignEmployeeDeptModal.classList.remove("hidden");
  });

  document.getElementById("cancel-employee-dept-btn").addEventListener("click", () => {
    assignEmployeeDeptModal.classList.add("hidden");
    clearSelections();
  });

  assignEmployeeDeptForm.addEventListener("submit", function (e) {
    e.preventDefault();

    if (employeeDeptSelect.value === "") {
      Swal.fire({
        icon: "info",
        title: "Employee Not Selected",
        text: "Please select an employee.",
      });
      return;
    }

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Department Assigned']"
    );
    const employeeName = employeeDeptSelect.options[employeeDeptSelect.selectedIndex].text;

    Swal.fire({
      title: "Confirm Assignment",
      text: `Are you sure you want to assign ${checkedAssets.length} asset(s) to ${employeeName}?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#2563eb",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, assign",
    }).then((result) => {
      if (result.isConfirmed) {
        assignEmployeeDeptForm.submit();
      }
    });
  });

  // ============================================
  // MOVE TO BRANCH LEVEL HANDLERS
  // ============================================
  toggleMoveBranchModal.addEventListener("click", () => {
    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Department Assigned']"
    );

    if (checkedAssets.length === 0) {
      Swal.fire({
        icon: "info",
        title: "No Assets Selected",
        text: "Please select assets to move.",
      });
      return;
    }

    const container = document.getElementById("selectedMoveBranchAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    document.getElementById("moveBranchAssetsCount").textContent = checkedAssets.length;
    moveBranchModal.classList.remove("hidden");
  });

  document.getElementById("cancel-move-branch-btn").addEventListener("click", () => {
    moveBranchModal.classList.add("hidden");
    clearSelections();
  });

  moveBranchForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Department Assigned']"
    );

    Swal.fire({
      title: "Confirm Move",
      text: `Are you sure you want to move ${checkedAssets.length} asset(s) back to branch level?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#b45309",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, move",
    }).then((result) => {
      if (result.isConfirmed) {
        moveBranchForm.submit();
      }
    });
  });

  // ============================================
  // RETURN TO DEPARTMENT HANDLERS
  // ============================================
  toggleReturnEmployeeModal.addEventListener("click", () => {
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

    const container = document.getElementById("selectedReturnEmployeeAssetsContainer");
    container.innerHTML = "";

    checkedAssets.forEach((asset) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "asset_ids[]";
      input.value = asset.value;
      container.appendChild(input);
    });

    document.getElementById("returnEmployeeAssetsCount").textContent = checkedAssets.length;
    returnEmployeeModal.classList.remove("hidden");
  });

  document.getElementById("cancel-return-employee-btn").addEventListener("click", () => {
    returnEmployeeModal.classList.add("hidden");
    clearSelections();
  });

  returnEmployeeForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const checkedAssets = document.querySelectorAll(
      ".asset-checkbox:checked[data-status='Employee Assigned']"
    );

    Swal.fire({
      title: "Confirm Return",
      text: `Are you sure you want to return ${checkedAssets.length} asset(s) to department level?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ea580c",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, return",
    }).then((result) => {
      if (result.isConfirmed) {
        returnEmployeeForm.submit();
      }
    });
  });

  // ============================================
  // INITIALIZE
  // ============================================
  updateSelectAllState();
  updateButtonStates();
});

  document.addEventListener('DOMContentLoaded', function() {
    let table = $('#branchAssetTable').DataTable();
    const filterButtons = document.querySelectorAll('.filter-btn');
    let currentFilter = 'all';

    // Custom filter function for DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const row = table.row(dataIndex).node();
      const status = row.getAttribute('data-status');

      if (currentFilter === 'all') {
        return true;
      } else if (currentFilter === 'assigned') {
        return status === 'Employee Assigned';
      } else if (currentFilter === 'department') {
        return status === 'Department Assigned';
      } else if (currentFilter === 'repair') {
        return status === 'Under Maintenance';
      } else if (currentFilter === 'defective') {
        return status === 'Uncommitted';
      }
      return true;
    });

    // Filter button click handler
    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        currentFilter = this.getAttribute('data-filter');

        // Update active button styling
        filterButtons.forEach(btn => {
          btn.classList.remove('active', 'bg-red-500', 'text-white', 'border-blue-700');
          btn.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
        });

        this.classList.add('active', 'bg-red-500', 'text-white', 'border-blue-700');
        this.classList.remove('bg-white', 'text-slate-700', 'border-slate-300');

        // Redraw DataTable with new filter
        table.draw();
      });
    });
  });