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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="styles/admindash.css">

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
</body>
</html>
