/**
 * Toastr notification wrappers and configuration
 * @param {'success'|'danger'|'warning'|'info'} type - Toast type
 * @param {string} message - Message to display
 * @param {number} [delay] - Auto-hide delay in ms (uses global config if not specified)
 */
export function showToast(type = "success", message, delay) {
  const toastMap = {
    success: toastr.success,
    danger: toastr.error, // Map 'danger' to Toastr's 'error'
    warning: toastr.warning,
    info: toastr.info,
  };

  const toastFn = toastMap[type] || toastr.info;

  // Only override timeOut if delay is explicitly provided
  const options = delay ? { timeOut: delay } : {};
  toastFn(message, "", options);
}

/**
 * Configure Toastr once in main.js
 */
export function configureToastr() {
  toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-bottom-right",
    timeOut: 8000, // Changed to 8 seconds to match main.js
    extendedTimeOut: 1000,
    preventDuplicates: true, // Prevent duplicate toasts
  };
}

/**
 * Quick access methods (optional - use if you prefer)
 */
export const successToast = (msg, delay) =>
  toastr.success(msg, "", delay ? { timeOut: delay } : {});
export const errorToast = (msg, delay) =>
  toastr.error(msg, "", delay ? { timeOut: delay } : {});
export const warningToast = (msg, delay) =>
  toastr.warning(msg, "", delay ? { timeOut: delay } : {});
export const infoToast = (msg, delay) =>
  toastr.info(msg, "", delay ? { timeOut: delay } : {});
