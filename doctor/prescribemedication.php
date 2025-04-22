<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all patients
$result = $conn->query("SELECT user_id, first_name, last_name, date_of_birth, email FROM users WHERE role = 'patient'");
$patients = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescribe Medication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/doctordash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <link rel="stylesheet" href="styles/bars.css">
        <script src="scripts/bars.js" defer></script>

        <script src="../accessibility/accessibility.js" defer></script>

    <style>

        .search-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 900px;
            margin: auto;
        }

        .search-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #00819d;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-control {
            border-radius: 8px;
        }

        .patient-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }

        .patient-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }

        .btn-prescribe {
            background-color: #00819d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            transition: 0.3s ease;
        }

        .btn-prescribe:hover {
            background-color: #006d87;
        }

        .no-results {
            text-align: center;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>
<body>

<br>
<br>

<div class="content">
<div class="search-container">
    <div class="search-title">Prescribe Medication</div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" id="searchName" class="form-control" placeholder="Search by name">
        </div>
        <div class="col-md-6">
            <input type="date" id="searchDOB" class="form-control" placeholder="Search by date of birth">
        </div>
    </div>

    <div id="patientsContainer">
        <?php if (count($patients) > 0): ?>
            <?php foreach ($patients as $patient): ?>
                <div class="patient-card" 
                    data-name="<?= strtolower($patient['first_name'] . ' ' . $patient['last_name']) ?>" 
                    data-dob="<?= $patient['date_of_birth'] ?>">
                    <h5><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h5>
                    <p><strong>DOB:</strong> <?= htmlspecialchars($patient['date_of_birth']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
                    <a href="createprescription.php?patient_id=<?= $patient['user_id'] ?>" class="btn btn-prescribe">
                        <i class="fas fa-notes-medical"></i> Prescribe
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">No patients found in the system.</div>
        <?php endif; ?>
    </div>
</div>
</div>


<script>
    const searchName = document.getElementById('searchName');
    const searchDOB = document.getElementById('searchDOB');
    const cards = document.querySelectorAll('.patient-card');

    function filterPatients() {
        const nameVal = searchName.value.toLowerCase();
        const dobVal = searchDOB.value;

        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name;
            const dob = card.dataset.dob;

            const matchName = name.includes(nameVal);
            const matchDOB = dobVal === '' || dob === dobVal;

            if (matchName && matchDOB) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            if (!document.getElementById('noResults')) {
                const div = document.createElement('div');
                div.id = 'noResults';
                div.className = 'no-results';
                div.textContent = 'No matching patients found.';
                document.getElementById('patientsContainer').appendChild(div);
            }
        } else {
            const noRes = document.getElementById('noResults');
            if (noRes) noRes.remove();
        }
    }

    searchName.addEventListener('input', filterPatients);
    searchDOB.addEventListener('input', filterPatients);
</script>

<?php include '../accessibility/accessibility.php'; ?>

</body>
</html>
