<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";
require_once "controllers/BookingController.php";

requireLogin();

$auth = new AuthController($pdo);
$booking = new BookingController($pdo);

$userId = (int) $_SESSION["user_id"];
$isAdmin = $_SESSION["role"] === "admin";

// ── Handle profile update ─────────────────────────────────────────────────────
$profileErrors = [];
$profileSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "update_profile") {
        $result = $auth->updateProfile($userId, $_POST);
        if ($result["success"]) {
            setFlash("success", "Profile updated successfully.");
            redirect("dashboard.php");
        } else {
            $profileErrors = $result["errors"];
        }
    }

    if ($_POST["action"] === "change_password") {
        $result = $auth->changePassword(
            $userId,
            $_POST["current_password"] ?? "",
            $_POST["new_password"] ?? "",
            $_POST["confirm_password"] ?? "",
        );
        if ($result["success"]) {
            setFlash("success", "Password changed successfully.");
            redirect("dashboard.php");
        } else {
            $profileErrors = $result["errors"];
        }
    }
}

// ── Fetch data ────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT FullName, Email, PhoneNumber, Address, Role, DateJoined FROM Users WHERE UserID = ?",
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$rentals = $booking->getUserBookings($userId);

$flash = getFlash();
$title = "Dashboard | DriveEase";
$page = "dashboard";
$css = "dashboard";
$js = "dashboard";

// Tell JS which tab to open after a failed POST
$errTab = "";
if (!empty($profileErrors)) {
    $keys = array_keys($profileErrors);
    if (
        array_intersect($keys, [
            "current_password",
            "new_password",
            "confirm_password",
        ])
    ) {
        $errTab = "password";
    } elseif (array_intersect($keys, ["fullname", "phone", "address"])) {
        $errTab = "edit";
    }
}
include "view/layout/header.php";
?>

