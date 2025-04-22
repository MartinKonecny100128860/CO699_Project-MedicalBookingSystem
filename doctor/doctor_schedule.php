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
        <link rel="stylesheet" href="styles/bars.css">
        <script src="scripts/bars.js" defer></script>

        <script src="../accessibility/accessibility.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background-color: #f3f6f9;
    font-family: 'Segoe UI', sans-serif;
}

.container {
    margin-top: 90px;
    max-width: 900px;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0px 6px 14px rgba(0, 0, 0, 0.08);
    border: 1px solid #e3e3e3;
}

h2.text-center {
    font-weight: 700;
    color: #06799e;
    margin-bottom: 30px;
}

.schedule-card {
    background: #fdfdfd;
    border: 1px solid #dedede;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s ease;
}

.schedule-card:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
    background-color: #fcfcfc;
}

.schedule-card h5 {
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.form-check-label {
    font-weight: 500;
    color: #333;
}

.form-check-input:checked {
    background-color: #06799e;
    border-color: #06799e;
}

.time-fields {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    align-items: center;
}

.time-fields label {
    font-weight: 500;
    color: #333;
    margin-bottom: 0;
}

.time-fields input[type="time"] {
    width: 150px;
}

.btn-primary {
    background-color: #06799e;
    border-color: #06799e;
    font-weight: 600;
    font-size: 1rem;
    padding: 10px 24px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: rgb(2, 81, 107);
    border-color: rgb(2, 81, 107);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    font-weight: 600;
    font-size: 1rem;
    padding: 10px 24px;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #c82333;
}

.alert {
    margin-top: 20px;
    font-weight: 500;
    font-size: 0.95rem;
}

.delete-container {
    margin-top: 25px;
    text-align: center;
}
a {
    text-decoration: none !important;
}

    </style>
</head>
<body>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>
<div class= "content">
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
    <?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
