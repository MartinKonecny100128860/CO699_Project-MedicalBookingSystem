<?php
    require(__DIR__ . '/../../libraries/fpdf/fpdf.php'); // Ensure correct path
    session_start();

    // Redirect to login page if not logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Database connection setup (Ensure correct DB name and charset)
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "medicalbookingsystem"; // Corrected database name

    // Create database connection
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    $conn->set_charset("utf8mb4");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get admin details
    $admin_id = $_SESSION['user_id'] ?? 'N/A';
    $admin_name = 'N/A';

    // Fetch full name (first_name + last_name)
    if ($admin_id !== 'N/A') {
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);
        if ($stmt->fetch()) {
            $admin_name = trim("$first_name $last_name");
        }
        $stmt->close();
    }

    $conn->close();

    // Capture report data
    $report_title = $_POST['report_title'] ?? 'Untitled Report';
    $report_content = $_POST['report_content'] ?? 'No content provided.';

    // Get current date & time
    $current_datetime = date("d/m/Y H:i");

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Insert Logo
    $logo_path = __DIR__ . '/../../libraries/fpdf/assets/zenith.png';
    if (file_exists($logo_path)) {
        $pageWidth = $pdf->GetPageWidth();
        $logoWidth = 80;
        $xPosition = ($pageWidth - $logoWidth) / 2;

        $pdf->Image($logo_path, $xPosition, 10, $logoWidth);
    }

    // Move Timestamp Below Logo & Above Admin Info (Bigger & Bolder)
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetY(50);
    $pdf->Cell(0, 10, "Date: $current_datetime", 0, 1, 'C');
    $pdf->Ln(10);

    // Add Admin Info (Left-aligned, Fixing Spacing)
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, "Admin's ID:", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, $admin_id, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, "Admin's Full Name:", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, $admin_name, 0, 1, 'L');
    $pdf->Ln(10);

    // Report Title (Bold & Centered)
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $report_title, 0, 1, 'C');
    $pdf->Ln(5);

    // Report Content (Justified)
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, $report_content, 0, 'J');

    // Output PDF
    $pdf->Output('D', 'Administrator_Report.pdf');
    exit();
?>
