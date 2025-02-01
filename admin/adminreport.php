<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Admin Info
$admin_id = $_SESSION['user_id'] ?? 'N/A';

// Query to fetch full name from DB
$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name);
$stmt->fetch();
$stmt->close();

// Store the full name correctly
$admin_full_name = trim("$first_name $last_name") ?: "N/A";


// Get profile picture
$profilePictureQuery = "SELECT profile_picture FROM users WHERE user_id = ?";
$profilePictureStmt = $conn->prepare($profilePictureQuery);
if ($profilePictureStmt) {
    $profilePictureStmt->bind_param("i", $admin_id);
    $profilePictureStmt->execute();
    $profilePictureStmt->bind_result($profile_picture);
    $profilePictureStmt->fetch();
    $profilePictureStmt->close();
}

$_SESSION['profile_picture'] = $profile_picture ?? 'assets/defaults/admin_default.png';

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles/admindash.css">
    <link rel="stylesheet" href="styles/modals.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="../accessibility/accessibility.js" defer></script>
    <script src="scripts/edituser.js"></script>
    <script src="scripts/adduser.js"></script>
    <link rel="stylesheet" href="styles/report.css">

</head>
<body>

        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="../assets/logos/logo-dark.png" alt="Logo">
                <h1 style="margin-left: 20px;">Admin Dashboard</h1>
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
            <a href="#users" class="active">Manage Users</a>
            <a href="admindash.php">Dashboard</a>
            <a href="#">Statistics</a>
            <a href="#">Settings</a>
        </div>


    <!-- 🔷 Report Form -->
    <div class="report-container">
        <div class="report-card">
            <h2>Generate Medical Report</h2>

            <form action="php/generate_report.php" method="POST">
            <div class="mb-3">
                <label for="admin_id" class="form-label">Admin's ID</label>
                <input type="text" class="form-control" id="admin_id" name="admin_id" 
                    value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'N/A'; ?>" 
                    readonly>
            </div>

            <div class="mb-3">
                <label for="admin_name" class="form-label">Admin's Full Name</label>
                <input type="text" class="form-control" id="admin_name" name="admin_name" 
                value="<?= htmlspecialchars($admin_full_name) ?>" readonly>
            </div>


                <div class="mb-3">
                    <label for="report_title" class="form-label">Report Title</label>
                    <input type="text" class="form-control" id="report_title" name="report_title" required>
                </div>

                <div class="mb-3">
                    <label for="report_content" class="form-label">Report Content</label>
                    <textarea class="form-control" id="report_content" name="report_content" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Download PDF</button>
            </form>
        </div>
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
