/**
 * Page Utilities - Back Button & Scroll to Top
 */

export function initPageUtils() {
  const backBtn = document.getElementById("backBtn");
  const scrollBtn = document.getElementById("scrollToTopBtn");
  const page = document.body.getAttribute("data-page");

  // Hide back button on certain pages
  if (backBtn && ["dashboard", "login", "register"].includes(page)) {
    backBtn.remove();
  }

  // Back button click
  backBtn?.addEventListener("click", () => {
    window.history.length > 1
      ? window.history.back()
      : (window.location.href = "index.php?page=dashboard");
  });

  // Scroll to top visibility & click
  if (scrollBtn) {
    window.addEventListener("scroll", () => {
      scrollBtn.style.opacity = window.pageYOffset > 300 ? "1" : "0";
      scrollBtn.style.visibility =
        window.pageYOffset > 300 ? "visible" : "hidden";
    });

    scrollBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
}
