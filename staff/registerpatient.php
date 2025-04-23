<?php
// Check if the staff user is logged in
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register New Patient</title>
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
<body class="bg-light">
    <?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
    ?>

    <div class="content">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg">
                        <div class="card-header bg-primary text-white d-flex align-items-center">
                            <i class="fas fa-user-plus fa-lg me-2"></i>
                            <h4 class="mb-0">Register New Patient</h4>
                        </div>
                        <div class="card-body">
                            <form action="php/register_patient_action.php" method="POST" enctype="multipart/form-data">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-user"></i> Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-phone"></i> Telephone</label>
                                        <input type="text" name="telephone" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-id-badge"></i> First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-id-badge"></i> Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-home"></i> House No</label>
                                        <input type="text" name="house_no" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-road"></i> Street Name</label>
                                        <input type="text" name="street_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-mail-bulk"></i> Post Code</label>
                                        <input type="text" name="post_code" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-city"></i> City</label>
                                        <input type="text" name="city" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-phone-alt"></i> Emergency Contact</label>
                                        <input type="text" name="emergency_contact" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-venus-mars"></i> Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="">Select Gender...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other" selected>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"><i class="fas fa-image"></i> Profile Picture (optional)</label>
                                        <input type="file" name="profile_picture" class="form-control">
                                    </div>

                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-check-circle me-1"></i> Register Patient
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div> <!-- card-body -->
                    </div> <!-- card -->
                </div> <!-- col -->
            </div> <!-- row -->
        </div> <!-- container -->
    </div> <!-- container -->

    <?php include '../accessibility/accessibility.php'; ?>



</body>
</html>
