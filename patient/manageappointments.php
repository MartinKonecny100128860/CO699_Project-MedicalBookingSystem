<?php
session_start();

// Redirect to login page if the user is not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Establish database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current patient ID from session
$patient_id = $_SESSION['user_id'];

// Fetch appointments for the current patient, ordered by weekday and time
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

// Fetch all doctors for additional reference (e.g. dropdowns or matching)
$doctors = [];
$doctorQuery = "SELECT user_id, first_name, last_name FROM users WHERE role = 'doctor'";
$doctorResult = $conn->query($doctorQuery);

while ($row = $doctorResult->fetch_assoc()) {
    $doctors[] = $row;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="styles/patientdash.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="styles/cards.css">
    <link rel="stylesheet" href="../styles/global.css">


    <!-- Accessibility Script -->
    <script src="../accessibility/accessibility.js" defer></script>
    <script src="scripts/bars.js" defer></script>

    <!-- Inline Page Styles -->
    <style>
        /* General Styling */
        body {
            background-color: #f4f8fc;
            color: #333;
        }

        /* Centered & Responsive Container */
        .container {
            max-width: 100%;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
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
            background: #00819d;
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

<?php
    $pageTitle = "Manage Your Appointments";
    include 'php/bars.php'; // Header and sidebar component
?>

<!-- Main Content Container -->
<div class="content">
    <div class="container">
        <h2 class="h2-style">Manage Your Appointments</h2>
        <!-- Appointment Table -->
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
                            <!-- Doctor Column -->
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

                            <!-- Day Column -->
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

                            <!-- Time Column -->
                            <td>
                                <span id="time-info-<?= $appointment['appointment_id'] ?>">
                                    <?= date("h:i A", strtotime($appointment['appointment_time'])) ?>
                                </span>
                                <select class="form-select time-select" data-appointment="<?= $appointment['appointment_id'] ?>">
                                    <option value="">Select Time</option>
                                </select>
                            </td>

                            <!-- Action Buttons -->
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


<!-- JS Script for Appointment Management -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Load available time slots based on selected doctor and day
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

            // Populate slot options
            slots.forEach(slot => {
                let option = document.createElement("option");
                option.value = slot;
                option.textContent = slot;
                timeSelect.appendChild(option);
            });

            timeSelect.style.display = "block";
        })
        .catch(error => console.error("Error fetching slots:", error));
    }

    // Enable editing mode for an appointment
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");

            // Show selects
            document.querySelector(`.doctor-select[data-appointment="${id}"]`).style.display = "block";
            document.querySelector(`.day-select[data-appointment="${id}"]`).style.display = "block";
            document.querySelector(`.time-select[data-appointment="${id}"]`).style.display = "block";

            // Hide display values
            document.getElementById(`doctor-info-${id}`).style.display = "none";
            document.getElementById(`day-info-${id}`).style.display = "none";
            document.getElementById(`time-info-${id}`).style.display = "none";

            // Toggle buttons
            document.querySelector(`.save-btn[data-id="${id}"]`).style.display = "inline-block";
            document.querySelector(`.edit-btn[data-id="${id}"]`).style.display = "none";
        });
    });

    // Fetch new slots when doctor or day changes
    document.querySelectorAll(".doctor-select, .day-select").forEach(select => {
        select.addEventListener("change", function () {
            const id = this.getAttribute("data-appointment");
            const doctorId = document.querySelector(`.doctor-select[data-appointment="${id}"]`).value;
            const day = document.querySelector(`.day-select[data-appointment="${id}"]`).value;

            if (doctorId && day) {
                loadAvailableSlots(id, doctorId, day);
            }
        });
    });

    // Save the updated appointment
    document.querySelectorAll(".save-btn").forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const doctorId = document.querySelector(`.doctor-select[data-appointment="${id}"]`).value;
            const day = document.querySelector(`.day-select[data-appointment="${id}"]`).value;
            const time = document.querySelector(`.time-select[data-appointment="${id}"]`).value;

            if (!time) {
                alert("Please select a valid time slot.");
                return;
            }

            fetch("update_appointment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `appointment_id=${id}&doctor_id=${doctorId}&appointment_day=${day}&appointment_time=${time}`
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

    // Cancel appointment
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");

            if (!confirm("Are you sure you want to cancel this appointment? This action cannot be undone.")) {
                return;
            }

            fetch("cancel_appointment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `appointment_id=${id}`
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

<!-- Accessibility & Chat Scripts -->
<?php include '../accessibility/accessibility.php'; ?>
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>

</body>
</html>