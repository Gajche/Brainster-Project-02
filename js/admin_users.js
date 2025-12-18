// js/admin_users.js - Handles Team Lead toggle
export function init() {
  // Generic toggle function for level selects
  function toggleTeamLead(selectEl) {
    const teamLeadId = selectEl.dataset.teamLeadId;
    const teamLeadCheckbox = document.getElementById(teamLeadId);
    const container =
      teamLeadCheckbox?.closest(".form-check") ||
      document.getElementById("team-lead-container");

    if (teamLeadCheckbox && container) {
      if (selectEl.value !== "Senior") {
        container.style.display = "none";
        teamLeadCheckbox.disabled = true;
        teamLeadCheckbox.checked = false;
      } else {
        container.style.display = "block";
        teamLeadCheckbox.disabled = false;
      }
    }
  }

  // Attach to all .level-select elements (create and edits)
  document.querySelectorAll(".level-select").forEach((select) => {
    select.addEventListener("change", () => toggleTeamLead(select));
    toggleTeamLead(select); // Initial state
  });
}

// Auto-initialize if script is loaded directly (backward compatibility)
// Remove this after confirming the new system works
if (typeof module !== "undefined" && module.exports) {
  // Node/CommonJS environment
} else {
  // Browser - check if we're not being imported as a module
  document.addEventListener("DOMContentLoaded", function () {
    // Only auto-init if not being imported via main.js
    if (
      !document.body.dataset.page ||
      document.body.dataset.page !== "admin_users"
    ) {
      init();
    }
  });
}
