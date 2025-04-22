<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: new_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$result = $conn->query("SELECT * FROM medical_supplies ORDER BY name ASC");

$logQuery = "SELECT sl.*, ms.name AS supply_name, u.first_name, u.last_name 
             FROM supply_logs sl
             JOIN medical_supplies ms ON sl.supply_id = ms.supply_id
             JOIN users u ON sl.staff_id = u.user_id
             ORDER BY sl.log_timestamp DESC
             LIMIT 20"; // Only latest 20 logs

$logs = $conn->query($logQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Medical Supplies Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       

        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <script src="scripts/bars.js" defer></script>
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/staffdash.css">

        <script src="../accessibility/accessibility.js" defer></script>
</head>

<body>

<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>

    <div class="content">

    <!-- Add New Supply Form -->
    <form action="php/medical_supplies.php" method="POST" class="row g-3 mb-5">
        <input type="hidden" name="action" value="add">
        <div class="col-md-4">
            <input type="text" name="name" placeholder="Supply Name" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="category" placeholder="Category (e.g. PPE, Medication)" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="number" name="quantity" placeholder="Qty" class="form-control" required>
        </div>
        <div class="col-md-2">
            <input type="text" name="unit" placeholder="Unit (e.g. boxes)" class="form-control">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Add</button>
        </div>
    </form>

    <!-- Supply Table -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= htmlspecialchars($row['unit']) ?></td>
                <td><?= $row['last_updated'] ?></td>
                <td>
                    <form action="php/medical_supplies.php" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="restock">
                        <input type="hidden" name="supply_id" value="<?= $row['supply_id'] ?>">
                        <input type="number" name="amount" class="form-control d-inline w-25" placeholder="+" required>
                        <button type="submit" class="btn btn-success btn-sm">Restock</button>
                    </form>
                    <form action="php/medical_supplies.php" method="POST" class="d-inline ms-2">
                        <input type="hidden" name="action" value="use">
                        <input type="hidden" name="supply_id" value="<?= $row['supply_id'] ?>">
                        <input type="number" name="amount" class="form-control d-inline w-25" placeholder="-" required>
                        <button type="submit" class="btn btn-danger btn-sm">Use</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="mt-5">
    <h4>Recent Activity Log</h4>
    <table class="table table-sm table-striped">
        <thead class="table-secondary">
            <tr>
                <th>Date/Time</th>
                <th>Staff Member</th>
                <th>Action</th>
                <th>Supply</th>
                <th>Qty Change</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs && $logs->num_rows > 0): ?>
                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $log['log_timestamp'] ?></td>
                        <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                        <td><?= $log['action'] ?></td>
                        <td><?= htmlspecialchars($log['supply_name']) ?></td>
                        <td class="<?= $log['quantity_change'] < 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $log['quantity_change'] ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No recent activity found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>



</body>
</html>
