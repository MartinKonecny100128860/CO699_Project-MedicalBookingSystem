<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

// Get all patients
$patients = $conn->query("SELECT * FROM users WHERE role = 'patient' ORDER BY first_name ASC");

$nameFilter = $_GET['name'] ?? '';
$dobFilter = $_GET['dob'] ?? '';

$sql = "SELECT * FROM users WHERE role = 'patient'";
$params = [];
$types = '';

if (!empty($nameFilter)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
    $params[] = "%$nameFilter%";
    $params[] = "%$nameFilter%";
    $types .= 'ss';
}

if (!empty($dobFilter)) {
    $sql .= " AND date_of_birth = ?";
    $params[] = $dobFilter;
    $types .= 's';
}

$sql .= " ORDER BY first_name ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$patients = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Patients</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
<?php elseif (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
<?php endif; ?>

    <h2 class="fw-bold text-primary mb-4"><i class="fas fa-users me-2"></i>All Registered Patients</h2>

    <form class="row g-3 mb-4" method="GET">
    <div class="col-md-4">
        <input type="text" name="name" class="form-control" placeholder="Search by name..." value="<?= htmlspecialchars($nameFilter) ?>">
    </div>
    <div class="col-md-4">
        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($dobFilter) ?>">
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Apply Filters</button>
    </div>
</form>


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
                <?php while ($row = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['telephone']) ?></td>
                        <td><?= htmlspecialchars($row['city']) ?></td>
                        <td><?= htmlspecialchars($row['post_code']) ?></td>
                        <td>
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

<?php include '../accessibility/accessibility.php'; ?>


</body>
</html>
