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
        <link rel="stylesheet" href="styles/doctordash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <link rel="stylesheet" href="styles/bars.css">
        <script src="scripts/bars.js" defer></script>


        <script src="../accessibility/accessibility.js" defer></script>

    </head>
    <body>

        <?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

        <div class="content">
            <h2 class="dashboard-title">Doctor's Dashboard</h2>
            <div class="dashboard-grid">
                <a href="viewappointments.php" class="dashboard-item">
                    <img src="../assets/misc/appointments.jpg" alt="Appointments">
                    <p>View This Week's Appointments</p>
                </a>
                <a href="doctor_schedule.php" class="dashboard-item">
                    <img src="../assets/misc/schedule.jpg" alt="Schedule">
                    <p>Manage Your Schedule</p>
                </a>
                <a href="medicalreports.php" class="dashboard-item">
                    <img src="../assets/misc/records.jpg" alt="Medical Records">
                    <p>View Medical Records</p>
                </a>
                <a href="prescribemedication.php" class="dashboard-item">
                    <img src="../assets/misc/prescription.jpg" alt="Prescriptions">
                    <p>Prescribe Medication</p>
                </a>
                <a href="createtest.php" class="dashboard-item">
                    <img src="../assets/misc/emergency.jpg" alt="Test">
                    <p>Tests</p>
                </a>
                <a href="emergencycases.php" class="dashboard-item">
                    <img src="../assets/misc/emergency.jpg" alt="Emergency">
                    <p>Emergency Cases</p>
                </a>
            </div>
        </div>
        
        <?php include '../accessibility/accessibility.php'; ?>
    </body>
</html>
