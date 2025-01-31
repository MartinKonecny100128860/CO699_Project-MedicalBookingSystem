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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="styles/admindash.css">
        <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="../accessibility/accessibility.js" defer></script>
        <script src="scripts/adduser.js"></script>
        <script src="scripts/deleteuser.js"></script>
        <script src="scripts/edituser.js"></script>

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
        <!-- Content -->
        <div class="content">
            <div class="table-container" id="users">
            <h2>Users</h2>
            <button class="btn btn-primary mb-3" onclick="showAddUserModal()">Add New User</button>
            
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['user_id'] ?></td>
                                <td><?= $row['username'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= ucfirst($row['role']) ?></td>
                                <td>
                                <button class="btn btn-warning btn-sm edit-user-btn" 
    data-id="<?= $row['user_id'] ?>">
    Edit
</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $row['user_id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <!-- Edit User Modal -->
    <?php include 'includes/edit_user_modal.php'; ?>

    <!-- Add New User Modal -->
    <?php include 'includes/add_user_modal.php'; ?>




    <script>
        function showAddUserModal() {
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        }
        function showEditUserModal() {
            const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        }

    </script>

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