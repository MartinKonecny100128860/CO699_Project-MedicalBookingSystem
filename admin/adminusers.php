<?php
    session_start();

    // Redirect to login page if not logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Database connection setup (Ensure correct DB name and charset)
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "medicalbookingsystem"; // Corrected database name

    // Create database connection
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    $conn->set_charset("utf8mb4");

    // Check connection
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

    // Default sorting settings
    $orderBy = "user_id"; // Default column to sort
    $orderDir = "ASC"; // Default sorting direction

    // Capture sorting parameters from URL (if set)
    if (isset($_GET['sort_by'])) {
        $allowedColumns = ["user_id", "username", "email", "role"];
        if (in_array($_GET['sort_by'], $allowedColumns)) {
            $orderBy = $_GET['sort_by'];
        }
    }
    if (isset($_GET['order']) && in_array(strtoupper($_GET['order']), ["ASC", "DESC"])) {
        $orderDir = strtoupper($_GET['order']);
    }

    // Capture filtering by role
    $roleFilter = "";
    if (isset($_GET['role']) && in_array($_GET['role'], ["admin", "doctor", "receptionist", "staff", "patient"])) {
        $roleFilter = "AND role = '" . $conn->real_escape_string($_GET['role']) . "'";
    }

    // Capture search input
    $searchQuery = "";
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $conn->real_escape_string($_GET['search']);
        $searchQuery = "AND (username LIKE '%$search%' OR email LIKE '%$search%')";
    }

    // Updated query with sorting, filtering, and search
    $sql = "SELECT user_id, username, first_name, last_name, email, role, date_of_birth FROM users WHERE role IN ('admin', 'staff', 'doctor') $roleFilter $searchQuery ORDER BY $orderBy $orderDir";
    $result = $conn->query($sql);

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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <link rel="stylesheet" href="styles/admindash.css">
        <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>
        <script src="scripts/adduser.js"></script>
        <script src="scripts/deleteuser.js"></script>
        <script src="scripts/edituser.js"></script>

        <!-- Edit User Modal -->
        <?php include 'includes/edit_user_modal.php'; ?>
        <!-- Add New User Modal -->
        <?php include 'includes/add_user_modal.php'; ?>
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
            <a href="admindash.php">Dashboard</a>
            <a href="#users" class="active">Manage Users</a>
            <a href="#" onclick="showAddUserModal()">Add New User</a>
            <a href="#" onclick="showManageRolesModal()">Manage Roles</a>
        </div>

