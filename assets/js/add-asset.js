document.addEventListener("DOMContentLoaded", function () {
  const assignAssetButtons = document.querySelectorAll(".add-asset-btn");
  const modal = document.getElementById("add-asset-modal");
  const form = document.getElementById("add-asset-form");

  function closeAllMenus() {
    document
      .querySelectorAll(".menu")
      .forEach((m) => m.classList.add("hidden"));
  }

  function handleDetailedTableActions(e) {
    // Handle ellipsis toggle
    if (e.target.classList.contains("select-menu")) {
      e.stopPropagation();
      closeAllMenus();

      const menuElement = e.target.nextElementSibling;
      if (menuElement && menuElement.classList.contains("menu")) {
        menuElement.classList.toggle("hidden");
      }
    }

    // ASSIGN ASSET
    if (e.target.classList.contains("add-asset-btn")) {
      e.stopPropagation();
      openAssignAssetModal(e.target);
    }
  }

  const detailedTable = document.querySelector("#detailed-list-table");
  if (detailedTable) {
    detailedTable.addEventListener("click", function (e) {
      handleDetailedTableActions(e);
    });
  }

  // Category mappings for asset number generation (client-side preview)
  const categoryMappings = {
    laptop: { code: "01", suffix: "" },
    tv: { code: "02", suffix: "" },
    desktop: { code: "03", suffix: "" },
    monitor: { code: "04", suffix: "" },
    printer: { code: "05", suffix: "" },
    scanner: { code: "07", suffix: "" },
    "paper shredder": { code: "08", suffix: "" },
    webcam: { code: "09", suffix: "" },
    ipad: { code: "10", suffix: "" },
    projector: { code: "11", suffix: "" },
    speaker: { code: "12", suffix: "" },
    amplifier: { code: "13", suffix: "" },
    microphone: { code: "14", suffix: "" },
    mixer: { code: "15", suffix: "" },
    "laptop mouse": { code: "01", suffix: "M" },
    "laptop charger": { code: "01", suffix: "C" },
    headset: { code: "01", suffix: "H" },
    bracket: { code: "02", suffix: "BK" },
    "desktop monitor": { code: "03", suffix: "MO" },
    "desktop mouse": { code: "03", suffix: "M" },
    "system unit": { code: "03", suffix: "SU" },
  };

  // Add Asset Button
  async function openAssignAssetModal(target) {
    // Get data from button attributes
    const inventoryId = target.getAttribute("data-id");
    const itemNumber = target.getAttribute("data-item-number");
    const manufacturer = target.getAttribute("data-manufacturer");
    const categoryId = target.getAttribute("data-category-id");
    const categoryName = target.getAttribute("data-category-option");
    const assetNumber = target.getAttribute("data-asset-number");
    const model = target.getAttribute("data-model");
    const serialNumber = target.getAttribute("data-serial-number");
    const ipAddress = target.getAttribute("data-ip-address");
    const status = target.getAttribute("data-status");
    const conditions = target.getAttribute("data-conditions");
    const photo = target.getAttribute("data-photo");
    const assetId = target.getAttribute("data-asset-id");

    console.log("asset number:", assetNumber);
    // Charger info
    const chargerId = target.getAttribute("data-charger-id");
    const chargerAssetNumber = target.getAttribute("data-charger-asset-number");
    const chargerModel = target.getAttribute("data-charger-model");
    const chargerSerialNumber = target.getAttribute(
      "data-charger-serial-number"
    );
    const chargerCondition = target.getAttribute("data-charger-conditions");
    const chargerStatus = target.getAttribute("data-charger-status");
    console.log("Charger Asset No. ", chargerAssetNumber);
    // Populate modal fields
    document.getElementById("modal-item-number").value = itemNumber || "";
    document.getElementById("modal-inventory-id").value = inventoryId || "";
    document.getElementById("asset-id").value = assetId || 0;
    document.getElementById("modal-manufacturer").value = manufacturer || "";
    document.getElementById("modal-category-select").value = categoryName || "";
    document.getElementById("input-model").value = model || "";
    document.getElementById("input-serial-number").value = serialNumber || "";
    document.getElementById("input-ip-address").value = ipAddress || "";
    document.getElementById("select-status").value = status || "Available";
    document.getElementById("select-conditions").value = conditions || "Good";
    document.getElementById("figure-title").textContent =
      (manufacturer || "") + " - " + (categoryName || "");
    document.getElementById("photo-preview").src = "/" + (photo || "");

    // Generate asset number preview if it's a new asset
    console.log("Asset ID", assetId);
    if (!assetId || assetId === "0") {
      const categoryKey = categoryName ? categoryName.toLowerCase() : "";
      document.getElementById("form-add-asset").textContent = "Assign Asset";
      document.getElementById("asset-action").value = "Add Asset";
      if (categoryMappings[categoryKey]) {
        const mapping = categoryMappings[categoryKey];
        try {
          const nextNumber = await getNextAssetNumber(
            mapping.code,
            mapping.suffix
          );
          const fullAssetNumber = `TRA-${mapping.code}-${nextNumber}${mapping.suffix}`;
          document.getElementById("modal-asset-number").value = fullAssetNumber;
        } catch (error) {
          console.error("Error generating asset number:", error);
          document.getElementById(
            "modal-asset-number"
          ).value = `TRA-${mapping.code}-0001${mapping.suffix}`;
        }
      } else {
        document.getElementById("modal-asset-number").value = "TRA-XX-0001";
      }
    } else {
      document.getElementById("modal-asset-number").value = assetNumber;
      document.getElementById("form-add-asset").textContent = "Update Asset";
      document.getElementById("asset-action").value = "Update Asset";
    }

    // Handle laptop charger section
    const laptopChargerSection = document.getElementById(
      "laptop-charger-section"
    );
    if (categoryName && categoryName.toLowerCase() === "laptop") {
      laptopChargerSection.classList.remove("hidden");

      // Populate charger fields if they exist
      if (chargerId) {
        document.getElementById("charger-id").value = chargerId;
        document.getElementById("charger-asset-number").value =
          chargerAssetNumber || "";
        document.getElementById("model-charger").value = chargerModel || "";
        document.getElementById("charger-serial-number").value =
          chargerSerialNumber || "";
        document.querySelector('select[name="charger-condition"]').value =
          chargerCondition || "Good";
      } else {
        // Clear charger fields for new assignment - NO AUTO-GENERATION
        document.getElementById("charger-id").value = 0;
        document.getElementById("charger-asset-number").value = ""; // Empty for manual input
        document.getElementById("model-charger").value = manufacturer || "";
        document.getElementById("charger-serial-number").value = "";
        document.querySelector('select[name="charger-condition"]').value =
          "Good";
      }
    } else {
      laptopChargerSection.classList.add("hidden");
    }

    // Add category_id to form
    let categoryIdInput = document.querySelector('input[name="category_id"]');
    if (!categoryIdInput) {
      categoryIdInput = document.createElement("input");
      categoryIdInput.type = "hidden";
      categoryIdInput.name = "category_id";
      form.appendChild(categoryIdInput);
    }
    categoryIdInput.value = categoryId || "";

    // Add category_display to form
    let categoryDisplayInput = document.querySelector(
      'input[name="category_display"]'
    );
    if (!categoryDisplayInput) {
      categoryDisplayInput = document.createElement("input");
      categoryDisplayInput.type = "hidden";
      categoryDisplayInput.name = "category_display";
      form.appendChild(categoryDisplayInput);
    }
    categoryDisplayInput.value = categoryName || "";

    // Show modal
    modal.classList.remove("hidden");
  }

  // Function to get next asset number via AJAX
  async function getNextAssetNumber(categoryCode, suffix = "") {
    try {
      const response = await fetch("/get-next-asset-number.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          category_code: categoryCode,
          suffix: suffix,
        }),
      });

      console.log("Response status:", response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const responseText = await response.text();
      console.log("Raw response:", responseText);

      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error("JSON parse error:", parseError);
        console.error("Response was:", responseText);
        throw new Error("Invalid JSON response from server");
      }

      if (data.error) {
        throw new Error(data.error);
      }

      return data.next_number;
    } catch (error) {
      console.error("Error fetching next asset number:", error);
      return "0001"; // fallback
    }
  }

  // Close modal when clicking outside
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.add("hidden");
    }
  });

  // Handle form submission with charger validation
  form.addEventListener("submit", function (e) {
    console.log("Form submission started");

    const serialNumber = document
      .getElementById("input-serial-number")
      .value.trim();
    const inventoryId = document.getElementById("modal-inventory-id").value;
    const categoryName = document.getElementById("modal-category-select").value;

    // Validate main asset
    if (!serialNumber) {
      e.preventDefault();
      alert("Serial number is required");
      return false;
    }

    if (!inventoryId) {
      e.preventDefault();
      alert("Inventory ID is missing");
      return false;
    }

    // Validate charger fields if it's a laptop
    if (categoryName && categoryName.toLowerCase() === "laptop") {
      const chargerSerialNumber = document
        .getElementById("charger-serial-number")
        .value.trim();
      const chargerAssetNumber = document
        .getElementById("charger-asset-number")
        .value.trim();

      // If charger serial is provided, asset number must also be provided
      if (chargerSerialNumber && !chargerAssetNumber) {
        e.preventDefault();
        alert(
          "Charger asset number is required when charger serial number is provided"
        );
        return false;
      }
    }

    // Debug: Log all form data before submission
    const formData = new FormData(form);
    console.log("Form data being submitted:");
    for (let [key, value] of formData.entries()) {
      console.log(key + ":", value);
    }

    // Show loading state
    const submitBtn = document.getElementById("form-add-asset");
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Assigning...";
    }

    // Let the form submit normally
    return true;
  });
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

