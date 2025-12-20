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

  const form = $(e.currentTarget);
  const submitBtn = form.find('button[type="submit"]');
  const originalBtnText = submitBtn.text();

  submitBtn
    .html('<span class="spinner-border spinner-border-sm"></span> Loading...')
    .prop("disabled", true);

  post(form.attr("action"), form.serialize())
    // .done(function (response) {
    // if (response.success) {
    //   showToast(
    //     "success",
    //     response.message || "Action completed successfully!"
    //   );

    //   // Close any open Bootstrap modals
    //   const openModal = document.querySelector(".modal.show");
    //   if (openModal) {
    //     const modalInstance = bootstrap.Modal.getInstance(openModal);
    //     if (modalInstance) {
    //       modalInstance.hide();
    //     }
    //   }

    .done(function (response) {
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
        // if (form.data("reload")) {
        //   setTimeout(() => location.reload(), 3000); // Delay so toast is visible
        // } else if (form.data("redirect")) {
        //   setTimeout(
        //     () => (window.location.href = form.data("redirect")),
        //     3000
        //   );
        // } else {
        //   // Default:  800ms to show updated data
        //   setTimeout(() => location.reload(), 3000);
        // }

        // Handle reload/redirect
        if (form.data("reload")) {
          setTimeout(() => {
            window.scrollTo({ top: 0, behavior: "instant" }); // Instant scroll before reload
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
      }
    })
    // .fail(function (jqXHR, textStatus, errorThrown) {
    //   let errorMsg = "Network error. Please try again.";
    //   if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
    //     errorMsg = jqXHR.responseJSON.message;
    //   }
    //   showToast("danger", errorMsg);
    // })
    .fail(function (jqXHR, textStatus, errorThrown) {
      let errorMsg = "Network error. Please try again.";
      if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
        errorMsg = jqXHR.responseJSON.message;
      }
      showToast("danger", errorMsg);

      // For errors, also reload to show Bootstrap alert
      if (form.data("reload")) {
        setTimeout(() => location.reload(), 3000);
      }
    })

    .always(function () {
      submitBtn.html(originalBtnText).prop("disabled", false);
    });
}

/**
 * Changes the status of a task (Kanban drag & drop)
 */
// export function changeTaskStatus(taskId, newStatus) {
//   post("controllers/task_controller.php", {
//     action: "change_status",
//     task_id: taskId,
//     status: newStatus,
//   })
//     .done(function (response) {
//       if (response.success) {
//         showToast("success", response.message || "Status updated!");
//       } else {
//         showToast("danger", response.message || "Failed to update status.");
//       }
//     })
//     .fail(function (jqXHR, textStatus, errorThrown) {
//       let errorMsg = "Connection failed. Please try again.";
//       if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
//         errorMsg = jqXHR.responseJSON.message;
//       }
//       showToast("danger", errorMsg);
//     });
// }

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

/**
 * Optional helper for DELETE actions
 */
export function deleteResource(url, data, confirmMessage = "Are you sure?") {
  if (!confirm(confirmMessage)) {
    return Promise.resolve(false);
  }

  return post(url, data)
    .done(function (response) {
      if (response.success) {
        showToast("success", response.message || "Deleted successfully!");

        // Close modal if open
        const openModal = document.querySelector(".modal.show");
        if (openModal) {
          const modalInstance = bootstrap.Modal.getInstance(openModal);
          if (modalInstance) {
            modalInstance.hide();
          }
        }

        // Reload to reflect deletion
        setTimeout(() => location.reload(), 3000);
      } else {
        showToast("danger", response.message || "Failed to delete.");
      }
    })
    .fail(function (jqXHR) {
      showToast("danger", "Delete failed. Please try again.");
    });
}
