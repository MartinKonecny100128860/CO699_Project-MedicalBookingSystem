<?php
// Start session to check authentication
session_start();

// Ensure only logged-in staff can access this page
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php"); // Redirect unauthorised users
    exit(); // Stop execution
}

// Connect to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Ensure proper character encoding

// Retrieve search filter values from query parameters
$nameFilter = $_GET['name'] ?? ''; // Patient name filter
$dobFilter = $_GET['dob'] ?? '';   // Date of birth filter

// Base SQL query for retrieving patient records
$sql = "SELECT * FROM users WHERE role = 'patient'";

// Arrays to store parameters for the prepared statement
$params = [];
$types = ''; // String of types for bind_param (e.g., 'ss' for two strings)

// Append name filtering condition if provided
if (!empty($nameFilter)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
    $params[] = "%$nameFilter%"; // Use wildcards for partial matching
    $params[] = "%$nameFilter%";
    $types .= 'ss'; // Two strings
}

// Append date of birth filtering condition if provided
if (!empty($dobFilter)) {
    $sql .= " AND date_of_birth = ?";
    $params[] = $dobFilter;
    $types .= 's'; // One string
}

// Add sorting condition to the final query
$sql .= " ORDER BY first_name ASC";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Bind parameters if filters were applied
if ($params) {
    $stmt->bind_param($types, ...$params);
}

// Execute the prepared statement
$stmt->execute();

// Get the result set
$patients = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Patients</title>
    <meta charset="UTF-8">
    
    <!-- Bootstrap CSS for layout and components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Accessibility Styles -->
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">

    <!-- Page Specific Styles and Scripts -->
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="styles/staffdash.css">
    <script src="scripts/bars.js" defer></script>
    <script src="../accessibility/accessibility.js" defer></script>
</head>
<body>

<?php
    // Set page title and include sidebar/header bars
    $pageTitle = "Dashboard";
    include 'php/bars.php'; // Contains sidebar and top bar
?>

<div class="content">
<div class="container py-5">

    <!-- Display session success or error messages, if any -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
    <?php elseif (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
    <?php endif; ?>

    <!-- Page Heading -->
    <h2 class="fw-bold text-primary mb-4">
        <i class="fas fa-users me-2"></i>All Registered Patients
    </h2>

    <!-- Search and Filter Form -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="Search by name..." value="<?= htmlspecialchars($nameFilter) ?>">
        </div>
        <div class="col-md-4">
            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($dobFilter) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </form>

    <!-- Patient Table -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>City</th>
                        <th>Post Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <!-- Loop through each patient and display their info -->
                <?php while ($row = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['telephone']) ?></td>
                        <td><?= htmlspecialchars($row['city']) ?></td>
                        <td><?= htmlspecialchars($row['post_code']) ?></td>
                        <td>
                            <!-- Edit button for updating patient details -->
                            <a href="editpatient.php?user_id=<?= $row['user_id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
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

<!-- Accessibility tools panel -->
<?php include '../accessibility/accessibility.php'; ?>

</body>
</html>
