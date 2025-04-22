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
</head>
<body>

<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>


<div class="content">
    <h2 class="text-center mb-4">Doctor Appointments</h2>

    <div class="table-container">
        <table class="table table-hover text-center align-middle">
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
                        <tr id="row-<?= $row['appointment_id'] ?>">
                            <td><?= htmlspecialchars($row['patient_first'] . " " . $row['patient_last']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_day']) ?></td>
                            <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-primary btn-sm view-btn"
                                    data-bs-toggle="modal" data-bs-target="#viewPatientModal"
                                    data-patient-id="<?= $row['patient_id'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>

                                <button class="btn btn-success btn-sm complete-btn"
                                    onclick="completeAppointment(<?= $row['appointment_id'] ?>)">
                                    <i class="fas fa-check-circle"></i> Complete
                                </button>

                                <button class="btn btn-warning btn-sm"
                                    onclick="location.href='createmedicalreport.php?patient_id=<?= $row['patient_id'] ?>'">
                                    <i class="fas fa-file-medical"></i> Report
                                </button>


                                <button class="btn btn-info btn-sm prescription-btn"
                                    onclick="window.location.href='createprescription.php?patient_id=<?= $row['patient_id'] ?>'">
                                    <i class="fas fa-prescription-bottle-alt"></i> Prescription
                                </button>

                                <a href="createtest.php?patient_id=<?= $row['patient_id'] ?>" class="btn btn-secondary btn-sm results-btn">
                                    <i class="fas fa-vials"></i> Tests
                                </a>


                                <button class="btn btn-danger btn-sm cancel-btn"
                                    onclick="cancelAppointment(<?= $row['appointment_id'] ?>)">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
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

<style>
/* General Styling */
body {
    background-color: #f8f9fa;
    font-family: 'Arial', sans-serif;
}

/* Table Container */
.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Table */
.table {
    border-collapse: collapse;
    width: 100%;
}

.table thead {
    background: #007bff;
    color: white;
    text-transform: uppercase;
}

.table th, .table td {
    padding: 12px;
    text-align: center;
}

.table-hover tbody tr:hover {
    background: #f1f1f1;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    justify-content: center;
}

/* Button Styling */
.btn {
    font-size: 14px;
    font-weight: 600;
    padding: 7px 12px;
    border-radius: 6px;
    transition: 0.2s ease-in-out;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn i {
    font-size: 12px;
}

.btn:hover {
    transform: scale(1.05);
}

/* Button Colors */
.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-info { background: #17a2b8; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-danger { background: #dc3545; color: white; }

/* Responsive Design */
@media (max-width: 768px) {
    .table-container {
        padding: 15px;
    }

    .table th, .table td {
        padding: 10px;
    }

    .action-buttons {
        flex-direction: column;
    }
}

.modal-body {
            font-size: 16px;
        }
</style>


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

<?php include '../accessibility/accessibility.php'; ?>


</body>
</html>
