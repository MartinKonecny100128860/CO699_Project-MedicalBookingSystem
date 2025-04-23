<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];

// Predefined list of specialties
$all_specialties = [
    "Cardiology", "Neurology", "Dermatology", "Pediatrics", "Psychiatry",
    "Orthopedics", "Gastroenterology", "Oncology", "Endocrinology", "Urology",
    "General Medicine", "Obstetrics & Gynecology", "Rheumatology", "Radiology", "ENT"
];

// Fetch doctor’s current specialties
$current_specialties = [];
$specialtyResult = $conn->query("SELECT specialty FROM doctor_specialties WHERE doctor_id = $user_id");
while ($row = $specialtyResult->fetch_assoc()) {
    $current_specialties[] = $row['specialty'];
}

// Fetch doctor’s current description
$descQuery = $conn->query("SELECT description FROM users WHERE user_id = $user_id");
$current_description = $descQuery->fetch_assoc()['description'] ?? "";

// Save form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selected_specialties = $_POST['specialties'] ?? [];
    $description = $conn->real_escape_string($_POST['description']);

    // Update description
    $conn->query("UPDATE users SET description = '$description' WHERE user_id = $user_id");

    // Delete old specialties and insert new ones
    $conn->query("DELETE FROM doctor_specialties WHERE doctor_id = $user_id");
    foreach ($selected_specialties as $spec) {
        $spec = $conn->real_escape_string($spec);
        $conn->query("INSERT INTO doctor_specialties (doctor_id, specialty) VALUES ($user_id, '$spec')");
    }

    header("Location: doctorspecialties.php?success=1");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor Specialties</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- External links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

       
    <!-- stylesheet from styles folder -->
    <link rel="stylesheet" href="styles/doctordash.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="../styles/global.css">

    <script src="scripts/bars.js" defer></script>
    <script src="../accessibility/accessibility.js" defer></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            max-width: 100%;
            margin: 40px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }
        .form-check-label {
            font-weight: 500;
        }
        .specialty-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 20px;
            padding-top: 10px;
        }
        .form-check-input:checked {
            background-color: #06799e;
            border-color: #06799e;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

<div class="content">
<div class="card">
    <h3 div class="h2-style">Update Your Specialties</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">Specialties and description updated successfully!</div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-4">
            <label class="form-label fw-semibold">Select Your Specialties</label>
            <div class="specialty-grid">
                <?php foreach ($all_specialties as $specialty): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="specialties[]" id="<?= $specialty ?>" value="<?= $specialty ?>"
                            <?= in_array($specialty, $current_specialties) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $specialty ?>">
                            <?= $specialty ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label fw-semibold">Your Description</label>
            <textarea class="form-control" name="description" id="description" rows="5" required><?= htmlspecialchars($current_description) ?></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4">Save</button>
        </div>
    </form>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
