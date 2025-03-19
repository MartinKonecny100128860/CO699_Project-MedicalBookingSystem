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
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .profile-card {
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: #f9f9f9;
        }
        .profile-image {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .navbar {
            background-color: #0044cc;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .task-card {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 50px;
            background-color: #f8f9fa;
            padding: 10px;
        }
    </style>
</head>
<body>

    <!-- Navbar with logout link -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">Staff Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Staff Profile Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card text-center">
                    <img src="<?= $profile_picture ?>" alt="Profile Picture" class="profile-image">
                    <h4 class="mt-3"><?= $username ?></h4>
                    <p class="text-muted"><?= $_SESSION['role'] ?></p>
                </div>
            </div>
            <div class="col-md-8">
                <h3>Welcome, <?= $username ?>!</h3>
                <p>Last login: <?= date('Y-m-d H:i:s') ?></p>
            </div>
        </div>
    </div>

    <!-- Dashboard Content (e.g., upcoming tasks, medical records, etc.) -->
    <div class="container mt-5">
        <h4>Your Tasks for Today</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 1</h5>
                        <p class="card-text">Assist with patient appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 2</h5>
                        <p class="card-text">Prepare medical records for the doctor</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 3</h5>
                        <p class="card-text">Manage patient queries</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <p>&copy; 2025 Medical Booking System</p>
    </footer>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .profile-card {
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: #f9f9f9;
        }
        .profile-image {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .navbar {
            background-color: #0044cc;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .task-card {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 50px;
            background-color: #f8f9fa;
            padding: 10px;
        }
    </style>
</head>
<body>

    <!-- Navbar with logout link -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">Staff Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Staff Profile Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card text-center">
                    <img src="<?= $profile_picture ?>" alt="Profile Picture" class="profile-image">
                    <h4 class="mt-3"><?= $username ?></h4>
                    <p class="text-muted"><?= $_SESSION['role'] ?></p>
                </div>
            </div>
            <div class="col-md-8">
                <h3>Welcome, <?= $username ?>!</h3>
                <p>Last login: <?= date('Y-m-d H:i:s') ?></p>
            </div>
        </div>
    </div>

    <!-- Dashboard Content (e.g., upcoming tasks, medical records, etc.) -->
    <div class="container mt-5">
        <h4>Your Tasks for Today</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 1</h5>
                        <p class="card-text">Assist with patient appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 2</h5>
                        <p class="card-text">Prepare medical records for the doctor</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card task-card">
                    <div class="card-body">
                        <h5 class="card-title">Task 3</h5>
                        <p class="card-text">Manage patient queries</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <p>&copy; 2025 Medical Booking System</p>
    </footer>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
