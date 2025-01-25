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

    <style>
        body {
            font-family: 'Roboto', sans-serif; /* More modern and readable font */
            background-color: #f4f7f9; /* Softer background color */
            margin: 0;
            padding: 0;
        }
/* Header Styles */
.header {
    background-color:rgb(99, 139, 185); /* Softer and calming blue */
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

/* Dividing Line */
.header::after {
    content: '';
    position: absolute;
    right: 129px; /* Position the dividing line 80px from the right (adjust for icon size) */
    top: 50%; /* Center the line vertically */
    transform: translateY(-50%); /* Ensure it stays vertically centered */
    height: 100%; /* Line height, relative to header height */
    width: 5px; /* Thin dividing line */
    background-color: rgba(255, 255, 255, 0.2); /* Line color */
}

/* Power Icon Box */
.header .power-icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%; /* Circular button */
    background-color: rgba(255, 255, 255, 0.2); /* Light translucent white */
    cursor: pointer;
    transition: transform 0.2s ease, background-color 0.3s ease;

    /* Move the icon more to the left */
    margin-left: auto; /* Ensure alignment to the left of its container */
    margin-right: 20px; /* Adjust spacing from the dividing line */
    position: relative; /* Enable precise control */
    right: 10px; /* Move the icon left by 10px */
}

/* Power Icon Box Hover Effect */
.header .power-icon-box:hover {
    background-color: rgba(255, 255, 255, 0.3); /* Slightly brighter hover effect */
    transform: scale(1.05); /* Subtle zoom-in effect */
}

/* Power Icon */
.header .power-icon-box i {
    font-size: 22px; /* Icon size */
    color: white; /* Icon color */
    transition: color 0.3s ease; /* Smooth color transition */
}

/* Icon Hover Effect */
.header .power-icon-box:hover i {
    color: #FF6F61; /* Coral hover color */
}

/* Logo Styles */
.header img {
    width: 170px; /* Balanced logo size */
    height: auto;
}

/* Title Styles */
.header h1 {
    position: absolute; /* Make the title independent of flexbox alignment */
    left: 50%; /* Center horizontally */
    transform: translateX(-50%); /* Adjust for the title width */
    margin: 0;
    font-size: 26px; /* Larger title for prominence */
    font-weight: bold;
    color: #ffffff;
}


/* Remove Text Decoration */
a {
    text-decoration: none; /* Ensure no underline */
}


/* Sidebar Styles */
.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #eff3f6; /* Softer neutral background for modern look */
    position: fixed;
    top: 60px;
    left: 0;
    padding-top: 30px; /* Add some padding to move everything lower */
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    z-index: 999;
}

/* Sidebar Links */
.sidebar a {
    text-decoration: none;
    color: #333; /* Neutral dark gray for readability */
    display: block;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 500; /* Slightly bold for better readability */
    transition: all 0.3s ease-in-out;
    border-left: 3px solid transparent; /* Indicator for active link */
}

.sidebar a:hover, .sidebar .active {
    background-color: #d4ebf7; /* Light blue for hover and active states */
    color: #0078D7; /* Vibrant blue for active link */
    border-left: 3px solid #0078D7; /* Add active state indicator */
}

/* Profile Picture Container */
.profile-pic-container {
    display: flex;
    flex-direction: column; /* Stack picture and welcome text vertically */
    align-items: center;
    margin-top: 20px;
    margin-bottom: 15px; /* Add spacing below the profile section */
    padding-bottom: 20px; /* Add padding below welcome text for spacing */
    border-bottom: 1px solid #ccc; /* Subtle dividing line below welcome text */
}

/* Profile Picture Wrapper */
.profile-pic-wrapper {
    width: 100px; /* Slightly larger profile picture size */
    height: 100px;
    overflow: hidden;
    border-radius: 50%; /* Circular frame for the image */
    border: 3px solid #0078D7; /* Blue border for emphasis */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

/* Profile Picture */
.profile-pic {
    width: 100%; /* Ensure the image fills the wrapper */
    height: 100%; /* Ensure the image fills the wrapper */
    object-fit: cover; /* Maintain proper aspect ratio */
    transition: transform 0.3s ease; /* Smooth zoom effect */
}

/* Profile Picture Hover Effect */
.profile-pic-wrapper:hover .profile-pic {
    transform: scale(1.05); /* Slight zoom-in effect */
}

/* Welcome Text */
.welcome-text {
    margin-top: 10px;
    text-align: center;
    color: #333; /* Neutral dark gray text */
    font-size: 16px; /* Readable font size */
    font-weight: bold;
}

.welcome-text small {
    color: #555; /* Subtle gray for the ID */
    font-size: 14px;
}

/* Dividing Line Below Welcome Text */
.profile-pic-container::after {
    content: '';
    display: block;
    width: 80%;
    height: 1px;
    background-color: #ccc; /* Light gray dividing line */
    margin-top: 15px;
}

        /* Content Area */
        .content {
            margin-left: 250px;
            padding: 90px 20px; /* Adjusted padding for better spacing */
        }

        /* Table Styles */
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #005b96; /* Consistent header color */
            color: white;
        }

        /* Buttons */
        .btn {
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #009688;
            border-color: #00796b;
            border-radius: 5px;
            padding: 10px 24px;
            transition: background-color 0.3s;
            color: white;
        }

        .btn-primary:hover {
            background-color: #00796b; /* Deeper blue on hover */
        }

        .btn-warning {
            background-color: #f0ad4e;
            color: white;
        }

        .btn-warning:hover {
            background-color: #ec971f; /* Darker shade on hover */
        }

        .btn-danger {
            background-color: #d9534f;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c9302c;
        }

        /* Logs */
        .logs-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logs-container ul {
            list-style-type: none;
            padding: 0;
        }

        .logs-container li {
            background-color: #e2e3e5; /* Lighter for log entries */
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: calc(200px + 100px) 100px;
            }

            .sidebar {
                width: 200px;
                height: 100vh;
                position: fixed;
                top: 60px;
                left: 0;
                overflow-y: auto;
            }

            .header h1 {
                font-size: 20px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }

            .btn {
                font-size: 12px;
                padding: 6px 12px;
            }

            .header img {
                width: 90px;
            }
        }

        @media screen and (max-width: 576px) {
            .sidebar {
                position: static; /* i can change this if nav bar overlaps with content */
                width: 100%;
                height: auto;
                top: 60px;
            }

            .content {
                padding: calc(60px + 10px) 10px; /* i can change this if content is too high or low */
            }

            .header h1 {
                font-size: 18px;
            }

            table {
                font-size: 12px;
            }

            .btn {
                font-size: 10px;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div style="display: flex; align-items: center;">
        <img src="assets/logo.png" alt="Logo">
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
                $.post("delete_user.php", { id: userId }, function() {
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

            $.post("admin_edit_users.php", { user_id: userId, username, email, password }, function (data) {
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

        function deleteLog(logId) {
            if (confirm("Are you sure you want to delete this log?")) {
                $.post("delete_log.php", { log_id: logId }, function(response) {
                    alert(response.message); // Display success message
                    location.reload(); // Reload to reflect the changes
                }, "json").fail(function() {
                    alert("Error deleting log.");
                });
            }
        }

        function showAddUserModal() {
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        }

        function addNewUser() {
    const formData = new FormData(document.getElementById('addUserForm'));

    $.ajax({
        url: 'add_user.php', // Ensure this URL matches your server script
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
</body>
</html>
