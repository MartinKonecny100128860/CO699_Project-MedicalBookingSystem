<?php
session_start(); // Start a new session or resume the existing one for user session management.

// Database connection setup
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "";
$dbName = "medicalbookingsystem"; // Ensure correct database name (CASE SENSITIVE)

// Create database connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
$conn->set_charset("utf8mb4");

// Force correct database selection (for safety)
$conn->query("USE medicalbookingsystem");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Variable to store error messages

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare the SQL statement to fetch user details
    $sql = "SELECT user_id, password, role, profile_picture FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // If user exists, fetch the data
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password, $role, $profile_picture);
        $stmt->fetch();

        // Handle legacy MD5-hashed passwords (Backward compatibility)
        if (strlen($hashed_password) === 32 && md5($password) === $hashed_password) {
            // Update the old MD5 hash to a secure password hash
            $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $new_hashed_password, $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            // Update the hashed password variable for subsequent verification
            $hashed_password = $new_hashed_password;
        }

        // Verify password
        if (!password_verify($password, $hashed_password)) {
            $error = "Invalid password.";
        } else {
            // Successful login
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['profile_picture'] = $profile_picture ?: 'assets/defaults/admin_default.png';

            // Update last active timestamp
            $conn->query("UPDATE users SET last_active = NOW() WHERE user_id = " . $_SESSION['user_id']);

            // Function to Get User's Real IP Address
            function getUserIP() {
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    return $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]); // Get first valid IP
                } else {
                    return $_SERVER['REMOTE_ADDR'];
                }
            }

            // Get the user's real IP
            $ip_address = getUserIP();

            // If local (127.0.0.1 or ::1), use an external API to get real public IP
            if ($ip_address === '::1' || $ip_address === '127.0.0.1') {
                $external_ip = @file_get_contents("https://api64.ipify.org?format=json");
                if ($external_ip) {
                    $ip_data = json_decode($external_ip, true);
                    $ip_address = $ip_data['ip'] ?? 'Unknown';
                }
            }

            // Fetch Geolocation Data
            $api_url = "http://ip-api.com/json/$ip_address?fields=status,country,countryCode,city,query";
            $response = @file_get_contents($api_url);
            $geo_data = $response ? json_decode($response, true) : null;

            // Assign country & city with fallback
            if ($geo_data && $geo_data['status'] === 'success') {
                $country = $geo_data['country'] ?? 'Unknown';
                $city = $geo_data['city'] ?? 'Unknown';
                $country_code = strtolower($geo_data['countryCode'] ?? '');
            } else {
                $country = 'Unknown';
                $city = 'Unknown';
                $country_code = '';
            }

            // If location is still unknown, use default values
            if ($country === 'Unknown' || $city === 'Unknown') {
                $country = 'United Kingdom';
                $city = 'Nelson';
                $country_code = 'GB';
            }

            // Store Last Login Info in Database
            $updateQuery = "UPDATE users SET last_active = NOW(), last_ip = ?, last_country = ?, last_city = ?, last_country_code = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssi", $ip_address, $country, $city, $country_code, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // Redirect user based on role
            $redirectMap = [
                'admin' => "admin/admindash.php",
                'doctor' => "doctor/doctordash.php",
                'staff' => "staff/staffdash.php",
                'patient' => "patient/patientdash.php"
            ];

            if (isset($redirectMap[$role])) {
                header("Location: " . $redirectMap[$role]);
                exit();
            } else {
                error_log("User role not recognized: $role");
            }
        }
    } else {
        $error = "Invalid username.";
    }
    // $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Page</title>

        <!-- Include Bootstrap for styling -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Include Google reCAPTCHA -->
        <script src="https://www.google.com/recaptcha/enterprise.js" async defer></script>
        <!-- Include Font Awesome for icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="accessibility/highcontrast.css">
    </head>
    <body>
        <!-- Include accessibility menu -->
        <?php include 'accessibility/accessibility.php'; ?>

        <!-- Loading screen -->
        <div id="loading-screen">
            <img src="assets/gifs/loading2.gif" alt="Loading...">
        </div>

        <!-- Logo -->
        <img src="assets/logos/logo-light.png" alt="Medical Booking System Logo" class="logo" id="logo">

        <!-- Login container -->
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <!-- Display error messages if any -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form id="login-form" action="" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group position-relative">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-group-text position-absolute end-0" id="togglePassword">
                            <i id="passwordIcon" class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="g-recaptcha" data-sitekey="6LcMzLwqAAAAAClygQZytKsszDO9FvAGxkZdSoSo"></div>
                <br>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>


        <!-- Include accessibility styles and scripts -->
        <link rel="stylesheet" href="accessibility/accessibility.css">
        <script src="accessibility/accessibility.js" defer></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Hide the loading screen after 2.5 seconds
                const loadingScreen = document.getElementById("loading-screen");
                setTimeout(() => (loadingScreen.style.display = "none"), 2500);

                // Toggle password visibility when the toggle icon is clicked
                document.getElementById("togglePassword").addEventListener("click", function () {
                    const passwordInput = document.getElementById("password");
                    const passwordIcon = document.getElementById("passwordIcon");
                    const isText = passwordInput.type === "text"; // Check if the input type is 'text'
                    passwordInput.type = isText ? "password" : "text"; // Toggle between 'password' and 'text'
                    passwordIcon.classList.toggle("fa-eye", isText); // Update icon for visibility
                    passwordIcon.classList.toggle("fa-eye-slash", !isText); // Update icon for invisibility
                });

                // Update the logo based on the current theme (light or dark)
                function updateLogoBasedOnTheme() {
                    const logo = document.getElementById("logo");
                    logo.src = document.body.classList.contains("dark-mode")
                        ? "assets/logos/logo-dark.png" // Use dark logo if dark mode is enabled
                        : "assets/logos/logo-light.png"; // Use light logo if dark mode is disabled
                }

                // Attach an event listener to the dark mode toggle button to update the logo
                document.getElementById("dark-mode-toggle").addEventListener("click", updateLogoBasedOnTheme);

                // Check localStorage for saved theme preference and apply it
                if (localStorage.getItem("darkMode") === "enabled") {
                    document.body.classList.add("dark-mode"); // Enable dark mode
                    updateLogoBasedOnTheme(); // Update logo for dark mode
                }
            });
        </script>
    </body>
