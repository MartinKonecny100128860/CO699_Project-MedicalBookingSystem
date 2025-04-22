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
    // Check if the user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $issue_type = htmlspecialchars($_POST['issue_type']);
        $message = htmlspecialchars($_POST['message']);

        // Email configuration
        $to = "dx.32@hotmail.cz";
        $subject = "IT Support Request: $issue_type";
        $emailMessage = "
            <h3>IT Support Request</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Issue Type:</strong> $issue_type</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        ";
        $headers = "From: $email\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Send email
        if (mail($to, $subject, $emailMessage, $headers)) {
            $success_message = "Your message has been sent successfully!";
        } else {
            $error_message = "Error sending email. Please try again later.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contact IT Support</title>

        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

        <link rel="stylesheet" href="styles/admindash.css">
        <link rel="stylesheet" href="styles/modals.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>

    </head>
    <body>
        <div class="header">
            <div style="display: flex; align-items: center;">
            <img src="../assets/logos/logo-dark.png" alt="Logo">
            <h1 style="margin-left: 20px;">IT Support</h1>
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
                <a href="contactsupport.php" class="active">Contact IT Support</a>
                <a href="adminfeedback.php">Send Feedback</a>
            </div>
        </div>

        <div class="contentforms">
        <div class="main-content">
            <h2>Contact IT Support</h2>
            <p>If you're experiencing any issues, please fill out the form below to contact our IT support team.</p>

            <form onsubmit="event.preventDefault(); prepareEmail();">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="issue_type">Issue Type:</label>
                    <select id="issue_type" name="issue_type" required>
                        <option value="Connectivity Issue">Connectivity Issue</option>
                        <option value="Machine Issue">Machine Issue</option>
                        <option value="Software Issue">Software Issue</option>
                        <option value="Printer Issue">Printer Issue</option>
                        <option value="System Crash">System Crash</option>
                        <option value="Email Issues">Email Issues</option>
                        <option value="Login Problems">Login Problems</option>
                        <option value="Data Loss">Data Loss</option>
                        <option value="Backup Issues">Backup Issues</option>
                        <option value="Hardware Repair">Hardware Repair</option>
                        <option value="Other">Other (Please specify below)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="custom-submit-button">Submit</button>
            </form>
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

        <script>
            function prepareEmail() {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const issueType = document.getElementById('issue_type').value;
                const message = document.getElementById('message').value.trim();

                if (!name || !email || !message) {
                    alert('Please fill out all fields.');
                    return false;
                }

                const subject = encodeURIComponent(`IT Support Request: ${issueType}`);
                const body = encodeURIComponent(
                    `Name: ${name}\nEmail: ${email}\nIssue Type: ${issueType}\n\nMessage:\n${message}`
                );
                const mailtoLink = `mailto:dx.32@hotmail.cz?subject=${subject}&body=${body}`;

                window.location.href = mailtoLink;
            }
        </script>
    </body>
</html>
