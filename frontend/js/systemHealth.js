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
  message = "The database is missing.\n Check if XAMMP is running, MySQL & Apache started.\n(start MySQL & Apache, then refresh browser)\n Otherwise\n Please initialize it below."
) {
  const modalEl = document.getElementById("systemDbModal");
  if (!modalEl) return;

  modalEl.querySelector("#systemDbMessage").innerText = message;

  const modal = new bootstrap.Modal(modalEl, {
    backdrop: "static",
    keyboard: false,
  });

  modal.show();

  document
    .getElementById("systemDbInitBtn")
    .addEventListener("click", runDatabaseInit, { once: true });
}

// Run database initialization via AJAX
function runDatabaseInit() {
  const btn = document.getElementById("systemDbInitBtn");
  btn.disabled = true;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Setting up...';

  $.ajax({
    url: "/database/create_db.php",
    method: "POST",
    dataType: "json",
  })
    .done((response) => {
      showToast(
        "success",
        response?.message || "Database initialized successfully!"
      );
      setTimeout(() => location.reload(), 3000);
    })
    .fail((jqXHR) => {
      showToast(
        "error",
        jqXHR.responseJSON?.message || "Database initialization failed."
      );
      btn.disabled = false;
      btn.innerHTML = "Retry Setup";
    });
}
