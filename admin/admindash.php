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
        <a href="#" onclick="showAddUserModal()">Add New User</a>
        <a href="#" onclick="showManageRolesModal()">Manage Roles</a>
        <a href="#system-logs">System Logs</a>
        <h4 class="sidebar-heading">Appointments</h4>
        <a href="#scheduled-appointments">View Scheduled Appointments</a>
        <a href="#active-doctors">View Active Doctors</a>
        <h4 class="sidebar-heading">Resources</h4>
        <a href="#" onclick="showHelpGuideModal()">Admin Help Guide</a>
        <a href="contact_support.php">Contact IT Support</a>
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

<!-- Add New User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-container">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Success Message -->
                <div id="successMessage" class="success-message" style="display: none;">
                    New user added! Please close the window or add another user.
                </div>

                <!-- Add User Form -->
                <form id="addUserForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Telephone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="emergencyContact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="houseNo" class="form-label">House No / Name</label>
                            <input type="text" class="form-control" id="houseNo" name="house_no" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="streetName" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="streetName" name="street_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="postCode" class="form-label">Post Code</label>
                            <input type="text" class="form-control" id="postCode" name="post_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                    </div>

                    <!-- ✅ Role Type Selection Field (FIXED) -->
                    <div class="mb-3">
                        <label for="roleType" class="form-label">Role Type</label>
                        <select class="form-control" id="roleType" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                        <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="addNewUser()">Add User</button>
            </div>
        </div>
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

<script>
    function showAddUserModal() {
        const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    }

    function addNewUser() {
    const formData = new FormData(document.getElementById('addUserForm'));

    $.ajax({
        url: 'adminphpfunctions/add_user.php', // Ensure this matches your backend URL
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            let data;

            try {
                data = JSON.parse(response); // Try parsing the response as JSON
            } catch (err) {
                console.error("Error parsing server response:", response);
                alert("An unexpected error occurred. Please try again.");
                return;
            }

            if (data.success) {
                // Show success message
                $("#successMessage").text("New user added! Please close the window or add another user.").fadeIn();

                // Close the modal
                setTimeout(() => {
                    const addUserModalElement = document.getElementById('addUserModal');
                    const addUserModalInstance = bootstrap.Modal.getInstance(addUserModalElement);
                    if (addUserModalInstance) {
                        addUserModalInstance.hide();
                    }
                    document.getElementById("addUserForm").reset();
                    setTimeout(() => location.reload(), 1000);
                }, 2000);
            } else {
                alert("Error: " + data.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error occurred:", status, error);
            alert("Error adding user. Please try again later.");
        }
    });
}




    function showManageRolesModal() {
        const modal = new bootstrap.Modal(document.getElementById('manageRolesModal'));
        modal.show();
    }

    $(document).ready(function () {
        $(".save-role-btn").click(function () {
            const userId = $(this).data("user-id");
            const newRole = $(this).closest("tr").find(".role-dropdown").val();

            $.ajax({
                url: "adminphpfunctions/update_user_role.php",
                type: "POST",
                data: { user_id: userId, role: newRole },
                success: function (response) {
                    let data = JSON.parse(response);
                    if (data.success) {
                        alert("User role updated successfully.");
                        location.reload();
                    } else {
                        alert("Error updating role.");
                    }
                },
                error: function () {
                    alert("An error occurred while updating the role.");
                }
            });
        });
    });
</script>
<script>
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
