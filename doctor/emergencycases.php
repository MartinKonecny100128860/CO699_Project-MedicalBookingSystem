<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    header("Location: ../new_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$doctorId = $_SESSION['user_id'];
$emergencyCases = [];

$sql = "SELECT * FROM emergency_cases ORDER BY time_reported DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $viewedBy = json_decode($row['viewed_by'], true) ?? [];

    // If not yet marked as viewed, mark this case as viewed for the current doctor
    if (!in_array($doctorId, $viewedBy)) {
        $viewedBy[] = $doctorId;
        $newViewedJson = $conn->real_escape_string(json_encode($viewedBy));
        $conn->query("UPDATE emergency_cases SET viewed_by = '$newViewedJson' WHERE id = " . $row['id']);
    }

    $emergencyCases[] = $row;
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Cases</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">

    <script src="../accessibility/accessibility.js" defer></script>


    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="../styles/global.css">

    <script src="scripts/bars.js" defer></script>

    <style>
        .container {
            max-width: 1000px;
            margin: 50px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #00819d;
        }

        .emergency-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            transition: 0.3s ease-in-out;
        }

        .emergency-card:hover {
            transform: scale(1.01);
        }

        .emergency-card .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .severity-critical { background-color: #e53935; color: #fff; }
        .severity-severe { background-color: #fb8c00; color: #fff; }
        .severity-moderate { background-color: #fbc02d; color: #000; }
        .severity-mild { background-color: #43a047; color: #fff; }

        .card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .card-header .location {
            color: #666;
            font-size: 0.9rem;
        }

        .emergency-card h5 {
            margin: 5px 0;
        }

        .assigned {
            margin-top: 10px;
        }

        .btn-take {
            background-color: #00819d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: 0.2s ease;
        }

        .btn-take:hover {
            background-color: #006d87;
        }

        .assigned-doctor {
            font-weight: bold;
        }

        .assigned-doctor.none {
            color: #c0392b;
        }

        .assigned-doctor.name {
            color: #2e7d32;
        }
    </style>
</head>
<body>

<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>


<div class="content">
<div class="container">
    <h2 div class="h2-style">Emergency Cases</h2>

    <?php foreach ($emergencyCases as $case): ?>
        <div class="emergency-card">
            <div class="card-header">
                <span class="badge 
                    severity-<?= strtolower($case['severity']) ?>">
                    <?= strtoupper($case['severity']) ?>
                </span>
                <span class="location">
                    <i class="fas fa-map-marker-alt"></i> <?= $case['location'] ?>
                </span>
            </div>

            <h5><?= htmlspecialchars($case['patient']) ?> 
                <small>(<?= $case['age'] ?>, <?= $case['sex'] ?>)</small>
            </h5>
            <p><?= htmlspecialchars($case['issue']) ?></p>

            <div class="assigned">
                <strong>Assigned Doctor:</strong> 
                <?php if (!empty($case['assigned_doctor'])): ?>
                    <span class="assigned-doctor name"><?= htmlspecialchars($case['assigned_doctor']) ?></span>
                <?php else: ?>
                    <span class="assigned-doctor none">None</span>
                <?php endif; ?>
            </div>

            <?php if (!$case['assigned_doctor']): ?>
                <div class="mt-3">
                <button class="btn btn-take" onclick="takeCase(<?= $case['id'] ?>, this)">Take Case</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</div>

<script>
function takeCase(caseId, button) {
    if (!confirm("Are you sure you want to take this case?")) return;

    fetch('php/handle_emergency_case.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            case_id: caseId,
            action: 'take_case'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            button.outerHTML = `<span class="text-success fw-bold">You have taken this case</span>`;
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert("Something went wrong.");
        console.error(err);
    });
}
</script>

<?php include '../accessibility/accessibility.php'; ?>

</body>
</html>
