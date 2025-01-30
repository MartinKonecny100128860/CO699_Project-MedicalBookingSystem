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

    <style>

    </style>
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

    <!-- Scrollable Container -->
    <div class="scroll-container">
        <h4 class="sidebar-heading">Quick Links</h4>
        <a href="#" onclick="showAddUserModal()">Add New User</a>
        <a href="#" onclick="showManageRolesModal()">Manage Roles</a>
        <a href="#system-logs">System Logs</a>
        <h4 class="sidebar-heading">Appointments</h4>
        <a href="#scheduled-appointments">View Scheduled Appointments</a>
        <a href="#active-doctors">View Active Doctors</a>
        <h4 class="sidebar-heading">Resources</h4>
        <a href="#" onclick="showHelpGuideModal()">Admin Help Guide</a>
        <a href="contactsupport.php">Contact IT Support</a>
        <a href="adminfeedback.php">Submit Feedback</a>
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

<!-- Admin Help Guide Modal -->
<div class="modal fade" id="helpGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-container">
            <div class="modal-header">
                <h5 class="modal-title">Admin Help Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Welcome to the Admin Dashboard! Here’s a quick guide to help you navigate:</p>
                <ul>
                    <li><strong>Managing Users:</strong> Use the "Manage Roles" option to assign roles.</li>
                    <li><strong>Contact IT Support:</strong> If you encounter any issues, use the "Contact IT Support" page.</li>
                    <li><strong>Logs & Reports:</strong> You can track system logs in the admin panel.</li>
                </ul>
                <p>For a complete guide, download the full PDF below.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" onclick="confirmDownload()">Download PDF Guide</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/add_user_modal.php'; ?>


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
    function deleteLog(logId) {
            if (confirm("Are you sure you want to delete this log?")) {
                $.post("php/delete_log.php", { log_id: logId }, function(response) {
                    alert(response.message); // Display success message
                    location.reload(); // Reload to reflect the changes
                }, "json").fail(function() {
                    alert("Error deleting log.");
                });
            }
        }
</script>




</body>
</html>
