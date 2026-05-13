document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-password-toggle]").forEach((toggle) => {
    const input = document.querySelector(toggle.dataset.passwordToggle);

    if (!input) {
      return;
    }

    toggle.addEventListener("click", () => {
      const isPassword = input.getAttribute("type") === "password";
      input.setAttribute("type", isPassword ? "text" : "password");
      toggle.textContent = isPassword ? "visibility" : "visibility_off";
    });
  });

  const passwordInput = document.getElementById("password");
  const fill = document.getElementById("strengthFill");
  const text = document.getElementById("strengthText");

  if (!passwordInput || !fill || !text) {
    return;
  }

  passwordInput.addEventListener("input", () => {
    const value = passwordInput.value;
    let score = 0;

    if (value.length >= 8) score += 1;
    if (value.length >= 12) score += 1;
    if (/[A-Z]/.test(value)) score += 1;
    if (/[0-9]/.test(value)) score += 1;
    if (/[^A-Za-z0-9]/.test(value)) score += 1;

    const labels = ["", "Weak", "Fair", "Good", "Strong", "Excellent"];
    const colors = ["", "#dc2626", "#f59e0b", "#eab308", "#22c55e", "#16a34a"];

    fill.style.width = `${(score / 5) * 100}%`;
    fill.style.backgroundColor = colors[score] || "#e5e7eb";
    text.textContent = labels[score] || "";
    text.style.color = colors[score] || "";
  });
});
