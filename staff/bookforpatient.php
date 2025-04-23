<?php
session_start();

// Check if the user is logged in and has the 'staff' role
// Redirect to the login page if not authorized
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}

// Establish a connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

// Set the character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// SQL query to retrieve all patients (users with role = 'patient') 
// Ordered by their first name alphabetically
$query = "SELECT user_id, first_name, last_name, email, telephone FROM users WHERE role = 'patient' ORDER BY first_name ASC";

// Execute the query and store the result
$result = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Appointment for Patient</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <h2 class="fw-bold text-primary mb-0">
            <i class="fas fa-calendar-plus me-2"></i> Book Appointment for Patient
        </h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th scope="col"><i class="fas fa-user me-1"></i> Name</th>
                        <th scope="col"><i class="fas fa-envelope me-1"></i> Email</th>
                        <th scope="col"><i class="fas fa-phone me-1"></i> Telephone</th>
                        <th scope="col"><i class="fas fa-cog me-1"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['telephone']) ?></td>
                        <td>
                            <a href="../patient/bookappointment.php?user_id=<?= $row['user_id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-calendar-check me-1"></i> Book
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>

</body>
</html>
