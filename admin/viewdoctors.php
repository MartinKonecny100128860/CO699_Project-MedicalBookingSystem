<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$query = "SELECT user_id, first_name, last_name, email, description, profile_picture FROM users WHERE role = 'doctor'";
$result = $conn->query($query);

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Registered Doctors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="styles/gallery.css" rel="stylesheet"> <!-- your shared style -->
    <link rel="stylesheet" href="styles/admindash.css">

</head>
<body>

        <!-- Header HTML -->
        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="../assets/logos/logo-dark.png" alt="Logo">
                <h1 style="margin-left: 20px;">View Doctors</h1>
            </div>
            <a href="/MedicalBooking/logout.php" class="power-icon-box">
                <i class="material-icons">&#xe8ac;</i>    
            </a>
        </div>

        <!-- Side Nav Bar HTML -->
        <div class="sidebar">
            <div class="profile-pic-container">
                <div class="profile-pic-wrapper">
                <img src="<?= htmlspecialchars('../' . ($_SESSION['profile_picture'] ?? 'assets/defaults/user_default.png')) ?>" 
                    alt="Profile Picture" class="profile-pic">
                </div>
                <p class="welcome-text">
                    Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?><br>
                    <small>ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></small>
                </p>
            </div>

            <!-- Scrollable Container Inside Nav Bar -->
            <div class="scroll-container">
                <h4 class="sidebar-heading">Quick Links</h4>
                <a href="#" onclick="showAddUserModal()">Add New User</a>
                <a href="#" onclick="showManageRolesModal()">Manage Roles</a>
                <a href="logs.php">System Logs</a>

                <h4 class="sidebar-heading">Resources</h4>
                <a href="#" onclick="showHelpGuideModal()">Admin Help Guide</a>
                <a href="contactsupport.php">Contact IT Support</a>
                <a href="adminfeedback.php">Submit Feedback</a>

                <h4 class="sidebar-heading">Appointments</h4>
                <a href="viewappointments.php">View Scheduled Appointments</a>
                <a href="viewdoctors.php">View Active Doctors</a>

                <h4 class="sidebar-heading">Analytics</h4>
                <a href="statistics.php">View Statistics</a>
                <a href="adminreport.php">Generate Reports</a>

                <br>
                <br>
            </div>
        </div>
<div class="content">
<section class="image-gallery">
  <h2>All Registered Doctors</h2>
  <div class="gallery-container">
    <?php foreach ($doctors as $doctor): ?>
      <div class="card-box">
        <img src="<?= htmlspecialchars('../' . ($doctor['profile_picture'] ?? 'assets/defaults/doctor_default.png')) ?>" 
             alt="<?= $doctor['first_name'] ?> <?= $doctor['last_name'] ?>">
        <div class="card-info">
          <h3><?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></h3>
          <p><?= htmlspecialchars($doctor['description'] ?? 'No description provided.') ?></p>
        </div>
        <div class="text-white text-center" style="position: absolute; bottom: 15px; left: 0; width: 100%; font-size: 0.85rem;">
            <?= htmlspecialchars($doctor['email']) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
    </div>

</body>
</html>
