<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: new_login.php");
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

// Fetch All Appointments
$sql = "SELECT a.appointment_id, a.appointment_time, a.appointment_day, 
               p.first_name AS patient_first, p.last_name AS patient_last, 
               d.first_name AS doctor_first, d.last_name AS doctor_last
        FROM appointments a
        JOIN users p ON a.patient_id = p.user_id
        JOIN users d ON a.doctor_id = d.user_id
        ORDER BY FIELD(a.appointment_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), a.appointment_time ASC";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
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


    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .filter-container {
            margin-bottom: 15px;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #c82333;
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

        <div class="content">
    <h2 class="text-center">View Appointments</h2>

    <!-- Filters -->
    <div class="filter-container d-flex justify-content-between">
        <input type="text" id="searchBar" class="form-control me-2" placeholder="Search by Doctor or Patient">
        
        <select id="filterDay" class="form-select me-2">
            <option value="">All Days</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
        </select>

        <button class="btn btn-primary" onclick="filterAppointments()">Apply Filters</button>
    </div>

    <!-- Appointments Table -->
    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="appointmentsTableBody">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patient_first'] . " " . $row['patient_last']) ?></td>
                            <td><?= htmlspecialchars($row['doctor_first'] . " " . $row['doctor_last']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_day']) ?></td>
                            <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm cancel-btn" data-id="<?= $row['appointment_id'] ?>">
                                    Cancel
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No Appointments Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function filterAppointments() {
        let searchValue = document.getElementById("searchBar").value.toLowerCase();
        let selectedDay = document.getElementById("filterDay").value.toLowerCase();

        let rows = document.querySelectorAll("#appointmentsTableBody tr");
        rows.forEach(row => {
            let doctor = row.cells[1].textContent.toLowerCase();
            let patient = row.cells[0].textContent.toLowerCase();
            let day = row.cells[2].textContent.toLowerCase();

            let searchMatch = doctor.includes(searchValue) || patient.includes(searchValue);
            let dayMatch = selectedDay === "" || day === selectedDay;

            row.style.display = searchMatch && dayMatch ? "" : "none";
        });
    }

    // Cancel appointment
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function() {
            let appointmentId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to cancel this appointment?")) {
                fetch("cancel_appointment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "appointment_id=" + appointmentId
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });
</script>

<?php include '../accessibility/accessibility.php'; ?>


</body>
</html>
