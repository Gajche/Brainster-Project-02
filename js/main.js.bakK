import { initAuth } from "./auth.js";
import { initKanban } from "./kanban.js";
import { initTaskModals } from "./taskModal.js";
import { initComments } from "./comment.js";
import { handleAjaxFormSubmit } from "./api.js";

$(function () {
  // Initialize all modules
  initAuth();
  initKanban();
  initTaskModals();
  initComments();

  // Generic AJAX form handler
  $(document).on("submit", "form.ajax-form", handleAjaxFormSubmit);
});
