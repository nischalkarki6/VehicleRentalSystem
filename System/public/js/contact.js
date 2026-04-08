document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.querySelector("form");
  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent default submission
      alert(
        "Thank you for reaching out! Your message has been sent to our support team.",
      );
      contactForm.reset(); // Clear the form
    });
  }
});
