<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = $_SESSION['user_id'];
$message = "";

// Handle schedule submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_schedule'])) {
    $schedule = $_POST['schedule'];

    foreach ($schedule as $day => $data) {
        $status = $data['status'];
        $start_time = isset($data['start_time']) ? $data['start_time'] : NULL;
        $end_time = isset($data['end_time']) ? $data['end_time'] : NULL;

        // Remove old schedule for the day
        $conn->query("DELETE FROM doctor_schedule WHERE user_id = $doctor_id AND day_of_week = '$day'");

        if ($status === "available" && $start_time && $end_time) {
            $stmt = $conn->prepare("INSERT INTO doctor_schedule (user_id, day_of_week, start_time, end_time, available_slots) VALUES (?, ?, ?, ?, ?)");
            $default_slots = 10;
            $stmt->bind_param("isssi", $doctor_id, $day, $start_time, $end_time, $default_slots);
            $stmt->execute();
            $stmt->close();
        }
    }
    $message = "Schedule updated successfully!";
}

// Handle schedule deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_schedule'])) {
    $conn->query("DELETE FROM doctor_schedule WHERE user_id = $doctor_id");
    $message = "Schedule deleted successfully!";
}

// Fetch existing schedule
$schedule_data = [];
$result = $conn->query("SELECT * FROM doctor_schedule WHERE user_id = $doctor_id");
while ($row = $result->fetch_assoc()) {
    $schedule_data[$row['day_of_week']] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedule</title>
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/doctordash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 90px;
            max-width: 800px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
        }
        .schedule-card {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
            background: #ffffff;
            border: 1px solid #ddd;
        }
        .schedule-card:hover {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            background: #f7f7f7;
        }
        .form-check-label {
            font-weight: 600;
        }
        .time-fields {
            display: flex;
            gap: 12px;
        }
        .btn-primary, .btn-danger {
            width: 100%;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .alert {
            text-align: center;
        }
        .delete-container {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
        <!-- Header HTML -->
        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="../assets/logos/logo-dark.png" alt="Logo">
                <h1 style="margin-left: 20px;">Dashboard</h1>
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
                    Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Doctor') ?><br>
                    <small>ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></small>
                </p>
            </div>

            <!-- Scrollable Container Inside Nav Bar -->
            <div class="scroll-container">

            </div>
        </div>
    <div class="container">
        <h2 class="text-center mb-4">Set Your Weekly Schedule</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <form method="post">
            <?php 
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
            foreach ($days as $day):
                $existing = isset($schedule_data[$day]) ? $schedule_data[$day] : null;
            ?>
            <div class="schedule-card">
                <h5 class="mb-2"><?= $day ?></h5>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="schedule[<?= $day ?>][status]" value="available" 
                        <?= ($existing && $existing['start_time']) ? 'checked' : '' ?> required>
                    <label class="form-check-label">Available</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="schedule[<?= $day ?>][status]" value="unavailable" 
                        <?= (!$existing || !$existing['start_time']) ? 'checked' : '' ?>>
                    <label class="form-check-label">Unavailable</label>
                </div>

                <div class="time-fields mt-2" <?= (!$existing || !$existing['start_time']) ? 'style="display:none;"' : '' ?>>
                    <label>From:</label>
                    <input type="time" class="form-control" name="schedule[<?= $day ?>][start_time]" 
                        value="<?= $existing['start_time'] ?? '09:00' ?>">
                    <label>To:</label>
                    <input type="time" class="form-control" name="schedule[<?= $day ?>][end_time]" 
                        value="<?= $existing['end_time'] ?? '18:00' ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <button type="submit" name="save_schedule" class="btn btn-primary mt-3">Save Schedule</button>
        </form>

        <div class="delete-container">
            <form method="post" onsubmit="return confirm('Are you sure you want to delete your entire schedule? This action cannot be undone!');">
                <button type="submit" name="delete_schedule" class="btn btn-danger mt-3">Delete Entire Schedule</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const parentDiv = this.closest('.schedule-card');
                const timeFields = parentDiv.querySelector('.time-fields');
                if (this.value === 'available') {
                    timeFields.style.display = 'flex';
                } else {
                    timeFields.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
