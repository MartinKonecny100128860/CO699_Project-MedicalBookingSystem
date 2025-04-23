<?php
session_start();

// Redirect if not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_SESSION['user_id'];

// Fetch medical reports for the current patient
$sql = "SELECT m.report_id, m.diagnosis, m.treatment, m.notes, m.created_at, 
               m.referral_status, m.referral_reason, 
               d.first_name AS doctor_first, d.last_name AS doctor_last
        FROM medical_reports m
        JOIN users d ON m.doctor_id = d.user_id
        WHERE m.patient_id = ?
        ORDER BY m.created_at DESC";

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
    <title>My Medical Reports</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <link rel="stylesheet" href="styles/patientdash.css">  
    <link rel="stylesheet" href="styles/bars.css">       
    <link rel="stylesheet" href="styles/cards.css">           
    <link rel="stylesheet" href="../styles/global.css">          

    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">

    <script src="scripts/bars.js" defer></script>
    <script src="../accessibility/accessibility.js" defer></script>

    <style>
        /* Body Styling */
        body {
            background-color: #f4f7fc;
        }

        /* Main Container */
        .container {
            max-width: 100%;
            margin: auto;
            padding: 20px;
        }

        /* Page Heading */
        .headers {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 20px;
        }

        /* Wrapper for Table */
        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Table Layout */
        .table {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        /* Table Header Styling */
        .table thead {
            background-color: #007bff;
            color: white;
        }

        /* Table Cell Styling */
        .table th,
        .table td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        /* Table Row Hover Effect */
        .table tbody tr:hover {
            background-color: #f1f5ff;
            transition: 0.3s ease-in-out;
        }

        /* Badge Styles for Referral Status */
        .badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .badge-pending {
            background-color: #ffc107;
            color: black;
        }

        .badge-approved {
            background-color: #28a745;
            color: white;
        }

        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }

        .badge-not-referred {
            background-color: #6c757d;
            color: white;
        }

        /* Responsive Adjustments for Mobile Screens */
        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 15px;
            }

            .table {
                font-size: 14px;
            }

            .badge {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<?php
    $pageTitle = "View Your Medical Reports";
    include 'php/bars.php';
  ?>

<div class="content">
<div class="container">
    <h2 class="h2-style"><i class="fas fa-file-medical"></i> My Medical Reports</h2>

    <div class="table-container">
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Diagnosis</th>
                    <th>Treatment</th>
                    <th>Notes</th>
                    <th>Date</th>
                    <th>Referral Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['doctor_first'] . " " . $row['doctor_last']) ?></td>
                            <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                            <td><?= htmlspecialchars($row['treatment']) ?></td>
                            <td><?= htmlspecialchars($row['notes'] ?? "N/A") ?></td>
                            <td><?= date("d/m/Y", strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php
                                    $status = $row['referral_status'];
                                    $badgeClass = match ($status) {
                                        'Pending' => 'badge-pending',
                                        'Approved' => 'badge-approved',
                                        'Rejected' => 'badge-rejected',
                                        default => 'badge-not-referred',
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                                <?php if ($status !== 'Not Referred'): ?>
                                    <br><small><?= htmlspecialchars($row['referral_reason']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No Medical Reports Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>


<!-- AI Chat -->
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>



</body>
</html>