// Delete Asset
document.addEventListener("click", async function (e) {
  if (e.target.classList.contains("delete-asset-btn")) {
    e.preventDefault();
    e.stopPropagation();

    const assetId = e.target.getAttribute("data-asset-id");
    const assetNumber = e.target.getAttribute("data-asset-number");
    const manufacturer = e.target.getAttribute("data-category-option");

    if (!assetId || assetId === "0") {
      Swal.fire({
        icon: "error",
        title: "Invalid Asset",
        text: "Cannot delete: asset ID is missing or invalid.",
      });
      return;
    }

    Swal.fire({
      icon: "warning",
      title: "Delete Asset?",
      text: `Are you sure you want to delete ${manufacturer} with asset tag of "${assetNumber}"?`,
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Delete",
    }).then(async (result) => {
      if (result.isConfirmed) {
        try {
          const response = await fetch(window.location.href, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "Delete Asset",
              asset_id: assetId,
            }),
          });

          if (response.redirected) {
            window.location.href = response.url;
          } else {
            const text = await response.text();
            console.error("Unexpected response:", text);
            Swal.fire("Error", "Failed to delete asset. Check logs.", "error");
          }
        } catch (err) {
          console.error("Deletion error:", err);
          Swal.fire(
            "Error",
            "Something went wrong deleting the asset.",
            "error"
          );
        }
      }
    });
  }
});
