import { showToast } from "./ui.js";

function handleLogin(e) {
  e.preventDefault();
  e.stopImmediatePropagation(); //  Prevent duplicate handlers

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
      if (response.success === true) {
        showToast("success", response.message || "Login successful!");

        const redirectUrl =
          response.data?.redirect || "index.php?page=dashboard";
        setTimeout(() => {
          window.location.href = redirectUrl;
        }, 2000);
      } else {
        errorDiv.text(response.message || "Login failed.").show();
      }
    },
    error: function (jqXHR) {
      let msg = "An unknown error occurred.";
      if (jqXHR.responseJSON?.message) {
        msg = jqXHR.responseJSON.message;
      }
      errorDiv.text(msg).show();
    },
    complete: function () {
      submitBtn.html(originalBtnText);
      submitBtn.prop("disabled", false);
    },
  });
}

export function initAuth() {
  // Remove the ajax-form class if it exists to prevent double handling
  const loginForm = $("#login-form");
  loginForm.removeClass("ajax-form");
  loginForm.on("submit", handleLogin);
}
