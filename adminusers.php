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
    <link rel="stylesheet" href="styles/admindash.css">
    <link rel="stylesheet" href="styles/accessibility.css">
    <link rel="stylesheet" href="styles/highcontrast.css">
    <script src="scripts/accessibility.js" defer></script>


    <style>

    </style>
</head>
<body>
<div class="header">
    <div style="display: flex; align-items: center;">
        <img src="assets/logo-dark.png" alt="Logo">
        <h1 style="margin-left: 20px;">Admin Dashboard</h1>
    </div>
    <a href="phpfunctions/logout.php" class="power-icon-box">
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
    <a href="#users" class="active">Manage Users</a>
    <a href="#logs">View Logs</a>
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
                                <button class="btn btn-warning btn-sm" onclick="showEditUserModal(<?= $row['user_id'] ?>, '<?= $row['username'] ?>', '<?= $row['email'] ?>')">Edit</button>
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
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editPassword">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveUserChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                            <label for="userType" class="form-label">User Type</label>
                            <select class="form-control" id="userType" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="houseNo" class="form-label">House No / Name</label>
                        <input type="text" class="form-control" id="houseNo" name="house_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="streetName" class="form-label">Street Name</label>
                        <input type="text" class="form-control" id="streetName" name="street_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="postCode" class="form-label">Post Code</label>
                        <input type="text" class="form-control" id="postCode" name="post_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Telephone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" required>
                    </div>
                    <div class="mb-3">
                        <label for="emergencyContact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                        <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addNewUser()">Add User</button>
            </div>
        </div>
    </div>
</div>



    <script>
        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                $.post("phpfunctions/delete_user.php", { id: userId }, function() {
                    alert("User deleted successfully.");
                    location.reload();
                }).fail(function() {
                    alert("Error deleting user.");
                });
            }
        }

        function showEditUserModal(userId, username, email) {
            $('#editUserId').val(userId);
            $('#editUsername').val(username);
            $('#editEmail').val(email);
            $('#editPassword').val('');
            $('#editUserModal').modal('show');
        }

        function saveUserChanges() {
            const userId = $('#editUserId').val();
            const username = $('#editUsername').val();
            const email = $('#editEmail').val();
            const password = $('#editPassword').val();

            $.post("phpfunctions/admin_edit_users.php", { user_id: userId, username, email, password }, function (data) {
                if (data.success) {
                    alert(data.message); // Display success message
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert(data.message); // Display error message
                }
            }, "json").fail(function () {
                alert("Error updating user.");
            });
        }

        function showAddUserModal() {
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        }

        function addNewUser() {
        const formData = new FormData(document.getElementById('addUserForm'));

            $.ajax({
                url: 'phpfunctions/add_user.php', // Ensure this URL matches your server script
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    const data = JSON.parse(response);

                    if (data.success) {
                        alert(data.message); // Show success message

                        // Properly close the modal
                        const addUserModalElement = document.getElementById('addUserModal');
                        const addUserModalInstance = bootstrap.Modal.getOrCreateInstance(addUserModalElement);
                        addUserModalInstance.hide(); // Close the modal programmatically

                        // Clear the form fields after closing
                        document.getElementById('addUserForm').reset();

                        // Reload the page to show the updated user list
                        location.reload();
                    } else {
                        alert(data.message); // Show error message
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error occurred:", status, error);
                    alert("Error adding user.");
                }
            });
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
            <div id="dark-mode-toggle" class="dark-mode-toggle">
                <i id="dark-mode-icon" class="fas fa-toggle-off"></i>
            </div>
        </li>
        <li>
            <span>Text Resizing:</span>
            <button class="text-resize-decrease accessibility-option">A-</button>
            <button class="text-resize-increase accessibility-option">A+</button>
        </li>
        <li>
            <span>High Contrast Mode:</span>
            <button class="high-contrast-enable accessibility-option">Enable</button>
        </li>
        <li>
            <span>Text-to-Speech:</span>
            <button class="accessibility-option">Enable</button>
        </li>
        <li>
            <span>Pause Animations:</span>
            <button class="accessibility-option">Enable</button>
        </li>
    </ul>
</div>

</body>
</html>
