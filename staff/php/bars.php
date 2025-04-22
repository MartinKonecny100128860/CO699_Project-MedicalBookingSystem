<div class="header">
  <div class="header-left">
    <button id="toggleSidebarBtn" class="toggle-sidebar-btn">☰</button>
    <img src="../assets/logos/logo-dark.png" alt="Logo" class="logo-img" />
    </div>

  <div class="header-right">
    <a href="/MedicalBooking/logout.php" class="power-icon-box" title="Logout">
      <i class="material-icons">&#xe8ac;</i>
    </a>
  </div>
</div>



        <!-- Side Nav Bar HTML -->
        <div class="sidebar">
            <div class="profile-section">
                <div class="profile-pic-container">
                    <div class="profile-pic-wrapper">
                        <img src="<?= htmlspecialchars('../' . ($_SESSION['profile_picture'] ?? 'assets/defaults/user_default.png')) ?>" 
                        alt="Profile Picture" class="profile-pic">
                    </div>
                    <p class="welcome-text">
                        Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Doctor') ?><br>
                        <small>ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></small>
                    </p>
                </div>
            </div>

            <!-- Scrollable Container Inside Nav Bar -->
            <div class="scroll-container">
                <a href="/MedicalBooking/staff/staffdash.php" data-tooltip="Dashboard">
                    <i class="material-icons">dashboard</i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="/MedicalBooking/staff/bookforpatient.php" data-tooltip="Book Appointment">
                    <i class="material-icons">event</i>
                    <span class="nav-text">Book Appointment</span>
                </a>
                <a href="/MedicalBooking/staff/manageappointments.php" data-tooltip="Manage Appointments">
                    <i class="material-icons">today</i>
                    <span class="nav-text">Manage Appointments</span>
                </a>
                <a href="/MedicalBooking/staff/registerpatient.php" data-tooltip="Medical Reports">
                    <i class="material-icons">assignment</i>
                    <span class="nav-text">Register Patient</span>
                </a>
                <a href="/MedicalBooking/staff/viewpatients.php" data-tooltip="Prescriptions">
                    <i class="material-icons">local_pharmacy</i>
                    <span class="nav-text">View Patients</span>
                </a>
                <a href="/MedicalBooking/staff/medicalsupplies.php" data-tooltip="Prescriptions">
                    <i class="material-icons">local_pharmacy</i>
                    <span class="nav-text">Medical Supplies</span>
                </a>
            </div>
        </div>