</html>

<style>
    /* Body styling for centering content and adding gradient background */
    body { 
        display: flex; 
        flex-direction: column;
        height: 100vh; 
        align-items: center; 
        justify-content: center; 
        background: linear-gradient(135deg, #e3f2fd 0%, #e0f7fa 100%);
        font-family: 'Arial', sans-serif;
    }

    /* Logo styling for size and position */
    .logo {
        margin-bottom: 20px;
        width: 350px;
        height: auto;
        position: relative;
        left: 5px;
    }

    /* Login container with box shadow and hover effect */
    .login-container { 
        padding: 30px; 
        background: white; 
        border-radius: 10px; 
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.25);
        width: 100%;
        max-width: 360px;
        transition: transform 0.3s ease-in-out;
    }

    .login-container:hover {
        transform: scale(1.03);
    }

    /* Form group and label styling */
    .form-group {
        margin-bottom: 20px;
    }

     label {
        color: #06799e;
        font-weight: bold;
    }

    /* Input field styling with focus effect */
    .form-control {
        border: 1px solidrgb(122, 131, 191);
        border-radius: 5px;
        transition: border-color 0.3s ease-in-out;
    }

    .form-control:focus {
        border-color: #5d68b4;
    }

    /* Button styling for primary submit button */
    .btn-primary {
        background-color: #06799e;
        border-color: #06799e;
        border-radius: 5px;
        padding: 10px 24px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color:rgb(2, 81, 107);
    }

    /* Centering the reCAPTCHA widget */
    .g-recaptcha {
        display: flex;
        justify-content: center;
    }

    /* Loading screen styling */
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 2000;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease-in-out;
    }

    #loading-screen img {
        width: 550px;
        height: 400px;
    }

    /* Styling for the page header */
    h2 {
        color: #06799e;
        text-align: center;
    }

    /* Password toggle button styling */
    #togglePassword {
        border: none;
        background: transparent;
        padding: 0 10px;
        height: 100%;
        display: flex;
        align-items: center;
        color:rgb(55, 56, 56);
        cursor: pointer;
    }

    #togglePassword:hover {
        color:rgb(17, 17, 18);
    }

    /* Add padding for input field with password toggle */
    #password {
        padding-right: 40px;
    }

    /* Input group for seamless design */
    .input-group {
        position: relative;
    }

    /* Styling for alert messages */
    .alert {
        margin-bottom: 20px;
    }

    /* Re-aligning CAPTCHA */
    .g-recaptcha {
        display: flex;
        justify-content: center;
    }
</style>