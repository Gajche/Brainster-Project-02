export function validateForm(form) {
  let valid = true;
  form.querySelectorAll("input, textarea, select").forEach((field) => {
    const value = field.value.trim();
    if (field.required && !value) {
      showError(field, "Required");
      valid = false;
    } else if (
      field.type === "email" &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    ) {
      showError(field, "Invalid email");
      valid = false;
    } else if (field.type === "password" && value.length < 6) {
      showError(field, "Password must be 6+ chars");
      valid = false;
    } // Add more (e.g., special chars: if (/[<>&'"]/.test(value)) showError...)
    else {
      clearError(field);
    }
  });
  return valid;
}

function showError(field, msg) {
  let error = field.nextElementSibling;
  if (!error || !error.classList.contains("invalid-feedback")) {
    error = document.createElement("div");
    error.className = "invalid-feedback";
    field.after(error);
  }
  error.textContent = msg;
  field.classList.add("is-invalid");
}

function clearError(field) {
  const error = field.nextElementSibling;
  if (error && error.classList.contains("invalid-feedback")) error.remove();
  field.classList.remove("is-invalid");
}

// In main.js or forms: form.addEventListener('submit', e => { if (!validateForm(form)) e.preventDefault(); });
