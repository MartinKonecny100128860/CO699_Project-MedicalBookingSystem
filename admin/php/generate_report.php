<?php
require(__DIR__ . '/../../libraries/fpdf/fpdf.php'); // Ensure correct path
session_start();

// ✅ Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Get admin details
$admin_id = $_SESSION['user_id'] ?? 'N/A';
$admin_name = 'N/A';

// Fetch full name (first_name + last_name)
if ($admin_id !== 'N/A') {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);
    if ($stmt->fetch()) {
        $admin_name = trim("$first_name $last_name"); // Concatenate first_name and last_name
    }
    $stmt->close();
}

$conn->close();

// ✅ Capture report data
$report_title = $_POST['report_title'] ?? 'Untitled Report';
$report_content = $_POST['report_content'] ?? 'No content provided.';

// ✅ Get current date & time
$current_datetime = date("d/m/Y H:i");

// ✅ Initialize PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// 🟢 Insert Logo (Centered & Bigger)
$logo_path = __DIR__ . '/../../libraries/fpdf/assets/zenith.png'; // Ensure correct path
if (file_exists($logo_path)) {
    $pageWidth = $pdf->GetPageWidth();  // Get the width of the page
    $logoWidth = 80;  // Increase logo size
    $xPosition = ($pageWidth - $logoWidth) / 2; // Center logo

    $pdf->Image($logo_path, $xPosition, 10, $logoWidth);
}

// 🟢 Move Timestamp Below Logo & Above Admin Info (Bigger & Bolder)
$pdf->SetFont('Arial', 'B', 12); // **Make it BOLD**
$pdf->SetY(50); // Move timestamp down below the logo
$pdf->Cell(0, 10, "Date: $current_datetime", 0, 1, 'C'); // **Center-aligned**
$pdf->Ln(10); // Space before admin details

// 🟢 Add Admin Info (Left-aligned, Fixing Spacing)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, "Admin's ID:", 0, 0, 'L'); // Label
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, $admin_id, 0, 1, 'L'); // ✅ Ensure only one space after label

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, "Admin's Full Name:", 0, 0, 'L'); // Label
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, $admin_name, 0, 1, 'L'); // ✅ Ensure only one space after label
$pdf->Ln(10); // Space before content

// 🟢 Report Title (Bold & Centered)
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $report_title, 0, 1, 'C');
$pdf->Ln(5);

// 🟢 Report Content (Justified)
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $report_content, 0, 'J'); // ✅ Justified text

// ✅ Output PDF
$pdf->Output('D', 'Administrator_Report.pdf');
exit();
?>
