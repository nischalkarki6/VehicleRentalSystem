document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.querySelector("#togglePassword");
  const password = document.querySelector("#password");

  if (togglePassword && password) {
    togglePassword.addEventListener("click", function () {
      const type =
        password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.textContent = type === "password" ? "visibility_off" : "visibility";
    });
  }

  // Form Submission Functionality
  const loginForm = document.querySelector("form");
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent actual form submission
      alert("Login successful! Redirecting to home page...");
      window.location.href = "index.php";
    });
  }
});
