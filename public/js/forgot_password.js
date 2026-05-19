document.addEventListener("DOMContentLoaded", () => {
  const otpPanel = document.getElementById("otpPanel");
  const otpForm = document.getElementById("otpVerifyForm");
  const otpInput = document.getElementById("otpCode");
  const otpMessage = document.getElementById("otpMessage");
  const verifyButton = document.getElementById("verifyOtpBtn");
  const resetPopup = document.getElementById("resetPopup");
  const resetForm = document.getElementById("resetPasswordOtpForm");
  const resetTokenInput = document.getElementById("resetSessionToken");
  const resetMessage = document.getElementById("resetMessage");
  const resetButton = document.getElementById("resetPasswordOtpBtn");
  const passwordInput = document.getElementById("newPassword");
  const confirmInput = document.getElementById("confirmPassword");
  const strengthFill = document.getElementById("resetStrengthFill");
  const strengthText = document.getElementById("resetStrengthText");

  if (!otpForm || !resetForm) {
    return;
  }

  const endpoint = otpForm.dataset.endpoint;
  const email = otpForm.dataset.email;

  const setMessage = (element, message, type = "") => {
    element.textContent = message;
    element.classList.remove("is-error", "is-success");

    if (type) {
      element.classList.add(`is-${type}`);
    }
  };

  const setLoading = (button, isLoading, loadingText, defaultText) => {
    button.disabled = isLoading;
    const label = button.querySelector("span:first-child");

    if (label) {
      label.textContent = isLoading ? loadingText : defaultText;
    }
  };

  const postResetPayload = async (payload) => {
    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body: new URLSearchParams(payload),
    });

    const data = await response.json().catch(() => ({
      success: false,
      error: "Unexpected server response.",
    }));

    if (!response.ok || !data.success) {
      throw new Error(data.error || data.message || "Request failed.");
    }

    return data;
  };

  const passwordScore = (value) => {
    let score = 0;

    if (value.length >= 8) score += 1;
    if (value.length >= 12) score += 1;
    if (/[A-Z]/.test(value)) score += 1;
    if (/[0-9]/.test(value)) score += 1;
    if (/[^A-Za-z0-9]/.test(value)) score += 1;

    return score;
  };

  const updateStrength = () => {
    const score = passwordScore(passwordInput.value);
    const labels = ["", "Weak", "Fair", "Good", "Strong", "Excellent"];
    const colors = ["", "#dc2626", "#f59e0b", "#eab308", "#22c55e", "#16a34a"];

    strengthFill.style.width = `${(score / 5) * 100}%`;
    strengthFill.style.backgroundColor = colors[score] || "#e5e7eb";
    strengthText.textContent = labels[score] || "";
    strengthText.style.color = colors[score] || "";
  };

  const validatePassword = () => {
    const password = passwordInput.value;
    const confirm = confirmInput.value;

    if (password.length < 8) {
      return "Password must be at least 8 characters.";
    }

    if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
      return "Password must include an uppercase letter and a number.";
    }

    if (password !== confirm) {
      return "Passwords do not match.";
    }

    return "";
  };

  otpInput.addEventListener("input", () => {
    otpInput.value = otpInput.value.replace(/\D/g, "").slice(0, 6);
    setMessage(otpMessage, "");
  });

  passwordInput.addEventListener("input", () => {
    updateStrength();
    setMessage(resetMessage, "");
  });

  confirmInput.addEventListener("input", () => {
    setMessage(resetMessage, "");
  });

  otpForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const otp = otpInput.value.trim();
    if (!/^\d{6}$/.test(otp)) {
      setMessage(otpMessage, "Enter the 6-digit code from your email.", "error");
      otpInput.focus();
      return;
    }

    setLoading(verifyButton, true, "Verifying", "Verify Code");
    setMessage(otpMessage, "Checking your code...");

    try {
      const data = await postResetPayload({
        action: "verify",
        email,
        otp,
        csrf_token: otpForm.elements.csrf_token.value,
      });

      resetTokenInput.value = data.reset_session_token || "";
      setMessage(otpMessage, "Code verified.", "success");
      otpPanel.classList.add("is-hidden");

      window.setTimeout(() => {
        resetPopup.classList.add("is-open");
        resetPopup.setAttribute("aria-hidden", "false");
        passwordInput.focus();
      }, 260);
    } catch (error) {
      setMessage(otpMessage, error.message, "error");
      otpInput.select();
    } finally {
      setLoading(verifyButton, false, "Verifying", "Verify Code");
    }
  });

  resetForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const validationError = validatePassword();
    if (validationError) {
      setMessage(resetMessage, validationError, "error");
      return;
    }

    setLoading(resetButton, true, "Updating", "Update Password");
    setMessage(resetMessage, "Updating your password...");

    try {
      await postResetPayload({
        action: "reset",
        reset_session_token: resetTokenInput.value,
        password: passwordInput.value,
        confirm_password: confirmInput.value,
        csrf_token: resetForm.elements.csrf_token.value,
      });

      setMessage(resetMessage, "Password updated. Redirecting to login...", "success");
      window.setTimeout(() => {
        window.location.href = "login.php";
      }, 900);
    } catch (error) {
      setMessage(resetMessage, error.message, "error");
    } finally {
      setLoading(resetButton, false, "Updating", "Update Password");
    }
  });
});
