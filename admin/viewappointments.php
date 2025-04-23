<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$query = "SELECT a.*, 
    p.first_name AS patient_first, p.last_name AS patient_last,
    d.first_name AS doctor_first, d.last_name AS doctor_last
    FROM appointments a
    LEFT JOIN users p ON a.patient_id = p.user_id
    LEFT JOIN users d ON a.doctor_id = d.user_id
    ORDER BY a.appointment_day, a.appointment_time";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="styles/admindash.css">
    <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>

</head>
<body>
            <!-- Header HTML -->
            <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="../assets/logos/logo-dark.png" alt="Logo">
                <h1 style="margin-left: 20px;">Appointments</h1>
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

    <div class ="content">
    <h2 class="mb-4">All Appointments</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Day</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']) ?></td>
                <td><?= htmlspecialchars($row['doctor_first'] . ' ' . $row['doctor_last']) ?></td>
                <td><?= $row['appointment_day'] ?></td>
                <td><?= date('H:i', strtotime($row['appointment_time'])) ?></td>
                <td><span class="badge bg-<?= match ($row['status']) {
                    'Scheduled' => 'success',
                    'Completed' => 'primary',
                    'Cancelled' => 'danger',
                    default => 'secondary'
                } ?>"><?= $row['status'] ?></span></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
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
