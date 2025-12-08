import { showToast } from "./ui.js";
import { escapeHtml } from "./utils.js";
import { loadModalContent } from "./taskModal.js";

function handleAddComment(e) {
  e.preventDefault();
  const form = $(e.currentTarget);
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
        showToast("success", "Comment added successfully!", 3000);
        commentsList.find(".alert-info").remove();

        if (commentsList.find(".list-group").length === 0) {
          commentsList.html('<div class="list-group"></div>');
        }

        const newCommentHtml = `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">${escapeHtml(
                              response.comment.user_name
                            )}</h5>
                            <small>${escapeHtml(
                              response.comment.created_at
                            )}</small>
                        </div>
                        <p class="mb-1">${escapeHtml(
                          response.comment.content
                        )}</p>
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
      showToast("danger", errorMessage);
      errorDiv.text(errorMessage).show();
    },
    complete: function () {
      submitBtn.html(originalBtnText);
      submitBtn.prop("disabled", false);
    },
  });
}

function handleEditCommentClick(e) {
  const commentId = $(e.currentTarget).data("comment-id");
  const modalEl = document.getElementById("editCommentModal");
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

  modalInstance.show();

  const modalTitle = $("#editCommentModalTitle");
  const modalBody = $("#edit-comment-modal-body");

  loadModalContent(
    "controllers/task_controller.php",
    { action: "get_comment_edit_form", comment_id: commentId },
    modalTitle,
    modalBody
  );
}

export function initComments() {
  $("#add-comment-form").on("submit", handleAddComment);
  $(document).on("click", ".edit-comment-btn", handleEditCommentClick);
}
