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
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  });
}

/**
 * Generic handler for AJAX forms
 */
export function handleAjaxFormSubmit(e) {
  e.preventDefault();
  e.stopImmediatePropagation();

  console.log("AJAX Handler Called!");

  // Get the ACTUAL form that was submitted (from the event)
  const form = $(e.target);

  // Find the button that was clicked (stored in the event)
  let submitBtn = $(document.activeElement); // Get the currently focused element (the button that was clicked)

  // Fallback: if activeElement isn't a submit button, find it in the form
  if (!submitBtn.is('button[type="submit"]')) {
    submitBtn = form.find('button[type="submit"]');
  }

  console.log("Submit button:", submitBtn.length, submitBtn);

  if (submitBtn.length === 0) {
    console.error("No submit button found!");
    // Fallback: just submit without spinner
    return;
  }

  const originalBtnText = submitBtn.text();
  const originalBtnHtml = submitBtn.html();
  const startTime = Date.now();

  // Show bigger spinner with better spacing
  submitBtn
    .html('<span class="spinner-border me-2"></span> Loading...')
    .prop("disabled", true);

  post(form.attr("action"), form.serialize())
    .done(function (response) {
      // Minimum spinner display time (800ms)
      const minSpinnerDuration = 800;
      const elapsedTime = Date.now() - startTime;
      const remainingTime = Math.max(0, minSpinnerDuration - elapsedTime);

      // Wait for minimum spinner duration before processing response
      setTimeout(() => {
        if (response.success) {
          showToast(
            "success",
            response.message || "Action completed successfully!"
          );

          // Close any open Bootstrap modals
          const openModal = document.querySelector(".modal.show");
          if (openModal) {
            const modalInstance = bootstrap.Modal.getInstance(openModal);
            if (modalInstance) {
              modalInstance.hide();
            }
          }

          // Handle reload/redirect
          if (form.data("reload")) {
            setTimeout(() => {
              window.scrollTo({ top: 0, behavior: "instant" });
              location.reload();
            }, 3000);
          } else if (form.data("redirect")) {
            setTimeout(() => {
              window.scrollTo({ top: 0, behavior: "instant" });
              window.location.href = form.data("redirect");
            }, 3000);
          } else {
            setTimeout(() => {
              window.scrollTo({ top: 0, behavior: "instant" });
              location.reload();
            }, 3000);
          }
        } else {
          showToast("danger", response.message || "An error occurred.");

          // Reload to show Bootstrap alert for errors too
          if (form.data("reload")) {
            setTimeout(() => {
              window.scrollTo({ top: 0, behavior: "instant" });
              location.reload();
            }, 3000);
          }
        }

        // Restore button after minimum duration
        submitBtn.html(originalBtnHtml).prop("disabled", false);
      }, remainingTime);
    })
    // .fail(function (jqXHR, textStatus, errorThrown) {
    //   // Minimum spinner display time (800ms)
    //   const minSpinnerDuration = 800;
    //   const elapsedTime = Date.now() - startTime;
    //   const remainingTime = Math.max(0, minSpinnerDuration - elapsedTime);

    //   setTimeout(() => {
    //     let errorMsg = "Network error. Please try again.";
    //     if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
    //       errorMsg = jqXHR.responseJSON.message;
    //     }
    //     showToast("danger", errorMsg);

    //     // For errors, also reload to show Bootstrap alert
    //     if (form.data("reload")) {
    //       setTimeout(() => {
    //         window.scrollTo({ top: 0, behavior: "instant" });
    //         location.reload();
    //       }, 3000);
    //     }

    //     // Restore button after minimum duration
    //     submitBtn.html(originalBtnHtml).prop("disabled", false);
    //   }, remainingTime);
    // });

    .fail(function (jqXHR, textStatus, errorThrown) {
      // Minimum spinner display time (800ms)
      const minSpinnerDuration = 800;
      const elapsedTime = Date.now() - startTime;
      const remainingTime = Math.max(0, minSpinnerDuration - elapsedTime);

      setTimeout(() => {
        // SESSION EXPIRED HANDLING
        if (
          jqXHR.status === 401 &&
          jqXHR.responseJSON &&
          jqXHR.responseJSON.error === "session_expired"
        ) {
          showToast("warning", jqXHR.responseJSON.message);

          setTimeout(() => {
            window.location.href = "index.php?page=login";
          }, 1500);

          return; // stop normal error handling
        }

        let errorMsg = "Network error. Please try again.";
        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          errorMsg = jqXHR.responseJSON.message;
        }
        showToast("danger", errorMsg);

        // For errors, also reload to show Bootstrap alert
        if (form.data("reload")) {
          setTimeout(() => {
            window.scrollTo({ top: 0, behavior: "instant" });
            location.reload();
          }, 3000);
        }

        // Restore button after minimum duration
        submitBtn.html(originalBtnHtml).prop("disabled", false);
      }, remainingTime);
    });
}

/**
 * Changes the status of a task (Kanban drag & drop)
 */
export function changeTaskStatus(taskId, newStatus) {
  post("backend/controllers/task_controller.php", {
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

      // ALWAYS reload after 3 seconds (for both success and error)
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: "instant" });
        location.reload();
      }, 3000);
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      let errorMsg = "Connection failed. Please try again.";
      if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
        errorMsg = jqXHR.responseJSON.message;
      }
      showToast("danger", errorMsg);

      // Also reload on network failure
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: "instant" });
        location.reload();
      }, 3000);
    });
}
