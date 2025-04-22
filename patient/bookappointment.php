<?php
session_start();

// Redirect if not logged in or not a patient
// Shared booking logic for both patients and staff
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$isStaffBooking = ($_SESSION['role'] === 'staff' && isset($_GET['user_id']));
$patient_id = $_SESSION['user_id'];

// If staff is booking for a patient
if ($isStaffBooking) {
    $patient_id = intval($_GET['user_id']);
    // Optional: fetch patient name to show who you're booking for
    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
    $conn->set_charset("utf8mb4");
    $patientResult = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $patient_id AND role = 'patient'");
    $patientData = $patientResult->fetch_assoc();
    $bookingFor = $patientData ? $patientData['first_name'] . ' ' . $patientData['last_name'] : 'Unknown Patient';
}


// Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = null;
$available_slots = [];
$message = "";

$selected_specialty = $_GET['specialty'] ?? '';

$selected_specialty = $_GET['specialty'] ?? '';

// Get all specialties for dropdown
$specialtyListResult = $conn->query("SELECT DISTINCT specialty FROM doctor_specialties ORDER BY specialty ASC");
$all_specialties = [];
while ($row = $specialtyListResult->fetch_assoc()) {
    $all_specialties[] = $row['specialty'];
}

// Fetch doctors based on specialty filter
$doctors = [];
if (!empty($selected_specialty)) {
    $stmt = $conn->prepare("
        SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.profile_picture, u.description 
        FROM users u
        JOIN doctor_specialties ds ON u.user_id = ds.doctor_id
        WHERE u.role = 'doctor' AND ds.specialty = ?
    ");
    $stmt->bind_param("s", $selected_specialty);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT user_id, first_name, last_name, profile_picture, description FROM users WHERE role = 'doctor'");
}
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}


// Check if doctor_id is set from the previous form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];

    // Fetch the doctor's schedule based on doctor_id
    $scheduleQuery = "SELECT day_of_week, start_time, end_time FROM doctor_schedule WHERE user_id = $doctor_id";
    $scheduleResult = $conn->query($scheduleQuery);
    $available_days = [];

    // Loop through the result and store the available days
    while ($schedule = $scheduleResult->fetch_assoc()) {
        $day_of_week = $schedule['day_of_week']; // e.g., Monday, Tuesday, etc.
        $available_days[] = $day_of_week; // Store the available day
    }
}

// Handle the form submission for selecting the appointment day and viewing available time slots
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_day'])) {
    $appointment_day = $_POST['appointment_day'];  // Get the selected appointment day
    $doctor_id = $_POST['doctor_id'];

    // Fetch the schedule for the selected day
    $scheduleQuery = "SELECT start_time, end_time FROM doctor_schedule WHERE user_id = $doctor_id AND day_of_week = '$appointment_day'";
    $scheduleResult = $conn->query($scheduleQuery);
    $available_slots = [];

    while ($schedule = $scheduleResult->fetch_assoc()) {
        $start_time = $schedule['start_time'];
        $end_time = $schedule['end_time'];

        // Generate available slots (30 minutes)
        $current_time = strtotime($start_time);
        $end_time = strtotime($end_time);

        while ($current_time < $end_time) {
            $slot_start = date('H:i', $current_time);
            $current_time += 1800; // 30 minutes
            $slot_end = date('H:i', $current_time);
        
            // Format datetime for lookup
            $formattedDateTime = new DateTime("next $appointment_day " . $slot_start);
            $slot_time = $formattedDateTime->format('H:i:s');
        
            // Check if slot is already booked
            $checkSlotQuery = $conn->prepare("
                SELECT 1 FROM appointments 
                WHERE doctor_id = ? 
                  AND appointment_day = ? 
                  AND appointment_time = ?
            ");
            $checkSlotQuery->bind_param("iss", $doctor_id, $appointment_day, $slot_time);
            $checkSlotQuery->execute();
            $checkSlotQuery->store_result();
            $isBooked = $checkSlotQuery->num_rows > 0;
            $checkSlotQuery->close();
        
            $available_slots[] = [
                'start' => $slot_start,
                'end' => $slot_end,
                'booked' => $isBooked
            ];
        }
        
    }
}

// Handle the appointment booking when the time is selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $appointment_time = $_POST['appointment_time']; // e.g., '10:30'
    $appointment_day = $_POST['appointment_day'];
    $doctor_id = $_POST['doctor_id'];

    $today = new DateTime();
    $targetDay = new DateTime("next $appointment_day");
    $targetDateTime = $targetDay->format('Y-m-d') . ' ' . $appointment_time;
    $formatted_datetime = new DateTime($targetDateTime);

    // Check if slot is already booked
    $appointmentQuery = "SELECT * FROM appointments WHERE doctor_id = $doctor_id AND appointment_day = '$appointment_day' AND appointment_time = '" . $formatted_datetime->format('H:i:s') . "'";
    $appointmentResult = $conn->query($appointmentQuery);

    if ($appointmentResult->num_rows > 0) {
        $message = "This slot is already booked. Please choose a different time.";
    } else {
        // Book the appointment
        $bookQuery = "INSERT INTO appointments (doctor_id, patient_id, appointment_time, appointment_day, status) 
                      VALUES ($doctor_id, $patient_id, '" . $formatted_datetime->format('H:i:s') . "', '$appointment_day', 'scheduled')";
        if ($conn->query($bookQuery) === TRUE) {
            $message = "Appointment booked successfully!";

            // ✅ Insert notification inside this block
            $notifyQuery = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifMessage = "New appointment from Patient ID $patient_id on $appointment_day at " . $formatted_datetime->format('H:i');
            $notifyQuery->bind_param("is", $doctor_id, $notifMessage);
            $notifyQuery->execute();
        } else {
            $message = "Error booking appointment: " . $conn->error;
        }
    }
}



