$(document).ready(function () {
  let pendingAction = null;

  $(document).on("click", "[data-confirm-delete]", function (e) {
    e.preventDefault();

    const button = $(this);
    const form = button.closest("form");

    if (!form.length) {
      console.error("No form found for confirm button");
      return;
    }

    pendingAction = function () {
      if (form.hasClass("ajax-form")) {
        // AJAX form: temporarily make button submit type to trigger handler
        button.prop("type", "submit");
        form.submit();
        setTimeout(() => button.prop("type", "button"), 100);
      } else {
        // Normal form
        form.submit();
      }
    };

    // pendingAction = function () {
    //   form.trigger("submit");
    // };

    // === Dynamic Modal Customization ===

    // Default: destructive (delete)
    let headerClass = "bg-danger text-white";
    let btnClass = "btn-danger";
    let btnText = "Yes, Delete";
    let icon = "fa-exclamation-triangle";
    let title = "Confirm Deletion";

    // Check for custom type (e.g., reset password)
    const actionType = button.data("confirm-type") || form.data("confirm-type");

    if (actionType === "reset-password") {
      headerClass = "bg-warning text-dark";
      btnClass = "btn-warning";
      btnText = "Yes, Reset Password";
      icon = "fa-key";
      title = "Reset Password";
    }
    // You can add more types later: 'archive', 'disable', etc.

    // Custom message (with fallback)
    const message =
      button.data("confirm-message") ||
      form.data("confirm-message") ||
      "Are you sure you want to perform this action?";

    // Apply dynamic styles
    $("#confirmModalHeader")
      .removeClass("bg-danger bg-warning text-white text-dark")
      .addClass(headerClass);

    $("#confirmModalLabel").html(`<i class="fas ${icon} me-2"></i>${title}`);

    $("#confirmModalMessage").html(message);

    $("#confirmModalBtn")
      .removeClass("btn-danger btn-warning")
      .addClass(btnClass)
      .text(btnText);

    $("#confirmDeleteModal").modal("show");
  });

  $("#confirmModalBtn").on("click", function () {
    if (pendingAction) {
      pendingAction();
    }
    $("#confirmDeleteModal").modal("hide");
  });

  $("#confirmDeleteModal").on("hidden.bs.modal", function () {
    pendingAction = null;
    // Reset to default delete style for next use
    $("#confirmModalHeader")
      .removeClass()
      .addClass("modal-header bg-danger text-white");
    $("#confirmModalLabel").html(
      '<i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion'
    );
    $("#confirmModalBtn")
      .removeClass()
      .addClass("btn btn-danger")
      .text("Yes, Delete");
  });
});
