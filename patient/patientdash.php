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
    $sql = "SELECT user_id, username, email, role FROM users WHERE role IN ('admin', 'staff', 'doctor', 'patient')";
    $result = $conn->query($sql);

    // Check for query errors
    if (!$result) {
        die("Error fetching users: " . $conn->error);
    }

    // Check for and set the profile picture for the logged-in user
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

    // Fetch users excluding patients
    $usersQuery = "SELECT user_id, first_name, last_name, username, role FROM users WHERE role NOT IN ('patient')";
    $usersResult = $conn->query($usersQuery);
    $users = [];

    if ($usersResult->num_rows > 0) {
        while ($row = $usersResult->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>

        <!-- External links -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/patientdash.css">
                <link rel="stylesheet" href="styles/bars.css">

        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="scripts/bars.js" defer></script>
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/cards.css">

        <script src="../accessibility/accessibility.js" defer></script>

    </head>
    <body>

        <?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

        <div class="content">
            <div class="dashboard-grid">
                <a href="bookappointment.php" class="dashboard-item">
                    <img src="../assets/misc/userdash1.png" alt="Appointments">
                    <p>Book An Appointment</p>
                </a>
                <a href="manageappointments.php" class="dashboard-item">
                    <img src="../assets/misc/schedule.jpg" alt="Schedule">
                    <p>Manage Your Appointments</p>
                </a>
                <a href="viewmedicalreports.php" class="dashboard-item">
                    <img src="../assets/misc/records.jpg" alt="Medical Records">
                    <p>View Your Medical Reports</p>
                </a>
                <a href="viewprescriptions.php" class="dashboard-item">
                    <img src="../assets/misc/prescription.jpg" alt="Prescriptions">
                    <p>View Your Prescriptions</p>
                </a>
                <a href="viewtests.php" class="dashboard-item">
                    <img src="../assets/misc/results.jpg" alt="Tests">
                    <p>Recent Test Results</p>
                </a>
            </div>
        </div>

        <?php include '../accessibility/accessibility.php'; ?>


<!-- ✅ AI Chat -->
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>

        
    </body>
</html>
