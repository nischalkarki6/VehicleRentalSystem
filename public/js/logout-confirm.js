document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-confirm-logout]").forEach((link) => {
    link.addEventListener("click", (event) => {
      if (!confirm("Are you sure you want to log out?")) {
        event.preventDefault();
      }
    });
  });
});