<div class="page-container" id="dashboardPage" data-err-tab="<?= htmlspecialchars(
    $errTab,
) ?>">
<main class="dashboard-container container">

  <!-- ── Sidebar ────────────────────────────────────────────────────────── -->
  <div class="dashboard-sidebar">
    <div class="profile-card">
      <div class="profile-avatar">
        <span class="material-symbols-outlined">person</span>
      </div>
      <h3><?= htmlspecialchars($user["FullName"]) ?></h3>
      <span class="role-badge <?= $user["Role"] === "admin"
          ? "admin"
          : "user" ?>">
        <?= ucfirst($user["Role"]) ?> Account
      </span>
    </div>

    <nav class="dashboard-nav">
      <button class="nav-item active" id="btn-profile"   onclick="switchTab(event,'profile')">
        <span class="material-symbols-outlined">badge</span> Profile
      </button>
      <button class="nav-item" id="btn-edit"    onclick="switchTab(event,'edit')">
        <span class="material-symbols-outlined">edit</span> Edit Profile
      </button>
      <button class="nav-item" id="btn-password" onclick="switchTab(event,'password')">
        <span class="material-symbols-outlined">lock</span> Change Password
      </button>
      <button class="nav-item" id="btn-orders"  onclick="switchTab(event,'orders')">
        <span class="material-symbols-outlined">receipt_long</span> Order History
      </button>
      <?php if ($isAdmin): ?>
      <button class="nav-item" onclick="window.location.href='admin.php'">
        <span class="material-symbols-outlined">admin_panel_settings</span> Admin Panel
      </button>
      <?php endif; ?>
      <a href="logout.php" class="nav-item text-decoration-none text-inherit">
        <span class="material-symbols-outlined">logout</span> Logout
      </a>
    </nav>
  </div>

  <!-- ── Main content ───────────────────────────────────────────────────── -->
  <div class="dashboard-content">

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash["type"] ?>">
        <span class="material-symbols-outlined"><?= $flash["type"] === "success"
            ? "check_circle"
            : "error" ?></span>
        <?= htmlspecialchars($flash["message"]) ?>
      </div>
    <?php endif; ?>

    <!-- PROFILE VIEW TAB -->
    <div id="tab-profile" class="tab-pane active">
      <h2>Personal Information</h2>
      <div class="info-grid">
        <div class="info-box">
          <label>Full Name</label>
          <p><?= htmlspecialchars($user["FullName"]) ?></p>
        </div>
        <div class="info-box">
          <label>Email Address</label>
          <p><?= htmlspecialchars($user["Email"]) ?></p>
        </div>
        <div class="info-box">
          <label>Phone Number</label>
          <p><?= htmlspecialchars($user["PhoneNumber"]) ?></p>
        </div>
        <div class="info-box">
          <label>Home Address</label>
          <p><?= !empty($user["Address"])
              ? htmlspecialchars($user["Address"])
              : '<em class="text-muted-alt">Not provided</em>' ?></p>
        </div>
        <div class="info-box">
          <label>Member Since</label>
          <p><?= date("F j, Y", strtotime($user["DateJoined"])) ?></p>
        </div>
        <div class="info-box">
          <label>Account Role</label>
          <p><?= ucfirst($user["Role"]) ?></p>
        </div>
      </div>
    </div>

    <!-- EDIT PROFILE TAB -->
    <div id="tab-edit" class="tab-pane d-none">
      <h2>Edit Profile</h2>
      <form action="dashboard.php" method="POST" id="profileForm" novalidate>
        <input type="hidden" name="action" value="update_profile" />

        <div class="form-row-dash">
          <div class="form-group-dash">
            <label>Full Name</label>
            <input type="text" name="fullname"
                   value="<?= htmlspecialchars($user["FullName"]) ?>"
                   class="dash-input <?= isset($profileErrors["fullname"])
                       ? "input-error"
                       : "" ?>"
                   required />
            <?php if (!empty($profileErrors["fullname"])): ?>
              <span class="field-error"><?= htmlspecialchars(
                  $profileErrors["fullname"],
              ) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group-dash">
            <label>Phone Number</label>
            <input type="tel" name="phone"
                   value="<?= htmlspecialchars($user["PhoneNumber"]) ?>"
                   class="dash-input <?= isset($profileErrors["phone"])
                       ? "input-error"
                       : "" ?>"
                   required />
            <?php if (!empty($profileErrors["phone"])): ?>
              <span class="field-error"><?= htmlspecialchars(
                  $profileErrors["phone"],
              ) ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-group-dash">
          <label>Email Address <span class="text-muted-alt font-xs">(cannot be changed)</span></label>
          <input type="email" value="<?= htmlspecialchars(
              $user["Email"],
          ) ?>" class="dash-input" disabled />
        </div>

        <div class="form-group-dash">
          <label>Home Address</label>
          <input type="text" name="address"
                 value="<?= htmlspecialchars($user["Address"] ?? "") ?>"
                 class="dash-input"
                 placeholder="Street, City, Country" />
        </div>

        <button type="submit" class="btn-dash-primary">
          <span class="material-symbols-outlined">save</span> Save Changes
        </button>
      </form>
    </div>

    <!-- CHANGE PASSWORD TAB -->
    <div id="tab-password" class="tab-pane d-none">
      <h2>Change Password</h2>
      <form action="dashboard.php" method="POST" id="passwordForm" novalidate>
        <input type="hidden" name="action" value="change_password" />

        <div class="form-group-dash">
          <label>Current Password</label>
          <div class="dash-input-wrap">
            <input type="password" id="currentPass" name="current_password"
                   class="dash-input <?= isset(
                       $profileErrors["current_password"],
                   )
                       ? "input-error"
                       : "" ?>"
                   placeholder="Your current password" required />
            <span class="pass-toggle material-symbols-outlined" data-target="currentPass">visibility_off</span>
          </div>
          <?php if (!empty($profileErrors["current_password"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $profileErrors["current_password"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group-dash">
          <label>New Password</label>
          <div class="dash-input-wrap">
            <input type="password" id="newPass" name="new_password"
                   class="dash-input <?= isset($profileErrors["new_password"])
                       ? "input-error"
                       : "" ?>"
                   placeholder="Min. 8 characters" required />
            <span class="pass-toggle material-symbols-outlined" data-target="newPass">visibility_off</span>
          </div>
          <?php if (!empty($profileErrors["new_password"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $profileErrors["new_password"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group-dash">
          <label>Confirm New Password</label>
          <div class="dash-input-wrap">
            <input type="password" id="confirmPass" name="confirm_password"
                   class="dash-input <?= isset(
                       $profileErrors["confirm_password"],
                   )
                       ? "input-error"
                       : "" ?>"
                   placeholder="Repeat new password" required />
            <span class="pass-toggle material-symbols-outlined" data-target="confirmPass">visibility_off</span>
          </div>
          <?php if (!empty($profileErrors["confirm_password"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $profileErrors["confirm_password"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-dash-primary">
          <span class="material-symbols-outlined">lock_reset</span> Update Password
        </button>
      </form>
    </div>

    <!-- ORDERS TAB -->
    <div id="tab-orders" class="tab-pane d-none">
      <h2>Order &amp; Payment History</h2>
      <?php if (empty($rentals)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">car_rental</span>
          <p>No rentals found.</p>
          <a href="fleet.php" class="btn-dash-primary text-decoration-none d-inline-flex mt-1">
            Browse Fleet
          </a>
        </div>
      <?php else: ?>
        <div class="orders-table-wrapper">
          <table class="orders-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Duration</th>
                <th>Pickup / Dropoff</th>
                <th>Total (NPR)</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rentals as $r): ?>
              <tr>
                <td><?= $r["RentalID"] ?></td>
                <td>
                  <strong><?= htmlspecialchars(
                      $r["VehicleName"],
                  ) ?></strong><br>
                  <small class="text-muted-light"><?= htmlspecialchars(
                      $r["Category"],
                  ) ?></small>
                </td>
                <td>
                  <?= date("M j, Y", strtotime($r["StartDate"])) ?>
                  <span class="material-symbols-outlined font-xs v-align-middle">arrow_forward</span>
                  <?= $r["EndDate"]
                      ? date("M j, Y", strtotime($r["EndDate"]))
                      : "TBD" ?>
                </td>
                <td>
                  <small><?= htmlspecialchars($r["PickupLoc"]) ?></small><br>
                  <small class="text-muted-dark"><?= htmlspecialchars(
                      $r["DropoffLoc"],
                  ) ?></small>
                </td>
                <td><strong><?= number_format(
                    $r["TotalCost"],
                    2,
                ) ?></strong></td>
                <td>
                  <span class="status-badge status-<?= strtolower(
                      $r["Status"],
                  ) ?>">
                    <?= htmlspecialchars($r["Status"]) ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- .dashboard-content -->
</main>
</div><!-- .page-container -->

<?php include "view/layout/footer.php"; ?>
