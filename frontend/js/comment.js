// comment.js
import { showToast } from "./ui.js";
import { escapeHtml } from "./utils.js";
import { loadModalContent } from "./taskModal.js";

function handleAddComment(e) {
  e.preventDefault();
  const form = $(e.currentTarget);
  const submitBtn = form.find('button[type="submit"]');
  const originalBtnText = submitBtn.html();
  const commentsList = $("#comments-list");
  const textarea = form.find("textarea");

  submitBtn
    .html('<span class="spinner-border spinner-border-sm"></span> Adding...')
    .prop("disabled", true);

  $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    dataType: "json",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    success: function (response) {
      if (response.success && response.data?.comment) {
        showToast("success", response.message || "Comment added!");

        commentsList.find(".alert-info").remove();
        if (commentsList.find(".list-group").length === 0) {
          commentsList.html('<div class="list-group"></div>');
        }

        const comment = response.data.comment;
        const newCommentHtml = `
          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1">${escapeHtml(comment.user_name)}</h5>
              <small>${escapeHtml(comment.created_at)}</small>
            </div>
            <p class="mb-1">${escapeHtml(comment.content)}</p>
          </div>
        `;

        commentsList.find(".list-group").append(newCommentHtml);
        textarea.val("");
      } else {
        showToast("danger", response.message || "Failed to add comment.");
      }
    },
    error: function () {
      showToast("danger", "Network error.");
    },
    complete: function () {
      submitBtn.html(originalBtnText).prop("disabled", false);
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
    "backend/controllers/task_controller.php",
    { action: "get_comment_edit_form", comment_id: commentId },
    modalTitle,
    modalBody
  );
}

export function initComments() {
  $("#add-comment-form").on("submit", handleAddComment);
  $(document).on("click", ".edit-comment-btn", handleEditCommentClick);
  // NO form binding here - main.js handles all .ajax-form
}
