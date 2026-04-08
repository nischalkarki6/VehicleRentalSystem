// Booking form submission
const bookingForm = document.getElementById('bookingForm');

bookingForm.addEventListener('submit', (e) => {
    e.preventDefault();

    // Get rider details
    const riderName = document.getElementById('riderName').value;
    const licenseNumber = document.getElementById('licenseNumber').value;
    const licenseExpiry = document.getElementById('licenseExpiry').value;
    const paymentMethod = document.getElementById('paymentMethod').value;

    if (!riderName || !licenseNumber || !licenseExpiry || !paymentMethod) {
        alert("Please fill all the fields and select a payment method.");
        return;
    }

    // Store booking info (demo)
    const bookingInfo = {
        riderName,
        licenseNumber,
        licenseExpiry,
        paymentMethod
    };
    localStorage.setItem("booking", JSON.stringify(bookingInfo));

    alert(`Booking confirmed! Payment method: ${paymentMethod}`);
    // Redirect to a "Thank You" or homepage
    window.location.href = "thankyou.html";
});