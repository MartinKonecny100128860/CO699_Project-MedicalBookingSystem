<?php
session_start();

// Check if the user is logged in and has a staff role
// If not, redirect them to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}

// Connect to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Set character encoding to support special characters

// Optional: check if a specific patient ID is provided in the GET request for filtering
$filter_patient = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : null;

// Base SQL query to fetch appointment data along with patient and doctor names
$query = "
SELECT a.*, 
       p.first_name AS patient_first, p.last_name AS patient_last, 
       d.first_name AS doctor_first, d.last_name AS doctor_last
FROM appointments a
JOIN users p ON a.patient_id = p.user_id
JOIN users d ON a.doctor_id = d.user_id
";

// If a patient filter is applied, modify the query to include only that patient's appointments
if ($filter_patient) {
    $query .= " WHERE a.patient_id = $filter_patient";
}

// Sort the results by appointment day and time
$query .= " ORDER BY a.appointment_day, a.appointment_time";

// Execute the query and store the result
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       

        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="scripts/bars.js" defer></script>
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/staffdash.css">

        <script src="../accessibility/accessibility.js" defer></script>
</head>
<body>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>
    <div class="content">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Manage Patient Appointments</h2>
        <?php if ($filter_patient): ?>
            <div>
                <span class="badge bg-info text-dark p-2">
                    Showing appointments for Patient ID: <?= $filter_patient ?>
                </span>
                <a href="manageappointments.php" class="btn btn-outline-secondary btn-sm ms-2">Show All</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th scope="col"><i class="fas fa-user me-1"></i> Patient</th>
                        <th scope="col"><i class="fas fa-user-md me-1"></i> Doctor</th>
                        <th scope="col"><i class="fas fa-calendar-day me-1"></i> Day</th>
                        <th scope="col"><i class="fas fa-clock me-1"></i> Time</th>
                        <th scope="col"><i class="fas fa-tag me-1"></i> Status</th>
                        <th scope="col"><i class="fas fa-cog me-1"></i> Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']) ?></td>
                        <td><?= htmlspecialchars($row['doctor_first'] . ' ' . $row['doctor_last']) ?></td>
                        <td><span class="badge bg-secondary"><?= $row['appointment_day'] ?></span></td>
                        <td><code><?= date('H:i', strtotime($row['appointment_time'])) ?></code></td>
                        <td>
                            <?php
                                $status = ucfirst($row['status']);
                                $badge = match ($status) {
                                    'Scheduled' => 'success',
                                    'Arrived'   => 'info',
                                    'Completed' => 'primary',
                                    'Cancelled' => 'danger',
                                    default     => 'secondary'
                                };
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= $status ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] !== 'Cancelled'): ?>
                                <form method="post" action="php/update_appointment.php" class="d-inline">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Already Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

    </div>

    <?php include '../accessibility/accessibility.php'; ?>

</body>
</html>
