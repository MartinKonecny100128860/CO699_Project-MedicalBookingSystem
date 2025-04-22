<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = $_SESSION['user_id'];

// Fetch patients with appointments for this doctor
$sql = "SELECT DISTINCT u.user_id, u.first_name, u.last_name 
        FROM users u 
        JOIN appointments a ON u.user_id = a.patient_id 
        WHERE a.doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$patients = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Test Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

    <style>
/* General Styling */
body {
    background-color: #f4f7fc;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
    padding-bottom: 60px;
}

/* Container */
.containers {
    max-width: 750px;
    margin: auto;
    padding: 20px;
}

/* Card Style Form */
.form-container {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin-top: 40px;
    transition: all 0.3s ease;
}

.form-container:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* Labels */
.form-label {
    font-weight: 600;
    color: #06799e;
    margin-bottom: 6px;
    font-size: 0.95rem;
}

/* Inputs and Selects */
.form-control,
.form-select {
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
    transition: 0.3s ease-in-out;
}

.form-control:focus,
.form-select:focus {
    border-color: #06799e;
    box-shadow: 0 0 6px rgba(6, 121, 158, 0.2);
    outline: none;
}

/* Submit Button */
.btn-primary {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    font-weight: 600;
    background-color: #06799e;
    border: none;
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
}

.btn-primary:hover {
    background-color: rgb(2, 81, 107);
    transform: translateY(-1.5px);
}

/* Upload Container */
.upload-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.upload-container input {
    flex-grow: 1;
}

/* Image Preview */
.preview-img {
    display: none;
    margin-top: 18px;
    max-width: 100%;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .containers {
        max-width: 90%;
    }

    .upload-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .preview-img {
        margin-top: 10px;
    }
}

    </style>
</head>
<body>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

<div class="container">
<div class="containers">
    <h2 class="text-center mb-4" style="color: #007bff;">Create Test Result</h2>

    <div class="form-container">
        <form action="php/create_test.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">

            <!-- Select Patient -->
            <div class="mb-3">
                <label for="patient_id" class="form-label">Select Patient</label>
                <select class="form-select" name="patient_id" required>
                    <option value="">Select a Patient</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['user_id'] ?>">
                            <?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Select Test Type -->
            <div class="mb-3">
                <label for="test_type" class="form-label">Test Type</label>
                <select class="form-select" name="test_type" required>
                    <option value="">Select Test Type</option>
                    <option value="Blood Test">Blood Test</option>
                    <option value="X-ray">X-ray</option>
                    <option value="MRI">MRI</option>
                    <option value="CT Scan">CT Scan</option>
                    <option value="Urinalysis">Urinalysis</option>
                    <option value="ECG">ECG</option>
                </select>
            </div>

            <!-- Test Summary -->
            <div class="mb-3">
                <label for="test_summary" class="form-label">Test Summary</label>
                <textarea class="form-control" name="test_summary" rows="3" required></textarea>
            </div>

            <!-- Diagnosis -->
            <div class="mb-3">
                <label for="test_diagnosis" class="form-label">Diagnosis</label>
                <textarea class="form-control" name="test_diagnosis" rows="3" required></textarea>
            </div>

            <!-- Upload Test Result -->
            <div class="mb-3">
                <label for="test_file" class="form-label">Upload Test Result (Optional)</label>
                <div class="upload-container">
                    <input type="file" class="form-control" name="test_file" accept="image/*, .pdf" onchange="previewImage(event)">
                </div>
                <img id="testPreview" class="preview-img">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Submit Test Result</button>
        </form>
    </div>
</div>
</div>


<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('testPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
<?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
