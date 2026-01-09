import { showToast } from "./ui.js";

// Check system health on page load
export function initSystemHealthCheck() {
  $.ajax({
    url: "backend/api/system_health.php",
    method: "GET",
    dataType: "json",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .done(() => {})
    .fail((jqXHR) => {
      if (jqXHR.status === 503 && jqXHR.responseJSON?.error === "db_missing") {
        showSystemDbModal();
        // "The database is missing.\n Check if XAMMP is opened, MySQL and Apache started.\n Otherwise \n Please initialize it below."
        return;
      }

      // Fallback: real system error - use consistent toast
      showToast(
        "error",
        jqXHR.responseJSON?.message ||
          "System error. Please check Apache / MySQL."
      );
    });
}

// Show modal to initialize missing database
function showSystemDbModal(
  message = "The database is missing.\n\nEnsure XAMPP is running with MySQL & Apache started.\n(Start MySQL & Apache, then refresh browser)\n\nOtherwise, please initialize the database below."
) {
  const modalEl = document.getElementById("systemDbModal");
  if (!modalEl) return;

  modalEl.querySelector("#systemDbMessage").innerText = message;

  const modal = new bootstrap.Modal(modalEl, {
    backdrop: "static",
    keyboard: false,
  });

  modal.show();
}

// Handle database initialization button click
$(document).on("click", "#systemDbInitBtn", function (e) {
  e.preventDefault();
  e.stopPropagation();
  if ($(this).prop("disabled")) return; // Prevent double-clicks
  runDatabaseInit();
});

// Run database initialization via AJAX
function runDatabaseInit() {
  const btn = document.getElementById("systemDbInitBtn");
  btn.disabled = true;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Initializing...';

  $.ajax({
    url: "database/create_db.php",
    method: "POST",
    dataType: "json",
    timeout: 30000, // Prevent hanging if server unresponsive
  })
    .done((response) => {
      showToast(
        "success",
        response?.message || "Database initialized successfully!"
      );
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Success! Reloading...';
      setTimeout(() => location.reload(), 3000);
    })
    .fail((jqXHR) => {
      let message = "Database initialization failed.";
      if (jqXHR.responseJSON?.message) {
        message = jqXHR.responseJSON.message;
      } else if (jqXHR.status === 0 || jqXHR.readyState === 0) {
        message =
          "Cannot connect to the database. Please ensure MySQL is running in XAMPP and try again.";
      }

      showToast("error", message);

      btn.disabled = false;
      btn.innerHTML = "Retry Setup";
    });
}
