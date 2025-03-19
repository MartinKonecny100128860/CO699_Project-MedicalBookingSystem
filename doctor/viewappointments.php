<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database Connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "medicalbookingsystem";

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user ID and role
$loggedInUserId = $_SESSION['user_id'];
$loggedInUserRole = $_SESSION['role'];

// Fetch Appointments - Show only for logged-in doctor unless admin
if ($loggedInUserRole === 'admin') {
    $sql = "SELECT a.appointment_id, a.appointment_time, a.appointment_day, a.patient_id,
                   p.first_name AS patient_first, p.last_name AS patient_last, p.date_of_birth,
                   d.first_name AS doctor_first, d.last_name AS doctor_last
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN users d ON a.doctor_id = d.user_id
            ORDER BY FIELD(a.appointment_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), a.appointment_time ASC";
} else {
    $sql = "SELECT a.appointment_id, a.appointment_time, a.appointment_day, a.patient_id,
                   p.first_name AS patient_first, p.last_name AS patient_last, p.date_of_birth,
                   d.first_name AS doctor_first, d.last_name AS doctor_last
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN users d ON a.doctor_id = d.user_id
            WHERE a.doctor_id = ?
            ORDER BY FIELD(a.appointment_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), a.appointment_time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

if ($loggedInUserRole === 'admin') {
    $result = $conn->query($sql);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fc;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 85%;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        .btn {
            padding: 7px 12px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-complete {
            background-color: #28a745;
            color: white;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }

        .btn-report {
            background-color: #ffc107;
            color: black;
        }

        .btn-prescription {
            background-color: #6610f2;
            color: white;
        }

        .btn-results {
            background-color: #fd7e14;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .modal-body {
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Doctor Appointments</h2>

    <div class="table-container">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patient_first'] . " " . $row['patient_last']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_day']) ?></td>
                            <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
                            <td>
                            <td>
    <button class="btn btn-primary view-btn" data-bs-toggle="modal" data-bs-target="#viewPatientModal"
        data-patient-id="<?= $row['patient_id'] ?>">View</button>
        <button class="btn btn-success" onclick="completeAppointment(<?= $row['appointment_id'] ?>)">Complete</button>
                                <button class="btn btn-report">Generate Report</button>
                                <button class="btn btn-prescription">Prescription</button>
                                <button class="btn btn-results">Test & Results</button>
                                <button class="btn btn-cancel" onclick="cancelAppointment(<?= $row['appointment_id'] ?>)">Cancel</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No Appointments Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- view modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="patientName"></span></p>
                <p><strong>Date of Birth:</strong> <span id="patientDob"></span></p>
                <p><strong>Email:</strong> <span id="patientEmail"></span></p>
                <p><strong>Phone:</strong> <span id="patientTelephone"></span></p>
                <p><strong>Address:</strong> <span id="patientAddress"></span></p>
                <p><strong>Gender:</strong> <span id="patientGender"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
    // start of view modal code
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".view-btn").forEach(button => {
        button.addEventListener("click", function () {
            let patientId = this.getAttribute("data-patient-id");
            
            console.log("Fetching patient data for ID:", patientId); // Debugging step
            
            fetch("php/fetch_patient.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `patient_id=${patientId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Received patient data:", data); // Debugging step

                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById("patientName").innerText = `${data.first_name} ${data.last_name}`;
                    document.getElementById("patientDob").innerText = data.date_of_birth;
                    document.getElementById("patientEmail").innerText = data.email;
                    document.getElementById("patientAddress").innerText = `${data.house_no} ${data.street_name} ${data.post_code} ${data.city}`;
                    document.getElementById("patientTelephone").innerText = data.telephone;                    
                    document.getElementById("patientGender").innerText = data.gender;
                    document.getElementById("patientTelephone").innerText = data.telephone;

                    let modal = new bootstrap.Modal(document.getElementById("viewPatientModal"));
                    modal.show();
                }
            })
            .catch(error => console.error("Error fetching patient data:", error));
        });
    });
});


document.getElementById("viewPatientModal").addEventListener("hidden.bs.modal", function () {
    document.body.classList.remove("modal-open");
    document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
});

// end of view modal code

function completeAppointment(appointmentId) {
    if (confirm("Mark this appointment as completed?")) {
        fetch("php/complete_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `appointment_id=${appointmentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.success);
                
                // Remove the row smoothly with animation
                let row = document.getElementById(`row-${appointmentId}`);
                if (row) {
                    row.style.transition = "opacity 0.5s ease-out";
                    row.style.opacity = "0";
                    setTimeout(() => row.remove(), 500); // Delay removal for smooth effect
                }
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }
}

function cancelAppointment(appointmentId) {
    if (confirm("Are you sure you want to cancel this appointment?")) {
        fetch("php/cancel_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `appointment_id=${appointmentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.success);

                // Smoothly remove the appointment from the UI
                let row = document.getElementById(`row-${appointmentId}`);
                if (row) {
                    row.style.transition = "opacity 0.5s ease-out";
                    row.style.opacity = "0";
                    setTimeout(() => row.remove(), 500); // Delay removal for smooth fade-out effect
                }
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }
}

</script>

</body>
</html>
