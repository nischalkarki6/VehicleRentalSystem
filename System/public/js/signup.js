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
  const signupForm = document.querySelector("form");
  if (signupForm) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent actual form submission
      alert("Account successfully created! Please log in.");
      window.location.href = "login.php";
    });
  }
});
