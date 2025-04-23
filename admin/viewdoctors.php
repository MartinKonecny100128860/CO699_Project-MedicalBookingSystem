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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="styles/gallery.css" rel="stylesheet"> 
    <link rel="stylesheet" href="styles/admindash.css">
    <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


        <script src="../accessibility/accessibility.js" defer></script>

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
            <a href="admindash.php">Dashboard</a>
            <a href="#users" class="active">Manage Users</a>
            <a href="#" onclick="showAddUserModal()">Add New User</a>
            <a href="#" onclick="showManageRolesModal()">Manage Roles</a>
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

                        <!-- Accessibility Icon -->
                        <div id="accessibility-icon" class="accessibility-icon">
            <i class="fa fa-universal-access"></i>
        </div>

        <!-- Accessibility Popup Window -->
        <div id="accessibility-popup" class="accessibility-options">
            <div class="accessibility-popup-header">
                <h5>Accessibility Settings</h5>
                <span id="accessibility-close" class="accessibility-close">&times;</span>
            </div>
            <ul>
                <li>
                    <span>Dark Mode:</span>
                    <div id="dark-mode-toggle" class="dark-mode-toggle"></div>
                </li>
                <li>
                    <span>Text Resizing:</span>
                    <div>
                        <button class="text-resize-decrease accessibility-option">A-</button>
                        <button class="text-resize-increase accessibility-option">A+</button>
                    </div>
                </li>
                <li>
                    <span>High Contrast Mode:</span>
                    <button class="high-contrast-enable accessibility-option">Enable</button>
                </li>
                <li>
                    <span>Text-to-Speech:</span>
                    <button class="tts-on-click-enable accessibility-option">Enable</button>
                </li>
            </ul>
        </div>

</body>
</html>
