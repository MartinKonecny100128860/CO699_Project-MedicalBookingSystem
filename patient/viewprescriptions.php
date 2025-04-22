<?php
session_start();

// Redirect if not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_SESSION['user_id'];

// Fetch Prescriptions
$sql = "SELECT p.medication, p.dosage, p.instructions, p.created_at, 
               d.first_name AS doctor_first, d.last_name AS doctor_last
        FROM prescriptions p
        JOIN users d ON p.doctor_id = d.user_id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions</title>
        <!-- External links -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/patientdash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="scripts/bars.js" defer></script>
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/cards.css">

        <script src="../accessibility/accessibility.js" defer></script></head>
<body>

<style>
    /* 🌿 General Table Styling */
.table-container {
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: 0.3s ease-in-out;
}

.table-container:hover {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
}

.table-title {
    font-size: 1.8rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
    border-bottom: 3px solid #00819d;
    display: inline-block;
    padding-bottom: 5px;
}

/* 🌿 Table Styles */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
}

.table thead {
    background: #00819d;
    color: white;
}

.table thead th {
    padding: 12px;
    font-weight: bold;
    text-align: left;
}

.table tbody tr {
    transition: 0.3s ease-in-out;
    border-bottom: 1px solid #ddd;
}

.table tbody tr:nth-child(even) {
    background: #f9f9f9;
}

.table tbody tr:hover {
    background: rgba(0, 129, 157, 0.12);
}

.table td {
    padding: 10px;
    color: #444;
}

/* 🌿 No Data Styling */
.table tbody tr td.text-center {
    font-size: 1.1rem;
    font-weight: bold;
    color: #777;
    padding: 15px;
}

/* 🌿 Responsive Table */
@media (max-width: 768px) {
    .table thead {
        display: none;
    }

    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }

    .table tbody tr {
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #ffffff;
    }

    .table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        color: #00819d;
    }
}
</style>

<?php
    $pageTitle = "View Your Prescriptions";
    include 'php/bars.php'; // contains header and sidebar
  ?>
<div class="content">
<div class="container mt-5">
    <div class="table-container">
        <h2 class="table-title">My Prescriptions</h2>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Instructions</th>
                        <th>Prescribed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Medication"><?= htmlspecialchars($row['medication']) ?></td>
                                <td data-label="Dosage"><?= htmlspecialchars($row['dosage']) ?></td>
                                <td data-label="Instructions"><?= htmlspecialchars($row['instructions']) ?></td>
                                <td data-label="Prescribed By"><?= htmlspecialchars($row['doctor_first'] . " " . $row['doctor_last']) ?></td>
                                <td data-label="Date"><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No Prescriptions Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>


<!-- ✅ AI Chat -->
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>


</body>
</html>
