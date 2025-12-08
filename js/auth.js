
function handleLogin(e) {
    e.preventDefault();
    const form = $(e.currentTarget);
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
}


export function initAuth() {
    $("#login-form").on("submit", handleLogin);
}
