/**
 * Displays a beautiful Bootstrap toast notification
 * @param {string} type - 'success' | 'danger' | 'warning' | 'info'
 * @param {string} message - The message to show
 * @param {number} [delay=5000] - Auto-hide delay in ms (default: 5 seconds)
 */
export function showToast(type = "success", message, delay = 5000) {
  const validTypes = ["success", "danger", "warning", "info"];
  const toastType = validTypes.includes(type) ? type : "info";

  const toastHtml = `
    <div class="toast align-items-center text-white bg-${toastType} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body fw-medium">
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;

  // Create container if not exists
  let $container = $(".toast-container");
  if ($container.length === 0) {
    $container = $(
      '<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>'
    );
    $("body").append($container);
  }

  const $toast = $(toastHtml);
  $container.append($toast);

  // Initialize and show toast with custom delay
  const toastInstance = new bootstrap.Toast($toast[0], {
    delay: delay,
    autohide: true,
  });

  toastInstance.show();

  // Clean up when hidden
  $toast.on("hidden.bs.toast", function () {
    $(this).remove();
  });
}
