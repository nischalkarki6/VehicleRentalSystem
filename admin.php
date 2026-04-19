<?php
require_once "config/config.php";
require_once "controllers/BookingController.php";
require_once "controllers/VehicleController.php";

requireAdmin(); // Redirects non-admins to dashboard.php

$bookCtrl = new BookingController($pdo);
$vehCtrl = new VehicleController($pdo);

// ── Handle POST actions ───────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // Approve / Reject / Complete / Cancel booking
    if ($action === "update_booking_status") {
        $rentalId = (int) ($_POST["rental_id"] ?? 0);
        $status = $_POST["status"] ?? "";
        if ($rentalId > 0) {
            $bookCtrl->updateStatus($rentalId, $status);
            setFlash("success", "Booking #$rentalId updated to $status.");
        }
        redirect("admin.php?tab=bookings");
    }

    // Add vehicle
    if ($action === "add_vehicle") {
        $result = $vehCtrl->addVehicle($_POST);
        if ($result["success"]) {
            setFlash("success", "Vehicle added successfully.");
            redirect("admin.php?tab=fleet");
        } else {
            $vehicleErrors = $result["errors"];
        }
    }

    // Toggle vehicle availability
    if ($action === "toggle_vehicle") {
        $vehCtrl->toggleAvailability((int) $_POST["vehicle_id"]);
        redirect("admin.php?tab=fleet");
    }

    // Delete vehicle (only if no pending/active bookings)
    if ($action === "delete_vehicle") {
        $vid = (int) $_POST["vehicle_id"];
        $chk = $pdo->prepare(
            "SELECT COUNT(*) FROM Rentals WHERE VehicleID = ? AND Status IN ('Pending','Active')",
        );
        $chk->execute([$vid]);
        if ($chk->fetchColumn() > 0) {
            setFlash(
                "error",
                "Cannot delete: vehicle has pending or active bookings.",
            );
        } else {
            $vehCtrl->deleteVehicle($vid);
            setFlash("success", "Vehicle deleted.");
        }
        redirect("admin.php?tab=fleet");
    }
}

// ── Fetch data ────────────────────────────────────────────────────────────────
$allBookings = $bookCtrl->getAllBookings();
$allVehicles = $vehCtrl->getAllVehicles();

$stmtUsers = $pdo->query(
    "SELECT UserID, FullName, Email, PhoneNumber, Role, DateJoined FROM Users WHERE Role = 'user' ORDER BY UserID DESC",
);
$allUsers = $stmtUsers->fetchAll();

$stmtMsgs = $pdo->query("SELECT * FROM ContactMessages ORDER BY SentDate DESC");
$allMsgs = $stmtMsgs->fetchAll();

// ── Stats ─────────────────────────────────────────────────────────────────────
$totalRevenue = array_sum(
    array_column(
        array_filter(
            $allBookings,
            fn($b) => in_array($b["Status"], ["Active", "Completed"]),
        ),
        "TotalCost",
    ),
);
$pendingCount = count(
    array_filter($allBookings, fn($b) => $b["Status"] === "Pending"),
);
$activeCount = count(
    array_filter($allBookings, fn($b) => $b["Status"] === "Active"),
);

$flash = getFlash();
$activeTab = $_GET["tab"] ?? "overview";
$vehicleErrors = $vehicleErrors ?? [];

$title = "Admin Panel | DriveEase";
$page = "admin";
$css = "admin";
$js = "admin";
include "view/layout/header.php";
?>

