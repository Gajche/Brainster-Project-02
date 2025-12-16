// Main application entry point
import { configureToastr } from "./ui.js";
import {
  initAuth,
  initKanban,
  initTaskModals,
  initComments,
} from "./modules.js";
import { handleAjaxFormSubmit } from "./api.js";
import { validateForm } from "./validation.js";

let formsBound = false;

$(function () {
  // Configure Toastr ONCE with all settings
  if (typeof toastr !== "undefined") {
    configureToastr(); // This now sets timeOut to 8000ms
  }

  // Initialize modules
  [initAuth, initKanban, initTaskModals, initComments].forEach((init) =>
    init()
  );

  // Bind forms ONCE
  if (!formsBound) {
    $(document).on("submit", "form.ajax-form", function (e) {
      e.stopImmediatePropagation(); // STOP other handlers

      if (!validateForm(this)) {
        e.preventDefault();
        $(this).find(".is-invalid").first().focus();
        return;
      }

      e.preventDefault(); // Prevent default AFTER validation
      handleAjaxFormSubmit(e);
    });

    formsBound = true;
  }

  // Global AJAX error
  $(document).ajaxError((e, jqXHR) => {
    if (typeof toastr === "undefined" || jqXHR.status === 0) return;

    const errorMsg = (() => {
      try {
        return JSON.parse(jqXHR.responseText)?.message || "Server error";
      } catch {
        return "Request failed";
      }
    })();

    toastr.error(errorMsg);
  });
});
