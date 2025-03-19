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
        die("Error fetching users: " . $conn->error); //  Optional code i can remove
    }

    // Check for and set the profile picture for the logged-in user
    $user_id = $_SESSION['user_id'];
    $profilePictureQuery = "SELECT profile_picture FROM users WHERE user_id = ?";
    $profilePictureStmt = $conn->prepare($profilePictureQuery);
    if (!$profilePictureStmt) {
        die("Error preparing profile picture query: " . $conn->error); // Optional code i can remove
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
        <link rel="stylesheet" href="styles/admindash.css">
        <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        
        <!-- scripts from scripts folder -->
        <script src="scripts/edituser.js"></script>
        <script src="scripts/adduser.js"></script>
        <script src="../accessibility/accessibility.js" defer></script>

        <!-- PHP Files Includes-->
        <?php include 'includes/add_user_modal.php'; ?>
        <?php include 'includes/guide_modal.php'; ?>
    </head>
    <body>

        <!-- Header HTML -->
        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="../assets/logos/logo-dark.png" alt="Logo">
                <h1 style="margin-left: 20px;">Admin Dashboard</h1>
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
                <a href="#scheduled-appointments">View Scheduled Appointments</a>
                <a href="#active-doctors">View Active Doctors</a>

                <h4 class="sidebar-heading">Analytics</h4>
                <a href="statistics.php">View Statistics</a>
                <a href="adminreport.php">Generate Reports</a>

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
                <a href="logs.php" class="navigation-tile">
                    <i class="fas fa-file-alt"></i>
                    <h3>Logs</h3>
                </a>

                <!-- Tile: Statistics -->
                <a href="statistics.php" class="navigation-tile">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Statistics</h3>
                </a>

                <!-- Tile: Settings -->
                <a href="settings.php" class="navigation-tile">
                    <i class="fas fa-cogs"></i>
                    <h3>Settings</h3>
                </a>

                <!-- Tile: Reports -->
                <a href="adminreport.php" class="navigation-tile">
                    <i class="fas fa-file-invoice"></i>
                    <h3>Reports</h3>
                </a>
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
        <!-- Manage Roles Modal -->
        <div class="modal fade" id="manageRolesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content modal-container">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage User Roles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td>
                                        <select class="form-select role-dropdown" data-user-id="<?= $user['user_id'] ?>">
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                            <option value="doctor" <?= $user['role'] == 'doctor' ? 'selected' : '' ?>>Doctor</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm save-role-btn" data-user-id="<?= $user['user_id'] ?>">Save</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!--Script that show modals -->
        <script>
            function showAddUserModal() {
                const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
                addUserModal.show();
            }

            function showManageRolesModal() {
                const modal = new bootstrap.Modal(document.getElementById('manageRolesModal'));
                modal.show();
            }

            function showHelpGuideModal() {
                const helpModal = new bootstrap.Modal(document.getElementById('helpGuideModal'));
                helpModal.show();
            }

            function confirmDownload() {
                if (confirm("Are you sure you want to download the PDF guide?")) {
                    window.location.href = "files/adminguide.pdf";
                }
            }
        </script>
    </body>
</html>
