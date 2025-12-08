import { showToast } from "./ui.js";

export function loadModalContent(url, data, modalTitle, modalBody) {
  modalTitle.text("Loading...");
  modalBody.html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

  return $.get(url, data, null, "json")
    .done(function (response) {
      if (response.success) {
        modalTitle.text(response.title);
        modalBody.html(response.body);
      } else {
        modalTitle.text("Error");
        modalBody.html(
          `<p class="alert alert-danger">${
            response.message || "Could not load content."
          }</p>`
        );
      }
    })
    .fail(function () {
      modalTitle.text("Error");
      modalBody.html(
        '<p class="alert alert-danger">Failed to load details. Please try again.</p>'
      );
      showToast("danger", "Failed to load task details.");
    });
}

function handleViewTaskClick(e) {
  const taskId = $(e.currentTarget).data("task-id");
  const modalEl = document.getElementById("taskDetailsModal");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

  modalInstance.show();

  const modalTitle = $("#taskDetailsModalTitle");
  const modalBody = $("#task-details-modal-body");

  loadModalContent(
    "controllers/task_controller.php",
    { action: "get_task_details_html", task_id: taskId },
    modalTitle,
    modalBody
  );
}

function handleEditTaskClick(e) {
  const taskId = $(e.currentTarget).data("task-id");
  const modalEl = document.getElementById("editTaskModal");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

  modalInstance.show();

  const modalTitle = $("#editTaskModalTitle");
  const modalBody = $("#edit-task-modal-body");

  loadModalContent(
    "controllers/task_controller.php",
    { action: "get_task_edit_form", task_id: taskId },
    modalTitle,
    modalBody
  );
}

export function initTaskModals() {
  $(document).on("click", ".view-task-btn", handleViewTaskClick);
  $(document).on("click", ".edit-task-btn", handleEditTaskClick);
}
