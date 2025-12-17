// js/validation.js

// Validates entire form; returns true if valid, false if any invalid fields
export function validateForm(form) {
  let valid = true;

  const fields = form.querySelectorAll(
    "input:not([type=hidden]), textarea, select"
  );

  fields.forEach((field) => {
    const fieldValid = validateField(field, form);
    if (!fieldValid) valid = false;
  });

  return valid;
}

export function validateField(field, form = field.form) {
  if (!field || field.disabled) return true;

  clearError(field);

  const value = field.value?.trim() ?? "";

  // REQUIRED
  if (field.required && !value) {
    showError(field, "This field is required.");
    return false;
  }

  // EMAIL
  if (field.type === "email" && value) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(value)) {
      showError(field, "Invalid email format.");
      return false;
    }
  }

  // PASSWORD MIN LENGTH (except old_password)
  if (
    field.type === "password" &&
    field.name !== "old_password" &&
    value &&
    value.length < 6
  ) {
    showError(field, "Password must be at least 6 characters.");
    return false;
  }

  // PASSWORD MATCHING (REGISTER)
  if (field.name === "repeat_password") {
    const pw = form.querySelector("input[name='password']");
    if (pw && pw.value !== value) {
      showError(field, "Passwords do not match.");
      return false;
    }
  }

  // PASSWORD MATCHING (CHANGE PASSWORD)
  if (field.name === "repeat_new_password") {
    const newPw = form.querySelector("input[name='new_password']");
    if (newPw && newPw.value !== value) {
      showError(field, "New passwords do not match.");
      return false;
    }
  }

  // USER LEVEL (REGISTER)
  if (field.name === "level" && value === "Admin") {
    showError(field, "Invalid user level.");
    return false;
  }

  return true;
}

// helpers
function showError(field, message) {
  field.classList.add("is-invalid");

  let feedback = field.nextElementSibling;
  if (!feedback || !feedback.classList.contains("invalid-feedback")) {
    feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    field.after(feedback);
  }

  feedback.textContent = message;
}

function clearError(field) {
  field.classList.remove("is-invalid");

  const feedback = field.nextElementSibling;
  if (feedback && feedback.classList.contains("invalid-feedback")) {
    feedback.remove();
  }
}