$conn->close();

// Empathetic and thorough, Dr. Aisha supports women’s health and joint care with a gentle, informed approach tailored to complex, lifelong needs.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script>
    // Apply sidebar state BEFORE page renders
    (function () {
        const state = localStorage.getItem("sidebarState");
        if (state === "closed") {
        document.documentElement.classList.add("sidebar-closed");
        }
    })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <!-- External links -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/patientdash.css">
        <link rel="stylesheet" href="styles/bars.css">
        <link rel="stylesheet" href="styles/cards.css">

        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">

        <script src="../accessibility/accessibility.js" defer></script>
        <script src="scripts/bars.js" defer></script>


</head>
<body>
  <?php
    $pageTitle = "Book an Appointment";
    include 'php/bars.php'; // contains header and sidebar
  ?>

  <div class="content">
    <div class="container">
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="get" class="mb-4">
  <div class="card p-4 shadow-sm border-0" style="background-color: #f8f9fa;">
    <div class="row align-items-center">
      <div class="col-md-6">
        <label for="specialty" class="form-label fw-semibold text-secondary mb-2">
          <i class="fas fa-filter me-2"></i>Filter by Specialty
        </label>
        <select name="specialty" id="specialty" class="form-select form-select-lg" onchange="this.form.submit()">
          <option value="">-- Show All Specialties --</option>
          <?php foreach ($all_specialties as $specialty): ?>
            <option value="<?= htmlspecialchars($specialty) ?>" <?= ($selected_specialty === $specialty) ? 'selected' : '' ?>>
              <?= htmlspecialchars($specialty) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</form>


    <section class="image-gallery">
  <h2>Select a Doctor</h2>
  <div class="gallery-container">
    <?php foreach ($doctors as $doctor): ?>
      <div class="card-box">
        <img src="<?= htmlspecialchars('../' . ($doctor['profile_picture'] ?? 'assets/defaults/doctor_default.png')) ?>" 
             alt="<?= $doctor['first_name'] ?> <?= $doctor['last_name'] ?>">
        <div class="card-info">
          <h3><?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></h3>
          <p><?= htmlspecialchars($doctor['description'] ?? 'Consultation Available') ?></p>
          </div>
            <form method="post">
                <input type="hidden" name="doctor_id" value="<?= $doctor['user_id'] ?>">
                <?php if (!empty($selected_specialty)): ?>
                    <input type="hidden" name="selected_specialty" value="<?= htmlspecialchars($selected_specialty) ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-success">Select Doctor</button>
            </form>

      </div>
    <?php endforeach; ?>
  </div>
</section>


    <!-- Step 2: Select Appointment Day -->
    <?php if (isset($available_days) && count($available_days) > 0): ?>
        <div class="select-container">
            <h4>Select Appointment Day</h4>
            <form method="post">
                <select name="appointment_day" required>
                    <?php foreach ($available_days as $day): ?>
                        <option value="<?= $day ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit" class="btn btn-primary">View Available Time Slots</button>
                <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
            </form>
        </div>
    <?php endif; ?>

    <!-- Step 3: Display Available Time Slots -->
    <?php if (isset($available_slots) && count($available_slots) > 0): ?>
        <div class="slot-container">
            <h4>Available Slots for <?= $appointment_day ?></h4>
            <form method="post">
                <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
                <input type="hidden" name="appointment_day" value="<?= $appointment_day ?>">
                <table class="table table-bordered slot-table">
                    <thead>
                        <tr>
                            <th>Available Time Slots</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_slots as $slot): ?>
                            <tr>
                                <td>
                                    <label>
                                    <?php if ($slot['booked']): ?>
    <label class="text-muted" style="text-decoration: line-through;">
        <input type="radio" disabled>
        <?= $slot['start'] ?> - <?= $slot['end'] ?> (Booked)
    </label>
<?php else: ?>
    <label>
        <input type="radio" name="appointment_time" value="<?= $slot['start'] ?>" required>
        <?= $slot['start'] ?> - <?= $slot['end'] ?>
    </label>
<?php endif; ?>

                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
            </form>
        </div>
    <?php elseif (isset($available_days) && count($available_days) == 0): ?>
        <p>No available slots for this doctor on the selected day.</p>
    <?php endif; ?>
    <?php if ($isStaffBooking && isset($bookingFor)): ?>
    <div class="alert alert-info">
        <strong>Staff Booking:</strong> You are booking this appointment for <strong><?= htmlspecialchars($bookingFor) ?></strong>.
    </div>
<?php endif; ?>

</div>
</div>

<?php include '../accessibility/accessibility.php'; ?>


<!-- ✅ AI Chat -->
<div id="chat-placeholder"></div>
<script src="../aichat/chat.js"></script>


</body>
</html>