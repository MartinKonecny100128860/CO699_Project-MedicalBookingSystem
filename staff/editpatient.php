<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    die("Patient ID is missing.");
}

$patient_id = intval($_GET['user_id']);

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$result = $conn->query("SELECT * FROM users WHERE user_id = $patient_id AND role = 'patient'");
if ($result->num_rows === 0) {
    die("Patient not found.");
}

$patient = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Patient</title>
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
    <h2 class="fw-bold text-primary mb-4">
        <i class="fas fa-user-edit me-2"></i>Edit Patient Details
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="php/update_patient.php" method="POST">
                <input type="hidden" name="user_id" value="<?= $patient['user_id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($patient['first_name']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($patient['last_name']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="telephone" value="<?= htmlspecialchars($patient['telephone']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">House No</label>
                        <input type="text" name="house_no" value="<?= htmlspecialchars($patient['house_no']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Street Name</label>
                        <input type="text" name="street_name" value="<?= htmlspecialchars($patient['street_name']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Post Code</label>
                        <input type="text" name="post_code" value="<?= htmlspecialchars($patient['post_code']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($patient['city']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" value="<?= htmlspecialchars($patient['emergency_contact']) ?>" class="form-control" required>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Update Patient
                        </button>
                        <a href="viewpatients.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>


</body>
</html>
