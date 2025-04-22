<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$action = $_POST['action'];
$staff_id = $_SESSION['user_id'];

if ($action === 'add') {
    $name = $_POST['name'];
    $category = $_POST['category'] ?? '';
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'] ?? '';

    $stmt = $conn->prepare("INSERT INTO medical_supplies (name, category, quantity, unit) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $category, $quantity, $unit);
    $stmt->execute();

    $supply_id = $stmt->insert_id;

    $log = $conn->prepare("INSERT INTO supply_logs (supply_id, staff_id, action, quantity_change) VALUES (?, ?, 'Added', ?)");
    $log->bind_param("iii", $supply_id, $staff_id, $quantity);
    $log->execute();
}

if (($action === 'restock' || $action === 'use') && isset($_POST['supply_id'], $_POST['amount'])) {
    $supply_id = (int)$_POST['supply_id'];
    $amount = (int)$_POST['amount'];
    if ($amount <= 0) {
        header("Location: ../medicalsupplies.php");
        exit();
    }

    $adjustment = $action === 'use' ? -$amount : $amount;

    // Update stock
    $update = $conn->prepare("UPDATE medical_supplies SET quantity = quantity + ? WHERE supply_id = ?");
    $update->bind_param("ii", $adjustment, $supply_id);
    $update->execute();

    // Log it
    $log_action = $action === 'use' ? 'Used' : 'Restocked';
    $log = $conn->prepare("INSERT INTO supply_logs (supply_id, staff_id, action, quantity_change) VALUES (?, ?, ?, ?)");
    $log->bind_param("iisi", $supply_id, $staff_id, $log_action, $adjustment);
    $log->execute();
}

header("Location: ../medicalsupplies.php");
exit();