<div class="page-container">
<main class="admin-wrap container">

  <!-- ── Admin Sidebar ─────────────────────────────────────────────────────── -->
  <div class="admin-sidebar">
    <div class="admin-brand">
      <span class="material-symbols-outlined font-icon-xl">admin_panel_settings</span>
      <div>
        <strong>Admin Panel</strong>
        <small><?= htmlspecialchars($_SESSION["user_name"]) ?></small>
      </div>
    </div>
    <nav class="admin-nav">
      <a href="admin.php?tab=overview"  class="admin-nav-item <?= $activeTab ===
      "overview"
          ? "active"
          : "" ?>">
        <span class="material-symbols-outlined">dashboard</span> Overview
      </a>
      <a href="admin.php?tab=bookings"  class="admin-nav-item <?= $activeTab ===
      "bookings"
          ? "active"
          : "" ?>">
        <span class="material-symbols-outlined">receipt_long</span> Bookings
        <?php if ($pendingCount > 0): ?>
          <span class="badge-pill"><?= $pendingCount ?></span>
        <?php endif; ?>
      </a>
      <a href="admin.php?tab=fleet"     class="admin-nav-item <?= $activeTab ===
      "fleet"
          ? "active"
          : "" ?>">
        <span class="material-symbols-outlined">directions_car</span> Fleet
      </a>
      <a href="admin.php?tab=users"     class="admin-nav-item <?= $activeTab ===
      "users"
          ? "active"
          : "" ?>">
        <span class="material-symbols-outlined">group</span> Users
      </a>
      <a href="admin.php?tab=messages"  class="admin-nav-item <?= $activeTab ===
      "messages"
          ? "active"
          : "" ?>">
        <span class="material-symbols-outlined">mail</span> Messages
      </a>
      <hr class="admin-divider">
      <a href="dashboard.php"           class="admin-nav-item">
        <span class="material-symbols-outlined">person</span> My Profile
      </a>
    </nav>
  </div>

  <!-- ── Admin Content ─────────────────────────────────────────────────────── -->
  <div class="admin-content">

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash[
          "type"
      ] ?> mb-15">
        <span class="material-symbols-outlined"><?= $flash["type"] === "success"
            ? "check_circle"
            : "error" ?></span>
        <?= htmlspecialchars($flash["message"]) ?>
      </div>
    <?php endif; ?>

    <!-- ── OVERVIEW TAB ───────────────────────────────────────────────────── -->
    <?php if ($activeTab === "overview"): ?>
      <h2 class="admin-page-title">Dashboard Overview</h2>

      <div class="stats-grid">
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-blue">receipt_long</span>
          <div class="stat-value"><?= count($allBookings) ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-orange">pending</span>
          <div class="stat-value"><?= $pendingCount ?></div>
          <div class="stat-label">Pending Approval</div>
        </div>
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-green">check_circle</span>
          <div class="stat-value"><?= $activeCount ?></div>
          <div class="stat-label">Active Rentals</div>
        </div>
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-purple">directions_car</span>
          <div class="stat-value"><?= count($allVehicles) ?></div>
          <div class="stat-label">Fleet Size</div>
        </div>
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-darkgreen">payments</span>
          <div class="stat-value">NPR <?= number_format(
              $totalRevenue,
              0,
          ) ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
          <span class="material-symbols-outlined stat-icon stat-icon-red">group</span>
          <div class="stat-value"><?= count($allUsers) ?></div>
          <div class="stat-label">Registered Users</div>
        </div>
      </div>

      <!-- Recent bookings mini-table -->
      <h3 class="my-2-1">Recent Bookings</h3>
      <div class="orders-table-wrapper">
        <table class="orders-table">
          <thead>
            <tr><th>ID</th><th>User</th><th>Vehicle</th><th>Amount</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($allBookings, 0, 8) as $b): ?>
            <tr>
              <td>#<?= $b["RentalID"] ?></td>
              <td><?= htmlspecialchars($b["UserName"]) ?></td>
              <td><?= htmlspecialchars($b["VehicleName"]) ?></td>
              <td>NPR <?= number_format($b["TotalCost"], 2) ?></td>
              <td><span class="status-badge status-<?= strtolower(
                  $b["Status"],
              ) ?>"><?= $b["Status"] ?></span></td>
              <td>
                <?php if ($b["Status"] === "Pending"): ?>
                  <form method="POST" class="d-inline-block">
                    <input type="hidden" name="action"    value="update_booking_status">
                    <input type="hidden" name="rental_id" value="<?= $b[
                        "RentalID"
                    ] ?>">
                    <input type="hidden" name="status"    value="Active">
                    <button type="submit" class="btn-xs btn-approve">Approve</button>
                  </form>
                  <form method="POST" class="d-inline-block">
                    <input type="hidden" name="action"    value="update_booking_status">
                    <input type="hidden" name="rental_id" value="<?= $b[
                        "RentalID"
                    ] ?>">
                    <input type="hidden" name="status"    value="Cancelled">
                    <button type="submit" class="btn-xs btn-reject">Reject</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted-alt font-sm">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($allBookings)): ?>
              <tr><td colspan="6" class="text-center p-2 text-muted-light">No bookings yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <!-- ── BOOKINGS TAB ────────────────────────────────────────────────────── -->
    <?php elseif ($activeTab === "bookings"): ?>
      <h2 class="admin-page-title">All Bookings</h2>

      <div class="filter-bar">
        <input type="text" id="bookingSearch" class="dash-input w-max-300"
               placeholder="Search user, vehicle…" />
        <select id="statusFilter" class="dash-input w-max-160"
                onchange="filterByStatus()">
          <option value="">All Statuses</option>
          <option>Pending</option><option>Active</option>
          <option>Completed</option><option>Cancelled</option>
        </select>
      </div>

      <div class="orders-table-wrapper">
        <table class="orders-table" id="bookingsTable">
          <thead>
            <tr>
              <th>#</th><th>User</th><th>Vehicle</th>
              <th>Duration</th><th>Pickup → Dropoff</th>
              <th>Amount (NPR)</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allBookings as $b): ?>
            <tr data-status="<?= $b["Status"] ?>">
              <td>#<?= $b["RentalID"] ?></td>
              <td>
                <strong><?= htmlspecialchars($b["UserName"]) ?></strong><br>
                <small style="color:#999"><?= htmlspecialchars(
                    $b["UserEmail"],
                ) ?></small>
              </td>
              <td>
                <?= htmlspecialchars($b["VehicleName"]) ?><br>
                <small style="color:#999"><?= $b["Category"] ?></small>
              </td>
              <td>
                <?= date("M j, Y", strtotime($b["StartDate"])) ?><br>
                <small>→ <?= $b["EndDate"]
                    ? date("M j, Y", strtotime($b["EndDate"]))
                    : "TBD" ?></small>
              </td>
              <td>
                <small><?= htmlspecialchars($b["PickupLoc"]) ?></small><br>
                <small style="color:#888"><?= htmlspecialchars(
                    $b["DropoffLoc"],
                ) ?></small>
              </td>
              <td><strong><?= number_format($b["TotalCost"], 2) ?></strong></td>
              <td><span class="status-badge status-<?= strtolower(
                  $b["Status"],
              ) ?>"><?= $b["Status"] ?></span></td>
              <td class="action-btns">
                <?php if ($b["Status"] === "Pending"): ?>
                  <form method="POST">
                    <input type="hidden" name="action"    value="update_booking_status">
                    <input type="hidden" name="rental_id" value="<?= $b[
                        "RentalID"
                    ] ?>">
                    <input type="hidden" name="status"    value="Active">
                    <button type="submit" class="btn-xs btn-approve">✓ Approve</button>
                  </form>
                  <form method="POST">
                    <input type="hidden" name="action"    value="update_booking_status">
                    <input type="hidden" name="rental_id" value="<?= $b[
                        "RentalID"
                    ] ?>">
                    <input type="hidden" name="status"    value="Cancelled">
                    <button type="submit" class="btn-xs btn-reject">✕ Reject</button>
                  </form>
                <?php elseif ($b["Status"] === "Active"): ?>
                  <form method="POST">
                    <input type="hidden" name="action"    value="update_booking_status">
                    <input type="hidden" name="rental_id" value="<?= $b[
                        "RentalID"
                    ] ?>">
                    <input type="hidden" name="status"    value="Completed">
                    <button type="submit" class="btn-xs btn-complete">Complete</button>
                  </form>
                <?php else: ?>
                  <span style="color:#aaa;font-size:0.8rem">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ── FLEET TAB ───────────────────────────────────────────────────────── -->
    <?php elseif ($activeTab === "fleet"): ?>
      <div class="d-flex justify-content-between align-items-center mb-15">
        <h2 class="admin-page-title m-0">Fleet Management</h2>
        <button class="btn-dash-primary" onclick="toggleAddForm()">
          <span class="material-symbols-outlined">add</span> Add Vehicle
        </button>
      </div>

      <!-- Add Vehicle Form -->
      <div id="addVehicleForm" data-has-errors="<?= !empty($vehicleErrors)
          ? "1"
          : "0" ?>" class="d-none bg-light p-2 mb-2 rounded-12 border-light">
        <h3 class="m-0 mb-15">Add New Vehicle</h3>
        <form method="POST" id="vehicleForm" novalidate>
          <input type="hidden" name="action" value="add_vehicle">

          <div class="form-row-dash">
            <div class="form-group-dash">
              <label>Vehicle Name *</label>
              <input type="text" name="name" class="dash-input <?= isset(
                  $vehicleErrors["name"],
              )
                  ? "input-error"
                  : "" ?>"
                     placeholder="e.g. Toyota RAV4" required value="<?= htmlspecialchars(
                         $_POST["name"] ?? "",
                     ) ?>"/>
              <?php if (
                  !empty($vehicleErrors["name"])
              ): ?><span class="field-error"><?= htmlspecialchars(
    $vehicleErrors["name"],
) ?></span><?php endif; ?>
            </div>
            <div class="form-group-dash">
              <label>Category *</label>
              <select name="category" class="dash-input" required>
                <option value="">Select…</option>
                <option value="Car"  <?= ($_POST["category"] ?? "") === "Car"
                    ? "selected"
                    : "" ?>>Car</option>
                <option value="Bike" <?= ($_POST["category"] ?? "") === "Bike"
                    ? "selected"
                    : "" ?>>Bike</option>
              </select>
              <?php if (
                  !empty($vehicleErrors["category"])
              ): ?><span class="field-error"><?= htmlspecialchars(
    $vehicleErrors["category"],
) ?></span><?php endif; ?>
            </div>
          </div>

          <div class="form-row-dash">
            <div class="form-group-dash">
              <label>Type <small style="color:#999">(SUV, Sedan, Cruiser…)</small></label>
              <input type="text" name="type" class="dash-input" placeholder="e.g. SUV" value="<?= htmlspecialchars(
                  $_POST["type"] ?? "",
              ) ?>"/>
            </div>
            <div class="form-group-dash">
              <label>Transmission *</label>
              <select name="transmission" class="dash-input" required>
                <option value="">Select…</option>
                <option value="Manual"    <?= ($_POST["transmission"] ?? "") ===
                "Manual"
                    ? "selected"
                    : "" ?>>Manual</option>
                <option value="Automatic" <?= ($_POST["transmission"] ?? "") ===
                "Automatic"
                    ? "selected"
                    : "" ?>>Automatic</option>
              </select>
              <?php if (
                  !empty($vehicleErrors["transmission"])
              ): ?><span class="field-error"><?= htmlspecialchars(
    $vehicleErrors["transmission"],
) ?></span><?php endif; ?>
            </div>
          </div>

          <div class="form-row-dash">
            <div class="form-group-dash">
              <label>Fuel Type *</label>
              <select name="fuel_type" class="dash-input" required>
                <option value="">Select…</option>
                <option value="Petrol"   <?= ($_POST["fuel_type"] ?? "") ===
                "Petrol"
                    ? "selected"
                    : "" ?>>Petrol</option>
                <option value="Diesel"   <?= ($_POST["fuel_type"] ?? "") ===
                "Diesel"
                    ? "selected"
                    : "" ?>>Diesel</option>
                <option value="Electric" <?= ($_POST["fuel_type"] ?? "") ===
                "Electric"
                    ? "selected"
                    : "" ?>>Electric</option>
              </select>
              <?php if (
                  !empty($vehicleErrors["fuel_type"])
              ): ?><span class="field-error"><?= htmlspecialchars(
    $vehicleErrors["fuel_type"],
) ?></span><?php endif; ?>
            </div>
            <div class="form-group-dash">
              <label>Engine CC <small style="color:#999">(bikes)</small></label>
              <input type="number" name="engine_cc" class="dash-input" placeholder="e.g. 150" value="<?= htmlspecialchars(
                  $_POST["engine_cc"] ?? "",
              ) ?>"/>
            </div>
          </div>

          <div class="form-row-dash">
            <div class="form-group-dash">
              <label>Daily Rate (NPR) *</label>
              <input type="number" name="daily_rate" class="dash-input <?= isset(
                  $vehicleErrors["daily_rate"],
              )
                  ? "input-error"
                  : "" ?>"
                     placeholder="e.g. 3500" step="0.01" required value="<?= htmlspecialchars(
                         $_POST["daily_rate"] ?? "",
                     ) ?>"/>
              <?php if (
                  !empty($vehicleErrors["daily_rate"])
              ): ?><span class="field-error"><?= htmlspecialchars(
    $vehicleErrors["daily_rate"],
) ?></span><?php endif; ?>
            </div>
            <div class="form-group-dash">
              <label>Image URL</label>
              <input type="url" name="image_url" class="dash-input" placeholder="https://…" value="<?= htmlspecialchars(
                  $_POST["image_url"] ?? "",
              ) ?>"/>
            </div>
          </div>

          <div style="display:flex;gap:1rem;margin-top:1rem">
            <button type="submit" class="btn-dash-primary">
              <span class="material-symbols-outlined">save</span> Save Vehicle
            </button>
            <button type="button" class="btn-dash-secondary" onclick="toggleAddForm()">Cancel</button>
          </div>
        </form>
      </div>

      <!-- Vehicles Table -->
      <div class="orders-table-wrapper">
        <table class="orders-table">
          <thead>
            <tr><th>ID</th><th>Name</th><th>Category</th><th>Transmission</th><th>Fuel</th><th>Daily (NPR)</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allVehicles as $v): ?>
            <tr>
              <td>#<?= $v["VehicleID"] ?></td>
              <td>
                <strong><?= htmlspecialchars($v["Name"]) ?></strong><br>
                <small style="color:#999"><?= htmlspecialchars(
                    $v["Type"] ?? "",
                ) ?></small>
              </td>
              <td><?= $v["Category"] ?></td>
              <td><?= $v["Transmission"] ?></td>
              <td><?= $v["FuelType"] ?></td>
              <td><?= number_format($v["DailyRate"], 2) ?></td>
              <td>
                <span class="status-badge <?= $v["IsAvailable"]
                    ? "status-active"
                    : "status-cancelled" ?>">
                  <?= $v["IsAvailable"] ? "Available" : "Unavailable" ?>
                </span>
              </td>
              <td class="action-btns">
                <form method="POST" class="d-inline-block">
                  <input type="hidden" name="action"     value="toggle_vehicle">
                  <input type="hidden" name="vehicle_id" value="<?= $v[
                      "VehicleID"
                  ] ?>">
                  <button type="submit" class="btn-xs <?= $v["IsAvailable"]
                      ? "btn-reject"
                      : "btn-approve" ?>">
                    <?= $v["IsAvailable"] ? "Disable" : "Enable" ?>
                  </button>
                </form>
                <form method="POST" class="d-inline-block"
                      onsubmit="return confirm('Delete this vehicle? This cannot be undone.')">
                  <input type="hidden" name="action"     value="delete_vehicle">
                  <input type="hidden" name="vehicle_id" value="<?= $v[
                      "VehicleID"
                  ] ?>">
                  <button type="submit" class="btn-xs btn-reject">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ── USERS TAB ───────────────────────────────────────────────────────── -->
    <?php elseif ($activeTab === "users"): ?>
      <h2 class="admin-page-title">Registered Users</h2>
      <input type="text" class="dash-input" style="max-width:300px;margin-bottom:1rem"
             placeholder="Search users…" oninput="filterTable('usersTable',this.value)" />
      <div class="orders-table-wrapper">
        <table class="orders-table" id="usersTable">
          <thead>
            <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allUsers as $u): ?>
            <tr>
              <td><?= $u["UserID"] ?></td>
              <td><?= htmlspecialchars($u["FullName"]) ?></td>
              <td><?= htmlspecialchars($u["Email"]) ?></td>
              <td><?= htmlspecialchars($u["PhoneNumber"]) ?></td>
              <td><span class="role-badge <?= $u["Role"] ?>"><?= ucfirst(
    $u["Role"],
) ?></span></td>
              <td><?= date("M j, Y", strtotime($u["DateJoined"])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php elseif ($activeTab === "messages"): ?>
      <h2 class="admin-page-title">User Inquiries</h2>
      <div class="orders-table-wrapper">
        <table class="orders-table">
          <thead>
            <tr><th>Date</th><th>From</th><th>Email</th><th>Message</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allMsgs as $m): ?>
            <tr>
              <td style="white-space:nowrap"><?= date(
                  "M j, Y g:i A",
                  strtotime($m["SentDate"]),
              ) ?></td>
              <td><strong><?= htmlspecialchars($m["UserName"]) ?></strong></td>
              <td><?= htmlspecialchars($m["UserEmail"]) ?></td>
              <td><div style="max-width:400px"><?= nl2br(
                  htmlspecialchars($m["Message"]),
              ) ?></div></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($allMsgs)): ?>
              <tr><td colspan="4" class="text-center p-2 text-muted-light">No messages yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div><!-- .admin-content -->
</main>
</div><!-- .page-container -->



<?php include "view/layout/footer.php"; ?>
