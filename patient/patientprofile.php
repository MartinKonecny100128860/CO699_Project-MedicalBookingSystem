<?php
session_start();

// Restrict access to logged-in patients only
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];
$success = $error = null;

// Handle profile form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email        = $conn->real_escape_string($_POST['email']);
    $telephone    = $conn->real_escape_string($_POST['telephone']);
    $house_no     = $conn->real_escape_string($_POST['house_no']);
    $street_name  = $conn->real_escape_string($_POST['street_name']);
    $post_code    = $conn->real_escape_string($_POST['post_code']);
    $city         = $conn->real_escape_string($_POST['city']);
    $new_password = $_POST['password'] ?? "";

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $targetDir = "../uploads/profile_pics/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $filename = time() . '_' . basename($_FILES["profile_picture"]["name"]);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile);

        $relativePath = "uploads/profile_pics/$filename";
        $conn->query("UPDATE users SET profile_picture = '$relativePath' WHERE user_id = $user_id");
        $_SESSION['profile_picture'] = $relativePath;
    }

    // Update password only if filled
    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$hashed' WHERE user_id = $user_id");
    }

    // Update contact and address details
    $update = $conn->prepare("UPDATE users SET email=?, telephone=?, house_no=?, street_name=?, post_code=?, city=? WHERE user_id=?");
    $update->bind_param("ssssssi", $email, $telephone, $house_no, $street_name, $post_code, $city, $user_id);

    if ($update->execute()) {
        $success = "Details updated successfully.";
    } else {
        $error = "Update failed.";
    }

    $update->close();
}

// Fetch current user details
$result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$patient = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="styles/patientdash.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">

    <!-- Scripts -->
    <script src="scripts/bars.js" defer></script>
    <script src="../accessibility/accessibility.js" defer></script>
</head>

<body class="bg-light">
<?php include 'php/bars.php'; ?>

<div class="content">
    <div class="container py-5">
        <div class="card shadow-lg border-0 rounded-4 p-4 bg-white">
            <h2 class="h2-style">My Profile</h2>

            <!-- Display feedback messages -->
            <?php if ($success): ?>
                <div class="alert alert-success text-center"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form method="POST" enctype="multipart/form-data">
                <!-- Profile Picture -->
                <div class="text-center mb-4">
                    <img src="../<?= htmlspecialchars($patient['profile_picture'] ?? 'assets/defaults/user_default.png') ?>" 
                         class="rounded-circle border shadow" 
                         style="width: 140px; height: 140px; object-fit: cover;">
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Change Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control form-control-sm w-auto mx-auto">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="fas fa-envelope me-1"></i>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($patient['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="fas fa-phone-alt me-1"></i>Telephone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($patient['telephone']) ?>">
                    </div>

                    <!-- Address Fields -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">House No.</label>
                        <input type="text" name="house_no" class="form-control" value="<?= htmlspecialchars($patient['house_no']) ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Street Name</label>
                        <input type="text" name="street_name" class="form-control" value="<?= htmlspecialchars($patient['street_name']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Post Code</label>
                        <input type="text" name="post_code" class="form-control" value="<?= htmlspecialchars($patient['post_code']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($patient['city']) ?>">
                    </div>

                    <!-- Password -->
                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="fas fa-key me-1"></i>New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                </div>

                <!-- Save Button -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Button Styling -->
<style>
    .btn-primary {
        background-color: #06799e !important;
        border-color: #06799e !important;
    }

    .btn-primary:hover {
        background-color: rgb(2, 81, 107) !important;
        border-color: rgb(2, 81, 107) !important;
    }
</style>

<?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
