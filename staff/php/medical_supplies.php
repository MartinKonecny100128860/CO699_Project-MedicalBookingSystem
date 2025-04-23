<?php
// Start the session
session_start();

// Check if the user is logged in and has a staff role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../new_login.php");
    exit(); // Stop further execution if not authorised
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Ensure proper character encoding

// Get the action (add, restock, use) and staff ID from the session
$action = $_POST['action'];
$staff_id = $_SESSION['user_id'];

// ---------- ADD NEW SUPPLY ----------
if ($action === 'add') {
    // Get supply details from the form
    $name = $_POST['name'];
    $category = $_POST['category'] ?? '';
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'] ?? '';

    // Insert new supply into the medical_supplies table
    $stmt = $conn->prepare("INSERT INTO medical_supplies (name, category, quantity, unit) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $category, $quantity, $unit);
    $stmt->execute();

    // Get the ID of the newly inserted supply
    $supply_id = $stmt->insert_id;

    // Log the addition action
    $log = $conn->prepare("INSERT INTO supply_logs (supply_id, staff_id, action, quantity_change) VALUES (?, ?, 'Added', ?)");
    $log->bind_param("iii", $supply_id, $staff_id, $quantity);
    $log->execute();
}

// ---------- RESTOCK OR USE EXISTING SUPPLY ----------
if (($action === 'restock' || $action === 'use') && isset($_POST['supply_id'], $_POST['amount'])) {
    $supply_id = (int)$_POST['supply_id'];
    $amount = (int)$_POST['amount'];

    // Prevent invalid or negative inputs
    if ($amount <= 0) {
        header("Location: ../medicalsupplies.php");
        exit();
    }

    // Determine whether to add or subtract the amount
    $adjustment = $action === 'use' ? -$amount : $amount;

    // Update the quantity in the medical_supplies table
    $update = $conn->prepare("UPDATE medical_supplies SET quantity = quantity + ? WHERE supply_id = ?");
    $update->bind_param("ii", $adjustment, $supply_id);
    $update->execute();

    // Log the restock or usage action
    $log_action = $action === 'use' ? 'Used' : 'Restocked';
    $log = $conn->prepare("INSERT INTO supply_logs (supply_id, staff_id, action, quantity_change) VALUES (?, ?, ?, ?)");
    $log->bind_param("iisi", $supply_id, $staff_id, $log_action, $adjustment);
    $log->execute();
}

// Redirect back to the supplies management page after completing the action
header("Location: ../medicalsupplies.php");
exit();
