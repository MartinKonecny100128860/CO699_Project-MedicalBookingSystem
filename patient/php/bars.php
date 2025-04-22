<?php
$patientId = $_SESSION['user_id'] ?? null;
$notifCount = 0;

if ($patientId) {
    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $notifResult = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = $patientId AND is_read = 0");
    $notifCount = $notifResult->fetch_assoc()['total'] ?? 0;
    $conn->close();
}
?>

<div class="header">
  <div class="header-left">
    <button id="toggleSidebarBtn" class="toggle-sidebar-btn">☰</button>
    <img src="../assets/logos/logo-dark.png" alt="Logo" class="logo-img" />
  </div>

  <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>

  <div class="header-right">
  <div class="notification-icon" title="You have new notifications" onclick="toggleNotifications()">
  <i class="material-icons">notifications</i>
  <?php if ($notifCount > 0): ?>
    <span class="badge-ping" id="notif-badge"></span> <!-- Add id -->
  <?php endif; ?>
</div>


    <a href="/MedicalBooking/logout.php" class="power-icon-box" title="Logout">
      <i class="material-icons">&#xe8ac;</i>
    </a>
  </div>
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
                <a href="/MedicalBooking/patient/patientdash.php" data-tooltip="Dashboard">
                    <i class="material-icons">dashboard</i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="/MedicalBooking/patient/bookappointment.php" data-tooltip="Book Appointment">
                    <i class="material-icons">event</i>
                    <span class="nav-text">Book Appointment</span>
                </a>
                <a href="/MedicalBooking/patient/manageappointments.php" data-tooltip="Manage Appointments">
                    <i class="material-icons">today</i>
                    <span class="nav-text">Manage Appointments</span>
                </a>
                <a href="/MedicalBooking/patient/viewmedicalreports.php" data-tooltip="Medical Reports">
                    <i class="material-icons">assignment</i>
                    <span class="nav-text">Medical Reports</span>
                </a>
                <a href="/MedicalBooking/patient/viewprescriptions.php" data-tooltip="Prescriptions">
                    <i class="material-icons">local_pharmacy</i>
                    <span class="nav-text">Prescriptions</span>
                </a>
                <a href="/MedicalBooking/patient/viewtests.php" data-tooltip="Lab Tests">
                    <i class="material-icons">science</i>
                    <span class="nav-text">Lab Tests</span>
                </a>
                <a href="/MedicalBooking/patient/profile.php" data-tooltip="Profile">
                    <i class="material-icons">person</i>
                    <span class="nav-text">Profile</span>
                </a>
            </div>
        </div>


<!-- Notification Dropdown -->
<div id="notificationDropdown" class="notification-dropdown" style="display:none; position: absolute; top: 60px; right: 20px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; z-index: 999; border-radius: 8px;">
  <h6 class="dropdown-title p-3 border-bottom m-0">Notifications</h6>
  <ul class="notification-list list-unstyled m-0 p-3">
    <?php
    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $notifs = $conn->query("SELECT * FROM notifications WHERE user_id = $patientId ORDER BY created_at DESC LIMIT 10");
    while ($n = $notifs->fetch_assoc()):
    ?>
    <li class="mb-2 d-flex justify-content-between align-items-start" id="notif-<?= $n['id'] ?>">
    <div>
        <?= htmlspecialchars($n['message']) ?> 
        <br><small class="text-muted"><?= $n['created_at'] ?></small>
    </div>
    <button class="btn btn-sm btn-outline-danger ms-2" onclick="dismissNotif(<?= $n['id'] ?>)">
        &times;
    </button>
    </li>
    <?php endwhile; $conn->close(); ?>
  </ul>
</div>

<script>
let notifMarked = false;

document.addEventListener("DOMContentLoaded", function () {
  const dropdown = document.getElementById('notificationDropdown');
  const badge = document.getElementById('notif-badge');
  const icon = document.querySelector('.notification-icon');

  // Toggle dropdown visibility
  icon.addEventListener("click", function (e) {
    e.stopPropagation(); // Prevent click from bubbling
    const isOpen = dropdown.style.display === 'block';
    dropdown.style.display = isOpen ? 'none' : 'block';

    if (!isOpen && !notifMarked) {
      fetch('php/mark_notifications_read.php')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            notifMarked = true;
            if (badge) badge.remove();
          }
        });
    }
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", function (e) {
    if (!dropdown.contains(e.target) && !icon.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });
});

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
  padding: 10px 15px;
  border-bottom: 1px solid #eee;
}

.notification-dropdown .notification-list {
  max-height: 300px;
  overflow-y: auto;
  padding: 15px;
}

.notification-dropdown {
  transition: opacity 0.3s ease;
  opacity: 1;
}

.notification-dropdown[style*="display: none"] {
  opacity: 0;
}


</style>