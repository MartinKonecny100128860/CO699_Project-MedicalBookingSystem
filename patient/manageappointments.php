<?php
session_start();

// Redirect if not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_SESSION['user_id'];

// Fetch Patient's Appointments
$sql = "SELECT a.appointment_id, a.appointment_day, a.appointment_time, 
               d.user_id AS doctor_id, d.first_name AS doctor_first, d.last_name AS doctor_last
        FROM appointments a
        JOIN users d ON a.doctor_id = d.user_id
        WHERE a.patient_id = ? 
        ORDER BY FIELD(a.appointment_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), a.appointment_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all doctors
$doctors = [];
$doctorQuery = "SELECT user_id, first_name, last_name FROM users WHERE role = 'doctor'";
$doctorResult = $conn->query($doctorQuery);
while ($row = $doctorResult->fetch_assoc()) {
    $doctors[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>

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
/* General Styling */
body {
    background-color: #f4f8fc;
    font-family: 'Inter', sans-serif;
    color: #333;
}

/* Centered & Responsive Container */
.container {
    max-width: 850px;
    margin: 40px auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
}

/* Header */
h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Table Styling */
.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.table th {
    background: #3498db;
    color: white;
    text-align: center;
    padding: 12px;
    font-size: 15px;
}

.table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
    font-size: 14px;
}

/* Row Hover Effect */
.table tr:hover {
    background: #f1f8ff;
}

/* Dropdown Styling */
.form-select, .form-control {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border: 1px solid #d0d5db;
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
    display: none;
}

.form-select:focus, .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
}

/* Buttons */
.btn-action {
    padding: 8px 14px;
    border: none;
    font-weight: 600;
    border-radius: 6px;
    transition: 0.3s ease-in-out;
    text-align: center;
    font-size: 13px;
    cursor: pointer;
}

/* Edit Button */
.btn-edit {
    background-color: #f1c40f;
    color: white;
}

.btn-edit:hover {
    background-color: #e1b10d;
}

/* Save (Update) Button - Visible Only When Editing */
.btn-save {
    background-color: #2ecc71;
    color: white;
    display: none;
}

.btn-save:hover {
    background-color: #27ae60;
}

/* Cancel Button */
.btn-cancel {
    background-color: #e74c3c;
    color: white;
}

.btn-cancel:hover {
    background-color: #c0392b;
}

/* Responsive Table for Mobile */
@media (max-width: 768px) {
    .container {
        max-width: 95%;
        padding: 15px;
    }

    .table th, .table td {
        font-size: 13px;
        padding: 8px;
    }

    .btn-action {
        padding: 6px 10px;
        font-size: 12px;
    }

    .form-select, .form-control {
        font-size: 13px;
        padding: 6px;
    }
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
    <h2>Manage Your Appointments</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($appointments) > 0): ?>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr id="row-<?= $appointment['appointment_id'] ?>">
                            <td>
                                <span id="doctor-info-<?= $appointment['appointment_id'] ?>">
                                    <?= htmlspecialchars($appointment['doctor_first'] . " " . $appointment['doctor_last']) ?>
                                </span>
                                <select class="form-select doctor-select" data-appointment="<?= $appointment['appointment_id'] ?>">
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['user_id'] ?>" <?= $doctor['user_id'] == $appointment['doctor_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($doctor['first_name'] . " " . $doctor['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <span id="day-info-<?= $appointment['appointment_id'] ?>">
                                    <?= htmlspecialchars($appointment['appointment_day']) ?>
                                </span>
                                <select class="form-select day-select" data-appointment="<?= $appointment['appointment_id'] ?>">
                                    <?php foreach (["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"] as $day): ?>
                                        <option value="<?= $day ?>" <?= $day == $appointment['appointment_day'] ? 'selected' : '' ?>>
                                            <?= $day ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <span id="time-info-<?= $appointment['appointment_id'] ?>">
                                    <?= date("h:i A", strtotime($appointment['appointment_time'])) ?>
                                </span>
                                <select class="form-select time-select" data-appointment="<?= $appointment['appointment_id'] ?>">
                                    <option value="">Select Time</option>
                                </select>
                            </td>

                            <td>
                                <button class="btn-action btn-edit edit-btn" data-id="<?= $appointment['appointment_id'] ?>">Edit</button>
                                <button class="btn-action btn-save save-btn" data-id="<?= $appointment['appointment_id'] ?>">Save</button>
                                <button class="btn-action btn-cancel cancel-btn" data-id="<?= $appointment['appointment_id'] ?>">Cancel</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No Appointments Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

