<?php
// Initialize error variable for later use
$error = null;

// Handle referral acceptance
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accept_referral'])) {

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $conn->set_charset("utf8mb4");

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the report ID from the form
    $report_id = $_POST['report_id'];

    // Prepare the update query to approve the referral
    $update = $conn->prepare("UPDATE medical_reports SET referral_status = 'Approved' WHERE report_id = ?");
    $update->bind_param("i", $report_id);

    // Execute the query and redirect or display error
    if ($update->execute()) {
        header("Location: ERTriage.php?accepted=1");
        exit();
    } else {
        $error = "Failed to accept referral.";
    }

    // Clean up
    $update->close();
    $conn->close();
}

// Handle emergency case form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['accept_referral'])) {

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $conn->set_charset("utf8mb4");

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get and sanitise form input
    $patient = $conn->real_escape_string($_POST['patient_name']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $emergency_type = $conn->real_escape_string($_POST['emergency_type']);
    $severity = $conn->real_escape_string($_POST['severity']);
    $location = $conn->real_escape_string($_POST['location']);
    $issue = $conn->real_escape_string($_POST['issue']);

    // Calculate age from date of birth
    $age = date_diff(date_create($dob), date_create('today'))->y;

    // Insert emergency case into the database
    $query = "INSERT INTO emergency_cases 
        (patient, dob, sex, age, emergency_type, severity, location, issue) 
        VALUES ('$patient', '$dob', '$sex', '$age', '$emergency_type', '$severity', '$location', '$issue')";

    // Execute query and redirect or show error
    if ($conn->query($query)) {
        header("Location: ERTriage.php?success=1");
        exit();
    } else {
        $error = "Failed to submit emergency case.";
    }

    // Close the database connection
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Emergency Triage</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-center text-danger">Emergency Triage Submission</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">Case submitted successfully.</div>
        <?php elseif (isset($_GET['accepted'])): ?>
            <div class="alert alert-success text-center">Referral accepted successfully.</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="card shadow p-4 bg-white">
            <div class="mb-3">
                <label class="form-label">Patient Name</label>
                <input type="text" name="patient_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Sex</label>
                <select name="sex" class="form-select" required>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Emergency Type</label>
                <input type="text" name="emergency_type" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Severity</label>
                <select name="severity" class="form-select" required>
                    <option value="Critical">Red - Critical</option>
                    <option value="Severe">Orange - Severe</option>
                    <option value="Moderate">Yellow - Moderate</option>
                    <option value="Mild">Green - Mild</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. ER, ICU" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Symptoms / Notes</label>
                <textarea name="issue" rows="4" class="form-control" required></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-danger px-4">Submit Case</button>
            </div>
        </form>

        <?php
        // Get referrals
        $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
        $conn->set_charset("utf8mb4");

        $referrals = [];
        $sql = "SELECT mr.report_id, mr.referral_reason, mr.referral_date, mr.referral_status, u.first_name, u.last_name, u.user_id
                FROM medical_reports mr
                JOIN users u ON mr.referred_by = u.user_id
                WHERE mr.referral_status = 'Pending'
                ORDER BY mr.referral_date DESC";

        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $referrals[] = $row;
        }
        $conn->close();
        ?>

        <?php if (count($referrals) > 0): ?>
            <hr class="my-5">
            <h3 class="text-center text-primary mb-4">Doctor Referrals</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover bg-white shadow-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Doctor</th>
                            <th>Reason for Referral</th>
                            <th>Referred On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $ref): ?>
                            <tr>
                                <td><?= htmlspecialchars($ref['first_name'] . ' ' . $ref['last_name']) ?> (ID: <?= $ref['user_id'] ?>)</td>
                                <td><?= htmlspecialchars($ref['referral_reason']) ?></td>
                                <td><?= date('d M Y H:i', strtotime($ref['referral_date'])) ?></td>
                                <td>
                                    <?php if ($ref['referral_status'] === 'Pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="accept_referral" value="1">
                                            <input type="hidden" name="report_id" value="<?= $ref['report_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
