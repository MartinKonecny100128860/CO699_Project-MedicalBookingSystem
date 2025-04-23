<?php
    session_start();

    // Redirect to login page if not logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: new_login.php");
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
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       

        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="scripts/bars.js" defer></script>
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/staffdash.css">

        <script src="../accessibility/accessibility.js" defer></script>
        

    <style>

        .task-card {
            margin-bottom: 15px;
        }

        .staff-card-icon {
    font-size: 1.6rem;
    padding: 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}


    </style>
</head>
<body>

<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

<div class="content">
    <h3 class="mb-4 fw-bold">Staff Dashboard</h3>
    <div class="row g-4">

        <!-- 1. Check-In Patients -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-success text-white">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Patient Check-In</h5>
                        <p class="text-muted small mb-0">Mark patients as 'Arrived' for appointments.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="staffappointments.php" class="btn btn-success w-100 text-white">Check-In</a>
                </div>
            </div>
        </div>

        <!-- 2. Add New Patient -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-primary text-white">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Add New Patient</h5>
                        <p class="text-muted small mb-0">Register patients with login credentials.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="registerpatient.php" class="btn btn-primary w-100">Register</a>
                </div>
            </div>
        </div>

        <!-- 3. View Patients -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-info text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">View Patients</h5>
                        <p class="text-muted small mb-0">Browse and manage patient details.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="viewpatients.php" class="btn btn-info w-100 text-white">View</a>
                </div>
            </div>
        </div>

        <!-- 4. Book Appointment -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-warning text-white">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Book Appointment</h5>
                        <p class="text-muted small mb-0">Schedule appointments on behalf of patients.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="bookforpatient.php" class="btn btn-warning w-100 text-white">Book</a>
                </div>
            </div>
        </div>

        <!-- 5. Manage Appointments -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-secondary text-white">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Manage Appointments</h5>
                        <p class="text-muted small mb-0">Cancel existing bookings.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="manageappointments.php" class="btn btn-secondary w-100 text-white">Manage</a>
                </div>
            </div>
        </div>

        <!-- 6. Medical Supplies Tracker -->
        <div class="col-md-6 col-lg-4">
            <div class="staff-card shadow-sm d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start gap-3">
                    <div class="staff-card-icon bg-dark text-white">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Supplies Tracker</h5>
                        <p class="text-muted small mb-0">Monitor and manage supply stock levels.</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="medicalsupplies.php" class="btn btn-dark w-100">Track</a>
                </div>
            </div>
        </div>

    </div>
</div>



</div>

    </div>



    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../accessibility/accessibility.php'; ?>




</body>
</html>
