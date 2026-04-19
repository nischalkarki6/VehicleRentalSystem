// ── dashboard.js ─────────────────────────────────────────────────────────────

// ── Tab switching ─────────────────────────────────────────────────────────────
function switchTab(event, tabId) {
  document.querySelectorAll(".tab-pane").forEach((el) => {
    el.style.display = "none";
    el.classList.remove("active");
  });
  document
    .querySelectorAll(".nav-item")
    .forEach((el) => el.classList.remove("active"));

  const target = document.getElementById("tab-" + tabId);
  if (target) {
    target.style.display = "block";
    target.classList.add("active");
  }
  if (event && event.currentTarget) {
    event.currentTarget.classList.add("active");
  }
}

// ── Auto-open correct tab when there are server-side errors (POST back) ───────
document.addEventListener("DOMContentLoaded", () => {
  // Auto-open correct tab when there are server-side errors (POST back)
  const page = document.getElementById("dashboardPage");
  const errTab = page ? page.dataset.errTab : "";
  if (errTab) {
    const btn = document.getElementById("btn-" + errTab);
    switchTab(btn ? { currentTarget: btn } : null, errTab);
  }

  // ── Password visibility toggles ─────────────────────────────────────────────
  document.querySelectorAll(".pass-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      const hidden = input.type === "password";
      input.type = hidden ? "text" : "password";
      btn.textContent = hidden ? "visibility" : "visibility_off";
    });
  });

  // ── Auth page password toggle (login / signup) ──────────────────────────────
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