function loadAvailableSlots(appointmentId, doctorId, day) {
    fetch("fetch_slots.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `doctor_id=${doctorId}&appointment_day=${day}`
    })
    .then(response => response.json())
    .then(slots => {
        let timeSelect = document.querySelector(`.time-select[data-appointment="${appointmentId}"]`);
        timeSelect.innerHTML = '<option value="">Select Time</option>';

        if (slots.error) {
            console.error(slots.error);
            return;
        }

        slots.forEach(slot => {
            let option = document.createElement("option");
            option.value = slot;
            option.textContent = slot;
            timeSelect.appendChild(option);
        });

        timeSelect.style.display = "block"; // Ensure time dropdown is visible
    })
    .catch(error => console.error("Error fetching slots:", error));
}

// Enable editing when clicking Edit button
document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function () {
        let appointmentId = this.getAttribute("data-id");

        document.querySelector(`.doctor-select[data-appointment="${appointmentId}"]`).style.display = "block";
        document.querySelector(`.day-select[data-appointment="${appointmentId}"]`).style.display = "block";
        document.querySelector(`.time-select[data-appointment="${appointmentId}"]`).style.display = "block";

        document.getElementById(`doctor-info-${appointmentId}`).style.display = "none";
        document.getElementById(`day-info-${appointmentId}`).style.display = "none";
        document.getElementById(`time-info-${appointmentId}`).style.display = "none";

        document.querySelector(`.save-btn[data-id="${appointmentId}"]`).style.display = "inline-block";
        document.querySelector(`.edit-btn[data-id="${appointmentId}"]`).style.display = "none";
    });
});

// Detect doctor or day change and reload time slots
document.querySelectorAll(".doctor-select, .day-select").forEach(select => {
    select.addEventListener("change", function () {
        let appointmentId = this.getAttribute("data-appointment");
        let doctorId = document.querySelector(`.doctor-select[data-appointment="${appointmentId}"]`).value;
        let day = document.querySelector(`.day-select[data-appointment="${appointmentId}"]`).value;

        if (doctorId && day) {
            loadAvailableSlots(appointmentId, doctorId, day);
        }
    });
});

// Handle appointment update when clicking 'Save' button
document.querySelectorAll(".save-btn").forEach(button => {
    button.addEventListener("click", function () {
        let appointmentId = this.getAttribute("data-id");
        let doctorId = document.querySelector(`.doctor-select[data-appointment="${appointmentId}"]`).value;
        let day = document.querySelector(`.day-select[data-appointment="${appointmentId}"]`).value;
        let time = document.querySelector(`.time-select[data-appointment="${appointmentId}"]`).value;

        if (!time) {
            alert("Please select a valid time slot.");
            return;
        }

        fetch("update_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `appointment_id=${appointmentId}&doctor_id=${doctorId}&appointment_day=${day}&appointment_time=${time}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Appointment updated successfully!");
                location.reload();
            } else {
                alert("Error updating appointment: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    });
});

});

document.addEventListener("DOMContentLoaded", function () {
    // Handle appointment cancellation when clicking 'Cancel' button
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            let appointmentId = this.getAttribute("data-id");

            if (!confirm("Are you sure you want to cancel this appointment? This action cannot be undone.")) {
                return;
            }

            fetch("cancel_appointment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `appointment_id=${appointmentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Appointment cancelled successfully!");
                    location.reload();
                } else {
                    alert("Error cancelling appointment: " + data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});



</script>

</body>
</html>