<!-- Content -->
<div class="content">
    <div class="table-container" id="users">
        <h2>Users</h2>
        <button class="btn btn-primary mb-3" onclick="showAddUserModal()">Add New User</button>

        <!-- Search & Filtering -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Search Bar -->
            <input type="text" id="searchUser" class="form-control me-2" placeholder="Search by Name or Email" onkeyup="filterUsers()">

            <!-- Role Filter -->
            <select id="roleFilter" class="form-select me-2" onchange="filterUsers()">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="receptionist">Receptionist</option>
                <option value="staff">Staff</option>
            </select>

            <!-- Sorting Options -->
            <div class="sorting-icons">
                <i class="fas fa-sort-alpha-down sort-icon" data-column="user_id" title="Sort by ID"></i>
                <i class="fas fa-sort-numeric-down sort-icon" data-column="username" title="Sort by Username (A-Z)"></i>
                <i class="fas fa-user-tie sort-icon" data-column="role" title="Sort by Role Type"></i>
            </div>
        </div>

        <!-- User Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><a href="javascript:void(0);" onclick="sortUsers('user_id')">User ID</a></th>
                    <th><a href="javascript:void(0);" onclick="sortUsers('username')">Username</a></th>
                    <th><a href="javascript:void(0);" onclick="sortUsers('first_name')">First Name</a></th>
                    <th><a href="javascript:void(0);" onclick="sortUsers('last_name')">Last Name</a></th>
                    <th><a href="javascript:void(0);" onclick="sortUsers('date_of_birth')">Date of Birth</a></th> 
                    <th><a href="javascript:void(0);" onclick="sortUsers('email')">Email</a></th>
                    <th><a href="javascript:void(0);" onclick="sortUsers('role')">Role</a></th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="userTableBody">
                <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['last_name'] ?? 'N/A') ?></td>
                    <td><?= !empty($row['date_of_birth']) ? date("d/m/Y", strtotime($row['date_of_birth'])) : '-' ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-user-btn" data-id="<?= $row['user_id'] ?>">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $row['user_id'] ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="8">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
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

        <!-- Scripts for this page -->
        <script>
            function showAddUserModal() {
                const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
                addUserModal.show();
            }
            function showEditUserModal() {
                const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editUserModal.show();
            }

            function showManageRolesModal() {
                const modal = new bootstrap.Modal(document.getElementById('manageRolesModal'));
                modal.show();
            }

            document.addEventListener("DOMContentLoaded", function () {
    // Sorting function
    function sortUsers(column) {
        let currentUrl = new URL(window.location.href);
        let currentOrder = currentUrl.searchParams.get("order");

        // Toggle order between ASC and DESC
        let newOrder = (currentOrder === "ASC") ? "DESC" : "ASC";

        // Update the URL parameters
        currentUrl.searchParams.set("sort_by", column);
        currentUrl.searchParams.set("order", newOrder);

        // Reload with the new sorting parameters
        window.location.href = currentUrl.toString();
    }

    // Update sorting icons dynamically
    function updateSortIcons() {
        let currentUrl = new URL(window.location.href);
        let sortBy = currentUrl.searchParams.get("sort_by");
        let order = currentUrl.searchParams.get("order");

        document.querySelectorAll(".sort-icon").forEach(icon => {
            icon.classList.remove("fa-sort-alpha-down", "fa-sort-alpha-up", "fa-sort-numeric-down", "fa-sort-numeric-up", "fa-sort-amount-up", "fa-sort-amount-down");

            let column = icon.getAttribute("data-column");

            if (column === sortBy) {
                if (order === "ASC") {
                    icon.classList.add(column === "user_id" ? "fa-sort-numeric-up" : "fa-sort-alpha-up"); // Up arrow for ASC
                } else {
                    icon.classList.add(column === "user_id" ? "fa-sort-numeric-down" : "fa-sort-alpha-down"); // Down arrow for DESC
                }
            } else {
                icon.classList.add(column === "user_id" ? "fa-sort-numeric-down" : "fa-sort-alpha-down"); // Default icon
            }
        });
    }

    // Function to filter users
    function filterUsers() {
        let searchValue = document.getElementById("searchUser").value.toLowerCase();
        let roleValue = document.getElementById("roleFilter").value.toLowerCase();

        let rows = document.querySelectorAll("#userTableBody tr");
        rows.forEach(row => {
            let username = row.cells[1].textContent.toLowerCase();
            let email = row.cells[5].textContent.toLowerCase();
            let firstName = row.cells[2].textContent.toLowerCase();
            let lastName = row.cells[3].textContent.toLowerCase();
            let role = row.cells[6].textContent.toLowerCase();

            let searchMatch = username.includes(searchValue) || email.includes(searchValue) || firstName.includes(searchValue) || lastName.includes(searchValue);
            let roleMatch = roleValue === "" || role === roleValue;

            row.style.display = searchMatch && roleMatch ? "" : "none";
        });
    }

    // Attach sorting event listeners to sorting icons
    document.querySelectorAll(".sort-icon").forEach(icon => {
        icon.addEventListener("click", function () {
            let column = this.getAttribute("data-column");
            sortUsers(column);
        });
    });

    // Attach event listeners to filter inputs
    document.getElementById("roleFilter").addEventListener("change", filterUsers);
    document.getElementById("searchUser").addEventListener("keyup", filterUsers);

    // Update icons on page load
    updateSortIcons();
});

        </script>
    </body>
</html>