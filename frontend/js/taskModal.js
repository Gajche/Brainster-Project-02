import { showToast } from "./ui.js";
// Load modal content via AJAX
export function loadModalContent(url, data, modalTitle, modalBody) {
  modalTitle.text("Loading...");
  modalBody.html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `);
  // AJAX request to fetch content
  return $.get(url, data)
    .done(function (response) {
      if (response.success && response.data) {
        modalTitle.text(response.data.title || "Task Details");
        modalBody.html(response.data.body || "<p>No content</p>");
      } else {
        modalTitle.text("Error");
        modalBody.html(
          `<div class="alert alert-danger">${response.message || "Error"}</div>`
        );
        showToast("danger", response.message || "Failed to load");
      }
    })
    .fail(function () {
      modalTitle.text("Error");
      modalBody.html('<div class="alert alert-danger">Network error</div>');
      showToast("danger", "Connection failed");
    });
}

// Handlers for opening modals
function handleViewTaskClick(e) {
  const taskId = $(e.currentTarget).data("task-id");
  const modalEl = document.getElementById("taskDetailsModal");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  modalInstance.show();

  const modalTitle = $("#taskDetailsModalTitle");
  const modalBody = $("#task-details-modal-body");

  loadModalContent(
    "backend/controllers/task_controller.php",
    { action: "get_task_details_html", task_id: taskId },
    modalTitle,
    modalBody
  );
}

// Edit Task Modal
function handleEditTaskClick(e) {
  const taskId = $(e.currentTarget).data("task-id");
  const modalEl = document.getElementById("editTaskModal");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  modalInstance.show();

  const modalTitle = $("#editTaskModalTitle");
  const modalBody = $("#edit-task-modal-body");

  loadModalContent(
    "backend/controllers/task_controller.php",
    { action: "get_task_edit_form", task_id: taskId },
    modalTitle,
    modalBody
  );
}

// Initialize event listeners for task modals
export function initTaskModals() {
  $(document).on("click", ".view-task-btn", handleViewTaskClick);
  $(document).on("click", ".edit-task-btn", handleEditTaskClick);
}
