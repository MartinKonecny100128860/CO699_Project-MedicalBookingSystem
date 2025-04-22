<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Check if patient ID is provided
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
    <title>Create Medical Report</title>
        
    <!-- External links -->
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

    <!-- Custom Styles -->
    <style>
/* FORM CONTAINER */
.form-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    max-width: 900px;
    margin: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* FLAT HEADER STYLE */
.form-header {
    background-color: #06799e;
    height: 70px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.5rem;
    font-weight: 600;
    padding: 0 20px;
    margin-bottom: 20px;
}

/* HEADER ICON */
.form-header i {
    font-size: 1.6rem;
    margin-right: 10px;
}

/* INPUT FIELD STYLES */
.form-control {
    border-radius: 6px;
    padding: 10px;
    border: 1px solid #ccc;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #06799e;
    box-shadow: 0 0 5px rgba(6, 121, 158, 0.3);
    outline: none;
}

/* CUSTOM BUTTON */
.btn-custom {
    background-color: #06799e;
    border: none;
    color: white;
    font-size: 1rem;
    padding: 10px 15px;
    width: 100%;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 6px;
}

.btn-custom:hover {
    background-color: rgb(2, 81, 107);
}

/* SECONDARY BUTTON */
.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 10px 15px;
    width: 100%;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 6px;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* RESPONSIVENESS */
@media (max-width: 768px) {
    .form-container {
        width: 90%;
    }

    .form-header {
        font-size: 1.3rem;
        height: auto;
        padding: 15px;
    }

    .btn-custom,
    .btn-secondary {
        font-size: 0.95rem;
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
            <!-- Form Header with Gradient & Icon -->
            <div class="form-header">
                <i class="fas fa-notes-medical"></i>
                Medical Report
            </div>

            <!-- Medical Report Form -->
            <form action="php/create_medical_report.php" method="POST">
                <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id) ?>">

                <br>

                <div class="mb-3">
                    <label for="diagnosis" class="form-label">Diagnosis</label>
                    <textarea class="form-control" name="diagnosis" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="treatment" class="form-label">Treatment Plan</label>
                    <textarea class="form-control" name="treatment" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Additional Notes</label>
                    <textarea class="form-control" name="notes"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <!-- SUBMIT REPORT BUTTON WITH ICON -->
                    <button type="submit" class="btn btn-custom">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>

                    <!-- CANCEL BUTTON WITH ICON -->
                    <a href="viewappointments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
