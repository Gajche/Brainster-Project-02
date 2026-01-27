/**
 * Project Members Multi-Select Functionality
 * Handles selecting multiple team members to add/remove from a project
 */

/**
 * Initialize multi-select functionality for adding team members
 */
function initAddMembers() {
  const form = document.getElementById("add-members-form");

  // Only initialize if the form exists on the page
  if (!form) return;

  const checkboxes = form.querySelectorAll(".member-checkbox");
  const submitBtn = form.querySelector("#add-members-btn");
  const selectedCount = document.getElementById("selected-count");
  const addMembersText = document.getElementById("add-members-text");
  const selectAllBtn = document.getElementById("select-all-members");
  const deselectAllBtn = document.getElementById("deselect-all-members");

  /**
   * Updates the UI based on current selection state
   */
  function updateUI() {
    const checkedCount = form.querySelectorAll(
      ".member-checkbox:checked",
    ).length;

    // Update counter badge
    selectedCount.textContent = `${checkedCount} selected`;
    selectedCount.classList.toggle("bg-info", checkedCount === 0);
    selectedCount.classList.toggle("bg-success", checkedCount > 0);

    // Update button state
    submitBtn.disabled = checkedCount === 0;

    // Update button text dynamically
    if (checkedCount === 0) {
      addMembersText.textContent = "Add Selected Members";
    } else if (checkedCount === 1) {
      addMembersText.textContent = "Add 1 Member";
    } else {
      addMembersText.textContent = `Add ${checkedCount} Members`;
    }
  }

  // Attach change listeners to all checkboxes
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateUI);
  });

  // Select All button handler
  if (selectAllBtn) {
    selectAllBtn.addEventListener("click", () => {
      checkboxes.forEach((cb) => {
        cb.checked = true;
      });
      updateUI();
    });
  }

  // Deselect All (Clear) button handler
  if (deselectAllBtn) {
    deselectAllBtn.addEventListener("click", () => {
      checkboxes.forEach((cb) => {
        cb.checked = false;
      });
      updateUI();
    });
  }

  // Initialize UI on page load
  updateUI();
}

/**
 * Initialize multi-select functionality for removing team members
 */
function initRemoveMembers() {
  const form = document.getElementById("remove-members-form");

  // Only initialize if the form exists on the page
  if (!form) return;

  const checkboxes = form.querySelectorAll(".remove-member-checkbox");
  const submitBtn = document.getElementById("remove-selected-btn");
  const removeText = document.getElementById("remove-selected-text");
  const selectAllBtn = document.getElementById("select-all-remove");
  const deselectAllBtn = document.getElementById("deselect-all-remove");
  const selectAllHeaderCheckbox = document.getElementById(
    "select-all-checkbox-header",
  );

  /**
   * Updates the UI based on current selection state
   */
  function updateUI() {
    const checkedCount = form.querySelectorAll(
      ".remove-member-checkbox:checked",
    ).length;

    // Update button state
    submitBtn.disabled = checkedCount === 0;

    // Update button text dynamically
    if (checkedCount === 0) {
      removeText.textContent = "Remove Selected (0)";
    } else if (checkedCount === 1) {
      removeText.textContent = "Remove Selected (1)";
    } else {
      removeText.textContent = `Remove Selected (${checkedCount})`;
    }

    // Update header checkbox state
    if (selectAllHeaderCheckbox) {
      const totalCheckboxes = checkboxes.length;
      selectAllHeaderCheckbox.checked =
        checkedCount > 0 && checkedCount === totalCheckboxes;
      selectAllHeaderCheckbox.indeterminate =
        checkedCount > 0 && checkedCount < totalCheckboxes;
    }

    // Update data-confirm-message dynamically based on selection
    updateConfirmMessage();
  }

  /**
   * Updates the confirmation message based on selected members
   */
  function updateConfirmMessage() {
    const checkedCheckboxes = form.querySelectorAll(
      ".remove-member-checkbox:checked",
    );
    const memberNames = Array.from(checkedCheckboxes)
      .map((cb) => cb.getAttribute("data-member-name"))
      .filter(Boolean);

    if (memberNames.length === 0) {
      return;
    }

    // Build confirmation message
    let message = `Are you sure you want to remove <strong>${memberNames.length}</strong> member${memberNames.length > 1 ? "s" : ""} from this project?<br><br>`;

    if (memberNames.length <= 5) {
      // Show names if 5 or fewer
      message += `<ul class="text-start mb-2">`;
      memberNames.forEach((name) => {
        message += `<li><strong>${name}</strong></li>`;
      });
      message += `</ul>`;
    }

    message += `They will lose access to all tasks and project data.`;

    // Update the button's data attribute for the confirmation modal
    submitBtn.setAttribute("data-confirm-message", message);
  }

  // Attach change listeners to all checkboxes
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateUI);
  });

  // Header checkbox handler (select/deselect all)
  if (selectAllHeaderCheckbox) {
    selectAllHeaderCheckbox.addEventListener("change", (e) => {
      const isChecked = e.target.checked;
      checkboxes.forEach((cb) => {
        cb.checked = isChecked;
      });
      updateUI();
    });
  }

  // Select All button handler
  if (selectAllBtn) {
    selectAllBtn.addEventListener("click", () => {
      checkboxes.forEach((cb) => {
        cb.checked = true;
      });
      updateUI();
    });
  }

  // Deselect All (Clear) button handler
  if (deselectAllBtn) {
    deselectAllBtn.addEventListener("click", () => {
      checkboxes.forEach((cb) => {
        cb.checked = false;
      });
      updateUI();
    });
  }

  // Initialize UI on page load
  updateUI();
}

/**
 * Main initialization function
 */
export function initProjectMembers() {
  initAddMembers();
  initRemoveMembers();
}
