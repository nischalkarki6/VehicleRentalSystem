// Logo click → redirect to homepage
document.getElementById("logo").addEventListener("click", function () {
    window.location.href = "index.html";
});

// Form and password elements
const signupForm = document.getElementById('signupForm');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const matchMessage = document.getElementById('matchMessage');

// Password match validation
function validatePasswords() {
    if (confirmPassword.value === "") {
        matchMessage.innerText = "";
        confirmPassword.style.borderColor = "#ccc";
        return;
    }

    if (password.value === confirmPassword.value) {
        matchMessage.innerText = "✓ Passwords match";
        matchMessage.style.color = "#28a745";
        confirmPassword.style.borderColor = "#28a745";
    } else {
        matchMessage.innerText = "× Passwords do not match";
        matchMessage.style.color = "#dc3545";
        confirmPassword.style.borderColor = "#dc3545";
    }
}

// Check on every keystroke
password.addEventListener('input', validatePasswords);
confirmPassword.addEventListener('input', validatePasswords);

// Form submission
signupForm.addEventListener('submit', (e) => {
    e.preventDefault();

    if (password.value !== confirmPassword.value) {
        alert("Please ensure your passwords match before submitting.");
        return;
    }

    // Store user (demo only)
    const user = {
        name: document.getElementById("fullName").value,
        email: document.getElementById("email").value
    };

    localStorage.setItem("user", JSON.stringify(user));

    alert("Account created successfully!");

    // Redirect to login page
    window.location.href = "login.html";
});