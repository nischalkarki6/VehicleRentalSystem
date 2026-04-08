<?php
$title = 'Contact | CarCodemandu';
$page = 'contact';
$css = 'contact';
include 'view/layout/header.php';
?>

<main class="page-container container">
      <div class="page-header">
        <h1>Contact Us</h1>
        <p>
          Have a question or need roadside assistance? Reach out to our 24/7
          support team.
        </p>
      </div>
      <div class="auth-container">
        <form action="#" method="POST">
          <div class="form-group">
            <label>Your Name</label>
            <input
              type="text"
              class="form-control"
              placeholder="Enter Name:"
              required
            />
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input
              type="email"
              class="form-control"
              placeholder="Enter Email:"
              required
            />
          </div>
          <div class="form-group">
            <label>Message</label>
            <textarea
              class="form-control"
              rows="4"
              placeholder="Enter Message:"
              required
            ></textarea>
          </div>
          <button class="btn-primary btn-full">Send Message</button>
        </form>
      </div>

      <div class="contact-meta-grid">
        <div class="meta-box">
          <span class="material-symbols-outlined">location_on</span>
          <h3>OFFICE</h3>
          <p>Kathmandu Valley, Nepal</p>
        </div>
        <div class="meta-box">
          <span class="material-symbols-outlined">mail</span>
          <h3>EMAIL</h3>
          <p>support@carcodemandu.com</p>
        </div>
        <div class="meta-box">
          <span class="material-symbols-outlined">call</span>
          <h3>PHONE</h3>
          <p>+977 1 444 5555</p>
        </div>
      </div>
    </main>

<?php include 'view/layout/footer.php'; ?>
