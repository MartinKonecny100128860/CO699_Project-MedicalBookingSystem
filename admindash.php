<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users for admin view (excluding patients)
$sql = "SELECT user_id, username, email, role FROM users WHERE role IN ('admin', 'staff', 'doctor')";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Error fetching users: " . $conn->error); // Optional: Remove in production
}

// Check for and set the profile picture for the logged-in user
$user_id = $_SESSION['user_id'];
$profilePictureQuery = "SELECT profile_picture FROM users WHERE user_id = ?";
$profilePictureStmt = $conn->prepare($profilePictureQuery);
if (!$profilePictureStmt) {
    die("Error preparing profile picture query: " . $conn->error); // Optional: Remove in production
}

$profilePictureStmt->bind_param("i", $user_id);
$profilePictureStmt->execute();
$profilePictureStmt->bind_result($profile_picture);
$profilePictureStmt->fetch();
$profilePictureStmt->close();

// Assign profile picture or default if not set
if (empty($profile_picture)) {
    switch ($_SESSION['role']) {
        case 'admin':
            $_SESSION['profile_picture'] = 'assets/defaults/admin_default.png';
            break;
        case 'doctor':
            $_SESSION['profile_picture'] = 'assets/defaults/doctor_default.png';
            break;
        case 'staff':
            $_SESSION['profile_picture'] = 'assets/defaults/staff_default.png';
            break;
        default:
            $_SESSION['profile_picture'] = 'assets/defaults/user_default.png';
            break;
    }
} else {
    $_SESSION['profile_picture'] = $profile_picture;
}

// Fetch logs
$logsQuery = "SELECT log_id, admin_id, action, DATE_FORMAT(timestamp, '%d/%m/%Y %H:%i:%s') AS formatted_timestamp FROM logs ORDER BY timestamp DESC";
$logsResult = $conn->query($logsQuery);

// Check for logs query errors
if (!$logsResult) {
    die("Error fetching logs: " . $conn->error); // Optional: Remove in production
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="adminstyles/admindash.css">
    <link rel="stylesheet" href="adminstyles/accessibility.css">
    <link rel="stylesheet" href="adminstyles/highcontrast.css">
    <script src="adminscripts/accessibility.js" defer></script>


    <style>

    </style>
</head>
<body>
<div class="header">
    <div style="display: flex; align-items: center;">
        <img src="assets/logo-dark.png" alt="Logo">
        <h1 style="margin-left: 20px;">Admin Dashboard</h1>
    </div>
    <a href="logout.php" class="power-icon-box">
    <i class="material-icons">&#xe8ac;</i>    
</a>
</div>


<div class="sidebar">
    <div class="profile-pic-container">
        <div class="profile-pic-wrapper">
            <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'assets/default_user.jpg') ?>" 
                 alt="Profile Picture" class="profile-pic">
        </div>
        <p class="welcome-text">
            Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?><br>
            <small>ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></small>
        </p>
    </div>

    <!-- Scrollable Container -->
    <div class="scroll-container">
        <h4 class="sidebar-heading">Quick Links</h4>
        <a href="#add-user">Add New User</a>
        <a href="#manage-roles">Manage Roles</a>
        <a href="#system-logs">System Logs</a>
        <h4 class="sidebar-heading">Appointments</h4>
        <a href="#scheduled-appointments">View Scheduled Appointments</a>
        <a href="#active-doctors">View Active Doctors</a>
        <h4 class="sidebar-heading">Resources</h4>
        <a href="#help-guide">Admin Help Guide</a>
        <a href="contact_support.php">Contact IT Support</a>
        <a href="#feedback">Submit Feedback</a>
        <h4 class="sidebar-heading">Analytics</h4>
        <a href="#view-statistics">View Statistics</a>
        <a href="#generate-reports">Generate Reports</a>
        <br>
        <br>
    </div>
</div>





    <!-- Content -->
    <div class="content">
    <div class="navigation-tiles-container">
        <!-- Tile: Users -->
        <a href="adminusers.php" class="navigation-tile">
            <i class="fas fa-users"></i>
            <h3>Users</h3>
        </a>
        <!-- Tile: Logs -->
        <a href="#logs" class="navigation-tile">
            <i class="fas fa-file-alt"></i>
            <h3>Logs</h3>
        </a>
        <!-- Tile: Statistics -->
        <a href="#" class="navigation-tile">
            <i class="fas fa-chart-bar"></i>
            <h3>Statistics</h3>
        </a>
        <!-- Tile: Settings -->
        <a href="#" class="navigation-tile">
            <i class="fas fa-cogs"></i>
            <h3>Settings</h3>
        </a>
        <!-- Tile: Reports -->
        <a href="#" class="navigation-tile">
            <i class="fas fa-file-invoice"></i>
            <h3>Reports</h3>
        </a>
        <!-- Tile: Notifications -->
        <a href="#" class="navigation-tile">
            <i class="fas fa-bell"></i>
            <h3>Notifications</h3>
        </a>
    </div>
        <div class="logs-container mt-4" id="logs">
            <h2>Logs</h2>
            <ul>
                <?php if ($logsResult && $logsResult->num_rows > 0): ?>
                    <?php while ($log = $logsResult->fetch_assoc()): ?>
                        <li>
                            Admin ID: <?= htmlspecialchars($log['admin_id'], ENT_QUOTES, 'UTF-8') ?> 
                            has <?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?> 
                            on <?= htmlspecialchars($log['formatted_timestamp'], ENT_QUOTES, 'UTF-8') ?>
                            <button 
                                class="btn btn-danger btn-sm" 
                                onclick="deleteLog(<?= $log['log_id'] ?>)">
                                &#10005;
                            </button>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No logs available.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>


    <script>
        function deleteLog(logId) {
            if (confirm("Are you sure you want to delete this log?")) {
                $.post("adminphpfunctions/delete_log.php", { log_id: logId }, function(response) {
                    alert(response.message); // Display success message
                    location.reload(); // Reload to reflect the changes
                }, "json").fail(function() {
                    alert("Error deleting log.");
                });
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
