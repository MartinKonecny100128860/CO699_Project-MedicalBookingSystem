<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_SESSION['user_id'];

// Fetch patient's test records
$sql = "SELECT test_type, test_summary, test_diagnosis, file_path FROM patient_tests WHERE patient_id = ?";
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
    <title>View Tests & Results</title>

    <!-- External Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="styles/patientdash.css">
    <link rel="stylesheet" href="../accessibility/accessibility.css">
    <link rel="stylesheet" href="../accessibility/highcontrast.css">
    <link rel="stylesheet" href="styles/bars.css">
    <link rel="stylesheet" href="styles/cards.css">
    <link rel="stylesheet" href="../styles/global.css">

    <script src="../accessibility/accessibility.js" defer></script>
    <script src="scripts/bars.js" defer></script>

    <style>
        body {
            background-color: #f4f7fc;
            color: #212529;
        }

        h2.text-center {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 35px;
            color: #06799e;
        }

        .test-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: 0.3s ease;
        }

        .test-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.1);
        }

        .test-header {
            font-size: 1.3rem;
            font-weight: bold;
            color: #06799e;
            border-bottom: 2px solid #06799e;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .test-info p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .test-diagnosis {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .test-image {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .test-image img {
            max-width: 100%;
            max-height: 360px;
            border-radius: 10px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .test-image img:hover {
            transform: scale(1.02);
        }

        .no-tests {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        /* Image Modal */
        .modal-img {
            display: none;
            position: fixed;
            z-index: 1050;
            padding-top: 60px;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.85);
        }

        .modal-img-content {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 80vh;
            border-radius: 8px;
        }

        .modal-img-close {
            position: absolute;
            top: 30px;
            right: 40px;
            color: white;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 20px;
            }
        }

        .container {
            max-width: 100%;
            margin: 100px auto 30px auto;
            padding: 30px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php
$pageTitle = "View Test Results";
include 'php/bars.php'; // header + sidebar
?>

<div class="content">
<div class="container">
    <h2 class="h2-style">My Test Results</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="test-card">
                <div class="test-header"><?= htmlspecialchars($row['test_type']) ?></div>
                <div class="test-info">
                    <p><strong>Summary:</strong> <?= htmlspecialchars($row['test_summary']) ?></p>
                    <p class="test-diagnosis"><strong>Diagnosis:</strong> <?= htmlspecialchars($row['test_diagnosis']) ?></p>
                </div>
                <?php if (!empty($row['file_path'])): ?>
                    <div class="test-image">
                        <img src="<?= "/MedicalBooking/" . htmlspecialchars($row['file_path']) ?>" alt="Test Image" onclick="openModal(this)">
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-tests">
            <p>No test results found.</p>
        </div>
    <?php endif; ?>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>


<!-- ✅ AI Chat -->
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>


<!-- MODAL FOR IMAGE ZOOM -->
<div id="imgModal" class="modal-img" onclick="closeModal()">
    <span class="modal-img-close" onclick="closeModal()">&times;</span>
    <img class="modal-img-content" id="imgPreview">
</div>

<?php include 'php/accessibility.php'; ?>

<script>
    function openModal(img) {
        const modal = document.getElementById("imgModal");
        const modalImg = document.getElementById("imgPreview");
        modal.style.display = "block";
        modalImg.src = img.src;
    }

    function closeModal() {
        document.getElementById("imgModal").style.display = "none";
    }
</script>

</body>
</html>
