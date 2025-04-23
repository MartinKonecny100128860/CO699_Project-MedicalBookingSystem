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

    // Set profile picture for current user if not already set
    $user_id = $_SESSION['user_id'];
    $profilePictureQuery = "SELECT profile_picture FROM users WHERE user_id = ?";
    $profilePictureStmt = $conn->prepare($profilePictureQuery);
    if (!$profilePictureStmt) {
        die("Error preparing profile picture query: " . $conn->error);
    }

    $profilePictureStmt->bind_param("i", $user_id);
    $profilePictureStmt->execute();
    $profilePictureStmt->bind_result($profile_picture);
    $profilePictureStmt->fetch();
    $profilePictureStmt->close();

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

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- External Libraries & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- Global & Component Styles -->
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="styles/patientdash.css">
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="styles/cards.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">

    <!-- Scripts -->
    <script src="scripts/bars.js" defer></script>
    <script src="../accessibility/accessibility.js" defer></script>
</head>
<body>
    <?php
        $pageTitle = "Dashboard";
        include 'php/bars.php';
    ?>

    <div class="content">
        <div class="dashboard-grid">
            <a href="bookappointment.php" class="dashboard-item">
                <img src="../assets/misc/userdash1.png" alt="Appointments">
                <p>Book An Appointment</p>
            </a>
            <a href="manageappointments.php" class="dashboard-item">
                <img src="../assets/misc/schedule.jpg" alt="Schedule">
                <p>Manage Appointments</p>
            </a>
            <a href="viewmedicalreports.php" class="dashboard-item">
                <img src="../assets/misc/records.jpg" alt="Medical Records">
                <p>Medical Reports</p>
            </a>
            <a href="viewprescriptions.php" class="dashboard-item">
                <img src="../assets/misc/prescription.jpg" alt="Prescriptions">
                <p>Prescriptions</p>
            </a>
            <a href="viewtests.php" class="dashboard-item">
                <img src="../assets/misc/results.jpg" alt="Tests">
                <p>Test Results</p>
            </a>
        </div>
    </div>

    <?php include '../accessibility/accessibility.php'; ?>

    <!-- AI Chat Widget -->
    <div id="chat-placeholder"></div>
    <script src="../aichat/chat.js"></script>
</body>
</html>