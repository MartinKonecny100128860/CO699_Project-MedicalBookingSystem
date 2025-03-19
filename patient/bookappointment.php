<?php
session_start();

// Redirect if not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = null;
$available_slots = [];
$message = "";

// Fetch all doctors
$doctors = [];
$doctorQuery = "SELECT user_id, first_name, last_name FROM users WHERE role = 'doctor'";
$result = $conn->query($doctorQuery);
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

// Check if doctor_id is set from the previous form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];

    // Fetch the doctor's schedule based on doctor_id
    $scheduleQuery = "SELECT day_of_week, start_time, end_time FROM doctor_schedule WHERE user_id = $doctor_id";
    $scheduleResult = $conn->query($scheduleQuery);
    $available_days = [];

    // Loop through the result and store the available days
    while ($schedule = $scheduleResult->fetch_assoc()) {
        $day_of_week = $schedule['day_of_week']; // e.g., Monday, Tuesday, etc.
        $available_days[] = $day_of_week; // Store the available day
    }
}

// Handle the form submission for selecting the appointment day and viewing available time slots
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_day'])) {
    $appointment_day = $_POST['appointment_day'];  // Get the selected appointment day
    $doctor_id = $_POST['doctor_id'];

    // Fetch the schedule for the selected day
    $scheduleQuery = "SELECT start_time, end_time FROM doctor_schedule WHERE user_id = $doctor_id AND day_of_week = '$appointment_day'";
    $scheduleResult = $conn->query($scheduleQuery);
    $available_slots = [];

    while ($schedule = $scheduleResult->fetch_assoc()) {
        $start_time = $schedule['start_time'];
        $end_time = $schedule['end_time'];

        // Generate available slots (30 minutes)
        $current_time = strtotime($start_time);
        $end_time = strtotime($end_time);

        while ($current_time < $end_time) {
            $slot_start = date('H:i', $current_time);
            $current_time += 1800; // Add 30 minutes
            $slot_end = date('H:i', $current_time);
            $available_slots[] = ['start' => $slot_start, 'end' => $slot_end];
        }
    }
}

// Handle the appointment booking when the time is selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $appointment_time = $_POST['appointment_time']; // e.g., '10:30' or '10:30 AM'
    $appointment_day = $_POST['appointment_day'];
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor_id'];

    // Get the target date and time for the selected day and time
    $today = new DateTime();
    $targetDay = new DateTime("next $appointment_day"); // Get the next occurrence of the chosen day
    $targetDateTime = $targetDay->format('Y-m-d') . ' ' . $appointment_time;

    // Convert this to a proper datetime object
    $formatted_datetime = new DateTime($targetDateTime);

    // Check if the appointment time is available for the given day
    $appointmentQuery = "SELECT * FROM appointments WHERE doctor_id = $doctor_id AND appointment_day = '$appointment_day' AND appointment_time = '" . $formatted_datetime->format('H:i:s') . "'";
    $appointmentResult = $conn->query($appointmentQuery);

    if ($appointmentResult->num_rows > 0) {
        $message = "This slot is already booked. Please choose a different time.";
    } else {
        // Book the appointment
        $bookQuery = "INSERT INTO appointments (doctor_id, patient_id, appointment_time, appointment_day, status) 
                      VALUES ($doctor_id, $patient_id, '" . $formatted_datetime->format('H:i:s') . "', '$appointment_day', 'scheduled')";
        if ($conn->query($bookQuery) === TRUE) {
            $message = "Appointment booked successfully!";
        } else {
            $message = "Error booking appointment: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/patientdash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>
    <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Arial', sans-serif;
    }
    .container {
        margin-top: 90px;
        max-width: 900px;
        background: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .doctor-card {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        background: #ffffff;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .doctor-card:hover {
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        background: #f7f7f7;
    }
    .btn-primary {
        width: 100%;
        font-weight: 600;
        letter-spacing: 0.5px;
        background-color: #007bff;
        border-color: #007bff;
        padding: 12px;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    .slot-table td, .slot-table th {
        padding: 15px;
        text-align: center;
        vertical-align: middle;
    }
    .slot-table th {
        background-color: #f1f1f1;
    }
    .slot-table td input {
        margin: 5px;
    }
    .alert {
        text-align: center;
        margin-top: 20px;
    }
    .select-container, .slot-container {
        margin-top: 20px;
    }
    .select-container h4, .slot-container h4 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
    select {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    select:focus {
        outline: none;
        border-color: #007bff;
        background-color: #ffffff;
    }
    select option {
        padding: 10px;
        font-size: 1rem;
    }
    .btn-primary {
        font-size: 1.1rem;
        background-color: #28a745;
        border-color: #28a745;
        padding: 10px 20px;
        border-radius: 8px;
    }
    .btn-primary:hover {
        background-color: #218838;
        border-color: #218838;
    }
    .doctor-card h5 {
        font-size: 1.2rem;
        font-weight: 600;
    }
    .slot-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    .slot-table td {
        padding: 12px;
        background-color: #f1f1f1;
        border: 1px solid #ddd;
    }
    .slot-table td input {
        margin: 0;
        cursor: pointer;
    }
    .slot-table td label {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .slot-table td input:checked + label {
        font-weight: bold;
        background-color: #e7f7e7;
        border-radius: 5px;
        padding: 5px;
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
    <h2 class="text-center mb-4">Book an Appointment</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Step 1: Select a Doctor -->
    <h4>Select a Doctor</h4>
    <div class="row">
        <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-4 mb-3">
                <div class="doctor-card">
                    <h5><?= $doctor['first_name'] ?> <?= $doctor['last_name'] ?></h5>
                    <form method="post">
                        <input type="hidden" name="doctor_id" value="<?= $doctor['user_id'] ?>">
                        <button type="submit" class="btn btn-primary">Select Doctor</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Step 2: Select Appointment Day -->
    <?php if (isset($available_days) && count($available_days) > 0): ?>
        <div class="select-container">
            <h4>Select Appointment Day</h4>
            <form method="post">
                <select name="appointment_day" required>
                    <?php foreach ($available_days as $day): ?>
                        <option value="<?= $day ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit" class="btn btn-primary">View Available Time Slots</button>
                <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
            </form>
        </div>
    <?php endif; ?>

    <!-- Step 3: Display Available Time Slots -->
    <?php if (isset($available_slots) && count($available_slots) > 0): ?>
        <div class="slot-container">
            <h4>Available Slots for <?= $appointment_day ?></h4>
            <form method="post">
                <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
                <input type="hidden" name="appointment_day" value="<?= $appointment_day ?>">
                <table class="table table-bordered slot-table">
                    <thead>
                        <tr>
                            <th>Available Time Slots</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_slots as $slot): ?>
                            <tr>
                                <td>
                                    <label>
                                        <input type="radio" name="appointment_time" value="<?= $slot['start'] ?>" required>
                                        <?= $slot['start'] ?> - <?= $slot['end'] ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
            </form>
        </div>
    <?php elseif (isset($available_days) && count($available_days) == 0): ?>
        <p>No available slots for this doctor on the selected day.</p>
    <?php endif; ?>
</div>

</body>
</html>
