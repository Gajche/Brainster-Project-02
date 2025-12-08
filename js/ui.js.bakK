/**
 * Displays a Bootstrap toast notification using jQuery.
 * @param {string} type The type of toast (e.g., 'success', 'danger').
 * @param {string} message The message to display.
 */
export function showToast(type, message) {
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
    $("body").append('<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
    toastContainer = $(".toast-container");
  }

  const toastEl = $(toastHtml);
  toastContainer.append(toastEl);

  const toast = new bootstrap.Toast(toastEl[0]);
  toast.show();

  toastEl.on("hidden.bs.toast", function () {
    $(this).remove();
  });
}
