function switchTab(event, tabId) {
  document.querySelectorAll(".tab-pane").forEach((el) => {
    el.classList.add("d-none");
    el.classList.remove("active");
  });
  document
    .querySelectorAll(".nav-item")
    .forEach((el) => el.classList.remove("active"));

  const target = document.getElementById("tab-" + tabId);
  if (target) {
    target.classList.remove("d-none");
    target.classList.add("active");
  }
  if (event && event.currentTarget) {
    event.currentTarget.classList.add("active");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const page = document.getElementById("dashboardPage");
  const errTab = page ? page.dataset.errTab : "";
  const requestedTab = new URLSearchParams(window.location.search).get("tab");
  const initialTab = errTab || requestedTab;
  if (initialTab) {
    const btn = document.getElementById("btn-" + initialTab);
    if (btn && document.getElementById("tab-" + initialTab)) {
      switchTab({ currentTarget: btn }, initialTab);
    }
  }

  document.querySelectorAll(".pass-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      const hidden = input.type === "password";
      input.type = hidden ? "text" : "password";
      btn.textContent = hidden ? "visibility" : "visibility_off";
    });
  });

  const toggleBtn = document.getElementById("togglePassword");
  const passInput = document.getElementById("password");
  if (toggleBtn && passInput) {
    toggleBtn.addEventListener("click", () => {
      const hidden = passInput.type === "password";
      passInput.type = hidden ? "text" : "password";
      toggleBtn.textContent = hidden ? "visibility" : "visibility_off";
    });
  }
});
