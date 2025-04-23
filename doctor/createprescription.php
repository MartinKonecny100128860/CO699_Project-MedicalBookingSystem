<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    header("Location: ../new_login.php");
    exit();
}

// Ensure a patient ID is provided
if (!isset($_GET['patient_id'])) {
    die("No patient selected.");
}

$patient_id = $_GET['patient_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Prescription</title>

    <!-- External Libraries -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/doctordash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <link rel="stylesheet" href="styles/bars.css">
        <script src="scripts/bars.js" defer></script>

        <script src="../accessibility/accessibility.js" defer></script>
        <link rel="stylesheet" href="styles/medicalreport.css">

    <style>
/* 🌿 FORM CONTAINER */
.form-container {
    background: #ffffff;
    border-radius: 14px;
    padding: 40px;
    max-width: 900px;
    margin: 40px auto;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 🧭 HEADER */
.form-header {
    font-size: 1.9rem;
    font-weight: 700;
    color: #1c1c1c;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #06799e;
    text-align: center;
    letter-spacing: 0.5px;
}

/* ✏️ INPUTS */
.form-control, .form-select {
    border: 1px solid #d0d7de;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 1rem;
    color: #212529;
    background-color: #fefefe;
    transition: 0.25s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #06799e;
    box-shadow: 0 0 0 0.15rem rgba(6, 121, 158, 0.25);
    outline: none;
}

/* 🎯 BUTTONS */
.btn-custom {
    background-color: #06799e;
    border: none;
    color: white;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 10px;
    width: 100%;
    transition: 0.25s ease;
}

.btn-custom:hover {
    background-color: rgb(2, 81, 107);
    box-shadow: 0 6px 14px rgba(2, 81, 107, 0.3);
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    font-weight: 600;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    width: 100%;
    transition: 0.25s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* 🖼️ FILE PREVIEW (optional) */
.preview-img {
    max-width: 100%;
    border-radius: 12px;
    margin-top: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    display: none;
}

/* 🧠 ACCESSIBILITY + RESPONSIVENESS */
@media (max-width: 768px) {
    .form-container {
        padding: 25px;
    }

    .form-header {
        font-size: 1.5rem;
    }
}

    </style>
</head>
<body>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>


    <div class="content">
        <div class="form-container">
            <!-- Form Header with Image -->
            <div class="form-header">
                <h2>PRESCRIPTIONS</h2>
            </div>

            <!-- Prescription Form -->
            <form action="php/create_prescription.php" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

                <div class="mb-3">
                    <label for="medication" class="form-label">Medication Name</label>
                    <input type="text" name="medication" id="medication" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="dosage" class="form-label">Dosage</label>
                    <input type="text" name="dosage" id="dosage" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="instructions" class="form-label">Instructions</label>
                    <textarea name="instructions" id="instructions" class="form-control" rows="3" required></textarea>
                </div>

                <div class="d-flex gap-2">
                    <!-- SEND PRESCRIPTION BUTTON WITH ICON -->
                    <button type="submit" class="btn btn-custom">
                        <i class="fas fa-paper-plane"></i> Send Prescription
                    </button>

                    <!-- BACK BUTTON WITH ICON -->
                    <a href="viewappointments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
