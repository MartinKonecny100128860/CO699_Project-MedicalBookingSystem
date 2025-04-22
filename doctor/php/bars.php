<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$doctorId = $_SESSION['user_id'] ?? null;
$unreadNotif = 0;
$notifications = [];

if ($doctorId) {
    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
    $conn->set_charset("utf8mb4");

    if (!$conn->connect_error) {
        // Get unread count
        $notifQuery = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = $doctorId AND is_read = 0");
        if ($notifQuery) {
            $unreadNotif = $notifQuery->fetch_assoc()['total'] ?? 0;
        }

        // Get latest notifications
        $notifList = $conn->query("SELECT * FROM notifications WHERE user_id = $doctorId ORDER BY created_at DESC LIMIT 10");
        while ($notif = $notifList->fetch_assoc()) {
            $notifications[] = $notif;
        }

        $conn->close();
    }
}
?>

<div class="header">
  <div class="header-left">
    <button id="toggleSidebarBtn" class="toggle-sidebar-btn">☰</button>
    <img src="../assets/logos/logo-dark.png" alt="Logo" class="logo-img" />
  </div>

  <div class="header-right">
    <div class="notification-icon" title="You have new notifications" onclick="toggleNotifications()">
      <i class="material-icons">notifications</i>
      <?php if ($unreadNotif > 0): ?>
        <span class="badge-ping"></span>
      <?php endif; ?>
    </div>
    <a href="/MedicalBooking/logout.php" class="power-icon-box" title="Logout">
      <i class="material-icons">&#xe8ac;</i>
    </a>
  </div>
</div>

<!-- Notification Dropdown -->
<div id="notificationDropdown" class="notification-dropdown" style="display: none;">
  <h6 class="dropdown-title">Notifications</h6>
  <ul class="notification-list">
    <?php if (empty($notifications)): ?>
      <li class="text-muted">No notifications</li>
    <?php else: ?>
      <?php foreach ($notifications as $notif): ?>
        <li class="d-flex justify-content-between align-items-start mb-2" id="notif-<?= $notif['id'] ?>">
  <div>
    <?= htmlspecialchars($notif['message']) ?>
    <br><small class="text-muted"><?= date('d M H:i', strtotime($notif['created_at'])) ?></small>
  </div>
  <button class="btn btn-sm btn-outline-danger ms-2" onclick="dismissNotif(<?= $notif['id'] ?>)">
    &times;
  </button>
</li>

      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
</div>


        <!-- Side Nav Bar HTML -->
        <div class="sidebar">
            <div class="profile-section">
                <div class="profile-pic-container">
                    <div class="profile-pic-wrapper">
                        <img src="<?= htmlspecialchars('../' . ($_SESSION['profile_picture'] ?? 'assets/defaults/user_default.png')) ?>" 
                        alt="Profile Picture" class="profile-pic">
                    </div>
                    <p class="welcome-text">
                        Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Doctor') ?><br>
                        <small>ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></small>
                    </p>
                </div>
            </div>

            <!-- Scrollable Container Inside Nav Bar -->
            <div class="scroll-container">
                <a href="/MedicalBooking/doctor/doctordash.php" data-tooltip="Dashboard">
                    <i class="material-icons">dashboard</i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="/MedicalBooking/doctor/viewappointments.php" data-tooltip="Book Appointment">
                    <i class="material-icons">event</i>
                    <span class="nav-text">View Appointments</span>
                </a>
                <a href="/MedicalBooking/doctor/doctor_schedule.php" data-tooltip="Manage Appointments">
                    <i class="material-icons">today</i>
                    <span class="nav-text">Manage Schedule</span>
                </a>
                <a href="/MedicalBooking/doctor/medicalreports.php" data-tooltip="Medical Reports">
                    <i class="material-icons">assignment</i>
                    <span class="nav-text">View Patients Medical Reports</span>
                </a>
                <a href="/MedicalBooking/doctor/prescribemedication.php" data-tooltip="Prescriptions">
                    <i class="material-icons">local_pharmacy</i>
                    <span class="nav-text">Prescribe Medication</span>
                </a>
                <a href="/MedicalBooking/doctor/emergencycases.php" data-tooltip="Lab Tests">
                    <i class="material-icons">science</i>
                    <span class="nav-text">Take Emergency Cases</span>
                </a>
                <a href="/MedicalBooking/doctor/doctorspecialties.php" data-tooltip="Profile">
                    <i class="material-icons">person</i>
                    <span class="nav-text">Profile</span>
                </a>
            </div>
        </div>

<script>
function toggleNotifications() {
  const dropdown = document.getElementById('notificationDropdown');
  dropdown.style.display = (dropdown.style.display === 'none') ? 'block' : 'none';

  // Mark as read on open
  if (dropdown.style.display === 'block') {
    fetch('php/mark_notifications_read.php')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.querySelector('.badge-ping')?.remove();
        }
      });
  }
}

function dismissNotif(notifId) {
  fetch(`php/dismiss_notification.php?id=${notifId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const li = document.getElementById(`notif-${notifId}`);
        if (li) li.remove();
      }
    });
}
</script>

<style>
    .notification-dropdown {
  position: absolute;
  top: 60px;
  right: 30px;
  background-color: white;
  width: 300px;
  box-shadow: 0 0 10px rgba(0,0,0,0.15);
  border-radius: 8px;
  z-index: 1000;
  padding: 15px;
}

.notification-dropdown .dropdown-title {
  font-weight: bold;
  margin-bottom: 10px;
}

.notification-list {
  list-style: none;
  padding-left: 0;
  max-height: 250px;
  overflow-y: auto;
}

.notification-list li {
  padding: 10px;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}
</style>
