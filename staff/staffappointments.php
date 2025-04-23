<?php
session_start();

// Check if the staff user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php"); // Redirect to login page if not authenticated
    exit();
}

// Establish database connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Set charset to support special characters

// Get the current day name (e.g., 'Monday', 'Tuesday', etc.)
$todayDayName = date('l');

// Prepare SQL query to fetch today's appointments
// It joins appointments with the patient users table to get patient names
$query = "SELECT a.*, u.first_name, u.last_name 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.user_id 
          WHERE a.appointment_day = ? 
          ORDER BY a.appointment_time ASC";

// Prepare and bind the statement securely
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $todayDayName); // Bind current day to the query
$stmt->execute();

// Get the result set
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Patient Check-In</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
    <h2 class="fw-bold text-primary mb-4">
        Appointments for <?= $todayDayName ?> <small class="text-muted">– Patient Check-In</small>
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th scope="col"><i class="fas fa-clock me-1"></i> Time</th>
                    <th scope="col"><i class="fas fa-user me-1"></i> Patient</th>
                    <th scope="col"><i class="fas fa-tag me-1"></i> Status</th>
                    <th scope="col"><i class="fas fa-check-circle me-1"></i> Action</th>
                </tr>
            </thead>

                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('H:i', strtotime($row['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td>
                                    <?php
                                        $status = ucfirst($row['status']);
                                        $badgeClass = match ($status) {
                                            'Scheduled' => 'warning',
                                            'Arrived' => 'info',
                                            'Completed' => 'success',
                                            'Cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Scheduled'): ?>
                                        <form action="php/check_in_patient.php" method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-sign-in-alt"></i> Check In
                                            </button>
                                        </form>
                                    <?php elseif ($row['status'] === 'Arrived'): ?>
                                        <span class="badge bg-info">Checked In</span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic"><?= $status ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No appointments scheduled for <?= $todayDayName ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../accessibility/accessibility.php'; ?>

</body>
</html>

