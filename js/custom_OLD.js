$(function () {
  // ============================
  // LOGIN FORM HANDLER
  // ============================
  $("#login-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    const errorDiv = $("#login-error");
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();

    errorDiv.hide();
    submitBtn.html(
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...'
    );
    submitBtn.prop("disabled", true);

    $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: form.serialize(),
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          window.location.href = response.redirect;
        } else {
          errorDiv
            .text(response.message || "An unexpected error occurred.")
            .show();
        }
      },
      error: function (jqXHR) {
        let errorMessage = "An unknown error occurred.";
        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          errorMessage = jqXHR.responseJSON.message;
        }
        errorDiv.text(errorMessage).show();
      },
      complete: function () {
        submitBtn.html(originalBtnText);
        submitBtn.prop("disabled", false);
      },
    });
  });

  // ============================
  // ADD COMMENT FORM (MAIN PAGE)
  // ============================
  $("#add-comment-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    const errorDiv = $("#comment-error");
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    const commentsList = $("#comments-list");
    const textarea = form.find("textarea");

    errorDiv.hide();
    submitBtn.html(
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...'
    );
    submitBtn.prop("disabled", true);

    $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: form.serialize(),
      dataType: "json",
      success: function (response) {
        if (response.status === "success" && response.comment) {
          commentsList.find(".alert-info").remove();

          // Ensure container exists
          if (commentsList.find(".list-group").length === 0) {
            commentsList.html('<div class="list-group"></div>');
          }

          const newCommentHtml = `
            <div class="list-group-item">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">${escapeHtml(response.comment.user_name)}</h5>
                <small>${escapeHtml(response.comment.created_at)}</small>
              </div>
              <p class="mb-1">${escapeHtml(response.comment.content)}</p>
            </div>
          `;

          commentsList.find(".list-group").append(newCommentHtml);
          textarea.val("");
        }
      },
      error: function (jqXHR) {
        let errorMessage = "An unknown error occurred.";
        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          errorMessage = jqXHR.responseJSON.message;
        }
        errorDiv.text(errorMessage).show();
      },
      complete: function () {
        submitBtn.html(originalBtnText);
        submitBtn.prop("disabled", false);
      },
    });
  });

  // ============================
  // KANBAN DRAG + DROP
  // ============================
  const kanbanColumns = document.querySelectorAll(".kanban-cards");
  if (kanbanColumns.length > 0) {
    kanbanColumns.forEach((column) => {
      new Sortable(column, {
        group: "kanban",
        animation: 150,
        ghostClass: "sortable-ghost",
        onEnd: function (evt) {
          const itemEl = evt.item;
          const toColumn = evt.to;

          const taskId = itemEl.dataset.taskId;
          const newStatus = toColumn.parentElement.dataset.status;
          const controllerUrl = "controllers/task_controller.php";

          itemEl.style.opacity = "0.5";

          $.post(
            controllerUrl,
            {
              action: "change_status",
              task_id: taskId,
              status: newStatus,
            },
            function (response) {
              if (response.status === "success") {
                showToast("success", response.message || "Status updated!");
              } else {
                showToast("danger", response.message || "Update failed.");
              }
            },
            "json"
          )
            .fail(function (jqXHR) {
              let errorMsg = "Request failed.";
              if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMsg = jqXHR.responseJSON.message;
              }
              showToast("danger", errorMsg);
            })
            .always(function () {
              itemEl.style.opacity = "1";
            });
        },
      });
    });
  }

  // ============================
  // HELPER: ESCAPE HTML
  // ============================
  function escapeHtml(text) {
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }

  // ============================
  // GENERIC AJAX FORM HANDLER
  // ============================
  $(document).on("submit", "form.ajax-form", function (e) {
    e.preventDefault();
    const form = $(this);
    const action = form.attr("action");
    const data = form.serialize();
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.text();

    submitBtn.html(
      '<span class="spinner-border spinner-border-sm"></span> Loading...'
    );
    submitBtn.prop("disabled", true);

    $.post(
      action,
      data,
      function (response) {
        submitBtn.html(originalBtnText);
        submitBtn.prop("disabled", false);

        if (response.success) {
          showToast("success", response.message || "Success!");

          if (form.data("reload")) {
            const reloadAction = form.data("reload");

            if (reloadAction === "task-modal") {
              const taskId = form.data("task-id");
              if (taskId) {
                // Find and hide the current modal before reloading the parent
                const nestedModalEl = form.closest(".modal");
                if (nestedModalEl.length > 0) {
                  const nestedModalInstance = bootstrap.Modal.getInstance(
                    nestedModalEl[0]
                  );
                  if (nestedModalInstance) {
                    // Hide the modal cleanly, then reload the parent modal's content
                    nestedModalEl.on("hidden.bs.modal", function () {
                      loadTaskModal(taskId);
                      // Make sure to remove the event listener to prevent it from firing multiple times
                      $(this).off("hidden.bs.modal");
                    });
                    nestedModalInstance.hide();
                  } else {
                    // Fallback if instance isn't found for some reason
                    loadTaskModal(taskId);
                  }
                }
              }
            } else {
              // Fallback to old, less robust logic
              const reloadSelector = reloadAction;
              $.get(window.location.href, function (htmlResponse) {
                const newContent = $(htmlResponse).find(reloadSelector).html();
                $(reloadSelector).html(
                  newContent || "<p>Content could not be reloaded.</p>"
                );
              }).fail(function () {
                showToast("danger", "Failed to reload content.");
              });
            }
          } else {
            location.reload();
          }
        } else {
          showToast("danger", response.message || "Error occurred.");
        }
      },
      "json"
    ).fail(function () {
      submitBtn.html(originalBtnText);
      submitBtn.prop("disabled", false);
      showToast("danger", "Request failed.");
    });
  });

  // ============================
  // TOAST FUNCTION
  // ============================
  function showToast(type, message) {
    const toastHtml = `
      <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    `;

    let toastContainer = $(".toast-container");
    if (toastContainer.length === 0) {
      $("body").append(
        '<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>'
      );
      toastContainer = $(".toast-container");
    }

    toastContainer.append(toastHtml);
    const toastEl = toastContainer.find(".toast").last();
    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    toastEl.on("hidden.bs.toast", function () {
      $(this).remove();
    });
  }

  // ============================
  // TASK MODAL LOADER FUNCTION
  // ============================
  function loadTaskModal(taskId) {
    const modalTitle = $("#taskDetailsModalTitle");
    const modalBody = $("#task-details-modal-body");

    // Show a loading state
    modalTitle.text("Loading...");
    modalBody.html(`
      <div class="text-center">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    `);

    // Return the AJAX promise
    return $.get(
      "controllers/task_controller.php",
      {
        action: "get_task_details_html",
        task_id: taskId,
      },
      null,
      "json"
    )
      .done(function (response) {
        if (response.success) {
          modalTitle.text(response.title);
          modalBody.html(response.body);
        } else {
          modalTitle.text("Error");
          modalBody.html(
            `<p class="alert alert-danger">${
              response.message || "Could not load task."
            }</p>`
          );
        }
      })
      .fail(function () {
        modalTitle.text("Error");
        modalBody.html(
          '<p class="alert alert-danger">Failed to load task details. Please try again.</p>'
        );
      });
  }
  // ============================
  // TASK MODAL TRIGGER
  // ============================
  $(document).on("click", ".view-task-btn", function () {
    const taskId = $(this).data("task-id");
    const modalEl = document.getElementById("taskDetailsModal");
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Show the modal immediately with loading content
    modalInstance.show();

    // Load the content after showing the modal
    loadTaskModal(taskId);
  });

  // ============================
  // EDIT TASK MODAL TRIGGER
  // ============================
  $(document).on("click", ".edit-task-btn", function () {
    console.log("Edit task button clicked.");
    const taskId = $(this).data("task-id");
    console.log("Task ID:", taskId);

    const modalBody = $("#edit-task-modal-body");
    const modalEl = document.getElementById("editTaskModal");
    if (!modalEl) {
      console.error("#editTaskModal not found in the DOM.");
      return;
    }
    const modal = new bootstrap.Modal(modalEl);

    modalBody.html("Loading...");
    modal.show();

    console.log("AJAX request sent for task form...");
    $.get(
      "controllers/task_controller.php",
      { action: "get_task_edit_form", task_id: taskId },
      function (response) {
        console.log("AJAX response received for task form:", response);
        if (response.success) {
          modalBody.html(response.body);
        } else {
          modalBody.html(
            `<p class="alert alert-danger">${
              response.message || "Failed to load task form."
            }</p>`
          );
        }
      },
      "json"
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.error("AJAX task form request failed:", textStatus, errorThrown);
      console.error("Response Text:", jqXHR.responseText);
      modalBody.html(
        '<p class="alert alert-danger">An error occurred while loading the form.</p>'
      );
    });
  });

  // ============================
  // EDIT COMMENT MODAL TRIGGER
  // ============================
  $(document).on("click", ".edit-comment-btn", function () {
    console.log("Edit comment button clicked.");
    const commentId = $(this).data("comment-id");
    console.log("Comment ID:", commentId);

    const modalBody = $("#edit-comment-modal-body");
    const modalEl = document.getElementById("editCommentModal");
    if (!modalEl) {
      console.error("#editCommentModal not found in the DOM.");
      return;
    }
    const modal = new bootstrap.Modal(modalEl);

    modalBody.html("Loading...");
    modal.show();

    console.log("AJAX request sent for comment form...");
    $.get(
      "controllers/task_controller.php",
      { action: "get_comment_edit_form", comment_id: commentId },
      function (response) {
        console.log("AJAX response received for comment form:", response);
        if (response.success) {
          modalBody.html(response.body);
        } else {
          modalBody.html(
            `<p class="alert alert-danger">${
              response.message || "Failed to load comment form."
            }</p>`
          );
        }
      },
      "json"
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.error(
        "AJAX comment form request failed:",
        textStatus,
        errorThrown
      );
      console.error("Response Text:", jqXHR.responseText);
      modalBody.html(
        '<p class="alert alert-danger">An error occurred while loading the form.</p>'
      );
    });
  });
});
