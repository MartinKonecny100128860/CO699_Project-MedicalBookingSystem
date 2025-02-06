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

    // Get current admin ID
    $admin_id = $_SESSION['user_id'];

    // Fetch current user login details
    $query = "SELECT last_ip, last_country, last_city, last_active FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($last_ip, $last_country, $last_city, $last_active);
    $stmt->fetch();
    $stmt->close();

    // Handle null values
    $last_ip = $last_ip ?? 'Unknown';
    $last_country = $last_country ?? 'Unknown';
    $last_city = $last_city ?? 'Unknown';
    $last_active = $last_active ?? 'Never';

    // Generate country flag URL
    $country_code = strtolower($_SESSION['country_code'] ?? '');
    $flag_url = !empty($country_code) ? "https://flagcdn.com/w40/$country_code.png" : "";

    // Fetch last connected users (limit to last 10)
    $query = "SELECT user_id, role, last_ip, last_country, last_city, last_active FROM users ORDER BY last_active DESC LIMIT 10";
    $result = $conn->query($query);
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Settings</title>

        <!-- Styles & Scripts -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <link rel="stylesheet" href="styles/settings.css">
        <link rel="stylesheet" href="styles/admindash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
    
        <script src="../accessibility/accessibility.js" defer></script>
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
            <div class="scroll-container">
                <h4 class="sidebar-heading">Quick Links</h4>
                <a href="admindash.php">Dashboard</a>
                <a href="settings.php" class="active">Settings</a>
                <a href="statistics.php">Statistics</a>
            </div>
        </div>

            <!-- Last 10 Connected Users -->
        <div class="content">
            <div class="card mt-4 p-4">
                <h3>Last 10 Connected Users</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Role</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_id']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                            <td><?= htmlspecialchars($row['last_ip']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['last_country']) ?>, <?= htmlspecialchars($row['last_city']) ?>
                                <?php 
                                    $country_code = strtolower(trim($row['last_country_code'] ?? '')); // Ensure lowercase and remove spaces
                                    if (!empty($country_code) && strlen($country_code) == 2): // Check it's a valid 2-letter country code
                                ?>
                                    <img src="https://flagcdn.com/w40/<?= $country_code ?>.png" 
                                    alt="<?= htmlspecialchars($row['last_country']) ?> Flag"
                                    title="<?= htmlspecialchars($row['last_country']) ?>"
                                    style="height: 20px;">
                                    <?php else: ?>
                                    <img src="https://flagcdn.com/w40/gb.png" alt="Unknown Flag" title="Unknown Country" style="height: 20px;">
                                    <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['last_active']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
