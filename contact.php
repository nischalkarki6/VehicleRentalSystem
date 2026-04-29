<?php
require_once "config/config.php";

$success = false;
$error = "";
$name = "";
$email = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO ContactMessages (UserName, UserEmail, Message) VALUES (?, ?, ?)",
        );
        if ($stmt->execute([$name, $email, $message])) {
            $success = true;
            $name = $email = $message = "";
        } else {
            $error = "Failed to send message. Please try again.";
        }
    }
}

$title = "Contact | DriveEase";
$page = "contact";
$css = "contact";
include "view/layout/header.php";
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
    <?php if ($success): ?>
      <div class="alert alert-success mb-2">
        <span class="material-symbols-outlined">send</span>
        Your message has been sent. We'll get back to you soon!
      </div>
    <?php elseif ($error): ?>
      <div class="alert alert-error mb-2">
        <span class="material-symbols-outlined">error</span>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="contact.php" method="POST">
      <div class="form-group">
        <label>Your Name</label>
        <input
          type="text"
          name="name"
          class="form-control"
          placeholder="Enter Name:"
          required
          value="<?= htmlspecialchars($name ?? "") ?>"
        />
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input
          type="email"
          name="email"
          class="form-control"
          placeholder="Enter Email:"
          required
          value="<?= htmlspecialchars($email ?? "") ?>"
        />
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea
          name="message"
          class="form-control"
          rows="4"
          placeholder="Enter Message:"
          required
        ><?= htmlspecialchars($message ?? "") ?></textarea>
      </div>
      <button class="btn-primary btn-full" type="submit">Send Message</button>
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
      <p>support@DriveEase.com</p>
    </div>
    <div class="meta-box">
      <span class="material-symbols-outlined">call</span>
      <h3>PHONE</h3>
      <p>+977 1 444 5555</p>
    </div>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
