<?php
session_start();

// Ensure request is POST and required parameters exist
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["doctor_id"]) || !isset($_POST["appointment_day"])) {
    echo json_encode(["error" => "Invalid request."]);
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

$doctor_id = intval($_POST["doctor_id"]);
$appointment_day = $conn->real_escape_string($_POST["appointment_day"]);

// Fetch the doctor's working hours for the selected day
$scheduleQuery = "SELECT start_time, end_time FROM doctor_schedule WHERE user_id = ? AND day_of_week = ?";
$stmt = $conn->prepare($scheduleQuery);
$stmt->bind_param("is", $doctor_id, $appointment_day);
$stmt->execute();
$scheduleResult = $stmt->get_result();
$stmt->close();

// Check if schedule exists
if ($scheduleResult->num_rows === 0) {
    echo json_encode(["error" => "No schedule found for this doctor on the selected day."]);
    exit();
}

$schedule = $scheduleResult->fetch_assoc();
$start_time = strtotime($schedule["start_time"]);
$end_time = strtotime($schedule["end_time"]);

if (!$start_time || !$end_time) {
    echo json_encode(["error" => "Invalid schedule times."]);
    exit();
}

// Generate available slots in 30-minute intervals
$available_slots = [];
$current_time = $start_time;

// Fetch already booked slots for this doctor on the selected day
$bookedSlotsQuery = "SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_day = ?";
$stmt = $conn->prepare($bookedSlotsQuery);
$stmt->bind_param("is", $doctor_id, $appointment_day);
$stmt->execute();
$bookedResult = $stmt->get_result();
$stmt->close();

$booked_slots = [];
while ($row = $bookedResult->fetch_assoc()) {
    $booked_slots[] = date("H:i", strtotime($row["appointment_time"]));
}

// Generate available slots that aren't booked
while ($current_time < $end_time) {
    $slot_start = date("H:i", $current_time);
    $current_time += 1800; // +30 minutes
    $slot_end = date("H:i", $current_time);

    if (!in_array($slot_start, $booked_slots)) {
        $available_slots[] = $slot_start;
    }
}

echo json_encode($available_slots);
$conn->close();
?>
