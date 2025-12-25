/**
 * Toastr notification wrappers and configuration
 * @param {'success'|'danger'|'warning'|'info'} type - Toast type
 * @param {string} message - Message to display
 * @param {number} [delay] - Auto-hide delay in ms (uses global config if not specified)
 */
// export function showToast(type = "success", message, delay) {
//   const toastMap = {
//     success: toastr.success,
//     danger: toastr.error, // Map 'danger' to Toastr's 'error'
//     warning: toastr.warning,
//     info: toastr.info,
//   };

//   const toastFn = toastMap[type] || toastr.info;

//   // Only override timeOut if delay is explicitly provided
//   const options = delay ? { timeOut: delay } : {};
//   toastFn(message, "", options);
// }

export function showToast(type = "success", message, delay) {
  const toastMap = {
    success: toastr.success,
    danger: toastr.error,
    warning: toastr.warning,
    info: toastr.info,
  };

  const toastFn = toastMap[type] || toastr.info;

  // Make errors more persistent (longer display time)
  let options = delay ? { timeOut: delay } : {};

  if (type === "danger") {
    options.timeOut = 6000; // Errors show for 6 seconds instead of 3
    options.extendedTimeOut = 2000;
  }

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
    timeOut: 3000, // Changed to 3 seconds to match main.js
    extendedTimeOut: 1000,
    preventDuplicates: true, // Prevent duplicate toasts
  };
}

// export function configureToastr() {
//   toastr.options = {
//     closeButton: true,
//     progressBar: true,
//     positionClass: "toast-top-right", // or "toast-bottom-right"
//     timeOut: 4000, // Shorter is more professional (not 8s)
//     extendedTimeOut: 1000,
//     preventDuplicates: true,
//     newestOnTop: true,
//     showEasing: "swing",
//     hideEasing: "linear",
//     showMethod: "fadeIn",
//     hideMethod: "fadeOut",
//     tapToDismiss: true, // Click to dismiss
//   };
// }

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

/**
 * Bootstrap modal accessibility fix
 * Prevents console warning when closing modal with focus on close button (or any element)
 * Blurs active element before modal hides and applies aria-hidden
 */
document.addEventListener("hide.bs.modal", function () {
  if (document.activeElement) {
    document.activeElement.blur();
  }
});
