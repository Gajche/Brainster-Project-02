import { configureToastr } from "./ui.js";
import {
  initAuth,
  initKanban,
  initTaskModals,
  initComments,
  initProjectMembers,
} from "./modules.js";
import { handleAjaxFormSubmit } from "./api.js";
import { validateForm, validateField } from "./validation.js";
import { initPageUtils } from "./pageUtils.js";
import { initSystemHealthCheck } from "./systemHealth.js";

initPageUtils();

// Page-specific module loader
async function loadPageSpecificModules() {
  const page = document.body.dataset.page;

  switch (page) {
    case "admin_users": {
      const adminUsersModule = await import("./admin_users.js");
      if (adminUsersModule.init) adminUsersModule.init();
      break;
    }

    case "dashboard":
    case "project_view":
      break;
  }
}

$(function () {
  // Smooth scroll to top if there's a flash message (after page loads)
  if (
    $(".alert-success, .alert-danger, .alert-warning, .alert-info").length > 0
  ) {
    setTimeout(() => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }, 100); // Small delay to ensure page is fully loaded
  }

  // Toastr
  if (typeof toastr !== "undefined") {
    configureToastr();
  }

  // System health check (DB / MySQL / Apache)
  initSystemHealthCheck();

  // Init core modules
  [
    initAuth,
    initKanban,
    initTaskModals,
    initComments,
    initProjectMembers, // Initialize project members multi-select
  ].forEach((init) => init());

  loadPageSpecificModules().catch(console.error);

  // FORM SUBMIT (ALL FORMS)
  $(document).on("submit", "form", function (e) {
    // For AJAX forms, prevent default FIRST
    if ($(this).hasClass("ajax-form")) {
      e.preventDefault();
      e.stopPropagation();

      // Then validate
      if (!validateForm(this)) {
        $(this).find(".is-invalid").first().focus();
        return false;
      }

      // If validation passes, handle AJAX
      handleAjaxFormSubmit(e);
      return false;
    }

    // For non-AJAX forms, validate normally
    if (!validateForm(this)) {
      e.preventDefault();
      $(this).find(".is-invalid").first().focus();
      return false;
    }
  });

  // LIVE VALIDATION

  // Validate on blur
  $(document).on("blur", "input, textarea, select", function () {
    this.dataset.touched = "true";
    validateField(this);
  });

  // Revalidate on input if touched
  $(document).on("input", "input, textarea, select", function () {
    if (this.dataset.touched === "true") {
      validateField(this);
    }
  });

  // GLOBAL AJAX ERROR
  $(document).ajaxError((e, jqXHR) => {
    if (typeof toastr === "undefined" || jqXHR.status === 0) return;

    let errorMsg = "Request failed";

    try {
      const json = JSON.parse(jqXHR.responseText);
      errorMsg = json?.message || errorMsg;
    } catch {}

    toastr.error(errorMsg);
  });
});
