/**
 * Safely escapes HTML to prevent XSS attacks
 * @param {string|number|null|undefined} text - Value to escape
 * @returns {string} HTML-safe string
 */
export function escapeHtml(text) {
  if (text == null) return "";

  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
