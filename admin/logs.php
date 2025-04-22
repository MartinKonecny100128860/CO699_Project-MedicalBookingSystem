<?php
    session_start();

    // Redirect to login page if not logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Database connection setup
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "medicalbookingsystem";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch logs with filtering, sorting, and searching
    $searchQuery = isset($_GET["search"]) ? "%" . $conn->real_escape_string($_GET["search"]) . "%" : "%";
    $adminFilter = isset($_GET["admin_id"]) && $_GET["admin_id"] !== "" ? intval($_GET["admin_id"]) : null;
    $actionFilter = isset($_GET["action"]) && $_GET["action"] !== "" ? "%" . $conn->real_escape_string($_GET["action"]) . "%" : "%";

    $logsQuery = "SELECT logs.log_id, logs.admin_id, logs.action, 
                        DATE_FORMAT(logs.log_timestamp, '%d/%m/%Y %H:%i:%s') AS timestamp,
                        users.username AS admin_name 
                FROM logs 
                LEFT JOIN users ON logs.admin_id = users.user_id
                WHERE logs.action LIKE ? 
                AND (logs.admin_id = ? OR ? IS NULL)
                ORDER BY logs.log_timestamp DESC";

    $stmt = $conn->prepare($logsQuery);
    $stmt->bind_param("sii", $actionFilter, $adminFilter, $adminFilter);
    $stmt->execute();
    $logsResult = $stmt->get_result();
    $logs = $logsResult->fetch_all(MYSQLI_ASSOC);

    // Fetch all admins for filter dropdown
    $adminQuery = "SELECT user_id, username FROM users WHERE role = 'admin'";
    $adminResult = $conn->query($adminQuery);
    $admins = $adminResult->fetch_all(MYSQLI_ASSOC);

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<link rel="stylesheet" href="styles/theme.css"> <!-- ← your override -->

    <link rel="stylesheet" href="styles/admindash.css">
    <link rel="stylesheet" href="styles/logs.css">
</head>

<body>
    <div class="header">
        <div style="display: flex; align-items: center;">
            <img src="../assets/logos/logo-dark.png" alt="Logo">
            <h1 style="margin-left: 20px;">System Logs</h1>
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
        <div class="scroll-container">
            <h4 class="sidebar-heading">Quick Links</h4>
            <a href="admindash.php">Dashboard</a>
            <a href="logs.php" class="active">View Logs</a>
            <a href="statistics.php">Statistics</a>
        </div>
    </div>

    <div class="content">
        <div class="logs-container">
        <h2>Admin Logs</h2>

        <!-- Search and Filter Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <input type="text" id="searchLogs" class="form-control me-2" placeholder="Search logs by action (e.g., 'Deleted user')">

            <select id="filterAction" class="form-select me-2">
                <option value="">All Actions</option>
                <option value="Added">Added</option>
                <option value="Updated">Updated</option>
                <option value="Deleted">Deleted</option>
            </select>

            <select id="filterAdmin" class="form-select me-2">
                <option value="">All Admins</option>
                <?php foreach ($admins as $admin): ?>
                    <option value="<?= $admin['user_id'] ?>"><?= htmlspecialchars($admin['username']) ?></option>
                <?php endforeach; ?>
            </select>

            <button id="applyFilters" class="btn btn-primary">Apply Filters</button>
        </div>

        <!-- Logs Table -->
        <table class="table table-bordered logs-table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Date & Time</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['admin_name']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['timestamp']) ?></td>
                    <td>
                        <button class="btn btn-danger btn-sm delete-log" data-log-id="<?= $log['log_id'] ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        $(document).ready(function () {
            // Handle search and filtering
            $("#applyFilters").click(function () {
                const searchQuery = $("#searchLogs").val();
                const selectedAction = $("#filterAction").val();
                const selectedAdmin = $("#filterAdmin").val();

                let url = "logs.php?search=" + encodeURIComponent(searchQuery) + "&action=" + encodeURIComponent(selectedAction) + "&admin_id=" + encodeURIComponent(selectedAdmin);
                window.location.href = url;
            });

            // Handle log deletion
            $(".delete-log").click(function () {
                const logId = $(this).data("log-id");

                if (confirm("Are you sure you want to delete this log?")) {
                    $.post("php/delete_log.php", { log_id: logId }, function (response) {
                        if (response.message) {
                            alert(response.message);
                            location.reload();
                        } else if (response.error) {
                            alert("Error: " + response.error);
                        }
                    }, "json");
                }
            });
        });
    </script>
</body>
</html>
