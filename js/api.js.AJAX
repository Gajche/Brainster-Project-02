import { showToast } from "./ui.js";

/**
 * A wrapper for making POST requests using jQuery's ajax method.
 * @param {string} url The URL to send the request to.
 * @param {object} data The data to send.
 * @returns {Promise} A jQuery promise.
 */
function post(url, data) {
  return $.ajax({
    url: url,
    type: "POST",
    data: data,
    dataType: "json",
  });
}

/**
 * Generic handler for AJAX forms
 */
export function handleAjaxFormSubmit(e) {
  e.preventDefault();

  const form = $(e.currentTarget);
  const submitBtn = form.find('button[type="submit"]');
  const originalBtnText = submitBtn.text();

  submitBtn
    .html('<span class="spinner-border spinner-border-sm"></span> Loading...')
    .prop("disabled", true);

  post(form.attr("action"), form.serialize())
    .done(function (response) {
      if (response.success) {
        showToast(
          "success",
          response.message || "Action completed successfully!"
        );
        if (form.data("reload")) {
          location.reload();
        }
      } else {
        showToast("danger", response.message || "An error occurred.");
      }
    })
    .fail(function () {
      showToast("danger", "Network error. Please try again.");
    })
    .always(function () {
      submitBtn.html(originalBtnText).prop("disabled", false);
    });
}

/**
 * Changes the status of a task (Kanban drag & drop)
 */
export function changeTaskStatus(taskId, newStatus) {
  post("controllers/task_controller.php", {
    action: "change_status",
    task_id: taskId,
    status: newStatus,
  })
    .done(function (response) {
      if (response.success) {
        showToast("success", response.message || "Status updated!");
      } else {
        showToast("danger", response.message || "Failed to update status.");
      }
    })
    .fail(function () {
      showToast("danger", "Connection failed. Please try again.");
    });
}
