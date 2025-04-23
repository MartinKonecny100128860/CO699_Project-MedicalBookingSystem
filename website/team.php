<?php
// Start the session to access session variables
session_start();

// Establish connection to the database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

// Check if the connection failed
if ($conn->connect_error) {
    die("Database connection failed.");
}

// Define a reusable function to fetch users based on their role
function fetchUsersByRole($conn, $role) {
    // Prepare a SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ?");
    $stmt->bind_param("s", $role); // Bind the role parameter (string)
    $stmt->execute(); // Execute the query
    return $stmt->get_result(); // Return the result set
}

// Fetch all doctors from the users table
$doctors = fetchUsersByRole($conn, 'doctor');

// Fetch all staff members from the users table
$staff = fetchUsersByRole($conn, 'staff');

// Fetch all admins from the users table
$admins = fetchUsersByRole($conn, 'admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meet the Team - Zenith General Practice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap + Font Awesome -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-NXrxh4V4iguh8bchb7CHHQojnbMmQ/lCgOWftVGvchLz9m9CmVXaFELAJyoKr7gQ5Cq0WjV7AA/xPW5W/zB6wA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<!-- Working Font Awesome CDN without integrity check -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="Styles/homestyles.css">
    <link rel="stylesheet" href="Styles/button.css">
    <link rel="stylesheet" href="Styles/media.css">
    <link rel="stylesheet" href="Styles/hero.css">
    <link rel="stylesheet" href="Styles/footer.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f9fd;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1400px;
      margin: auto;
      padding: 40px 20px;
    }

    .team-section {
      padding: 60px 0;
    }

    .white-bg {
      background-color: #ffffff;
    }

    .grey-bg {
      background-color: #f4f9fd;
    }

    .team-title {
      font-size: 36px;
      color: #06799e;
      font-weight: bold;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 1px;
      margin-bottom: 40px;
      position: relative;
    }

    .team-title::after {
      content: "";
      display: block;
      width: 100px;
      height: 3px;
      background-color: #06799e;
      margin: 15px auto 0;
      border-radius: 50px;
    }

    .card-deck {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }

    .team-card {
      background: #ffffff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
      width: 260px;
      transition: transform 0.3s ease, border 0.3s ease;
      border: 3px solid transparent;
    }

    .team-card:hover {
      transform: translateY(-5px);
      border: 3px solid #28a745;
    }

    .team-card img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      object-position: center 0%;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }

    .card-body {
      padding: 20px;
      text-align: center;
    }

    .card-body h5 {
      color: #06799e;
      font-weight: 600;
      font-size: 20px;
      margin-bottom: 5px;
    }

    .card-body p {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 6px;
    }

    hr.section-divider {
      margin: 0;
      height: 5px;
      background: #06799e;
      border: none;
      width: 100%;
    }

    @media (max-width: 768px) {
      .card-deck {
        gap: 20px;
      }
    }

    .login {
        display: flex;
        align-items: center;
        margin-right: 20px;
      }

      .login-btn {
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s;
      }

      .login-btn:hover {
        background-color: white;
        color: #06799e;
        text-decoration: none;
      }
  </style>
</head>
<body>

<header>
    <nav class="navbar">
      <div class="logo"><a href="#"><img src="../assets/logos/logo-dark.png" alt="YourLogo"></a></div>
      <div class="navbar-links">
        <ul>
          <li><a href="Index.html">Home</a></li>
          <li><a href="team.php">Meet The Team</a></li>
          <li><a href="#">About</a></li>
        </ul>
      </div>
      <div class="login">
      <a href="../new_login.php" class="login-btn" title="Login">
        <i class="fas fa-user me-2"></i> Login
      </a>
    </div>


      <a class="togglebtn">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
      </a>
    </nav>
    <script>
      const toggleBtn = document.querySelector('.togglebtn');
      const navbarLinks = document.querySelector('.navbar-links');
  
      toggleBtn.addEventListener('click', () => {
        navbarLinks.classList.toggle('active');
      });
    </script>
  </header>

  <!-- Doctors -->
  <hr class="section-divider">
  <div class="team-section white-bg">
    <div class="container">
      <h2 class="team-title"><i class="fas fa-user-md"></i> Doctors</h2>
      <div class="card-deck">
        <?php while ($doc = $doctors->fetch_assoc()): ?>
        <div class="team-card">
          <img src="<?= htmlspecialchars('../' . $doc['profile_picture']) ?>" alt="Doctor">
          <div class="card-body">
            <h5><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></h5>
            <p>Doctor</p>
            <p><?= htmlspecialchars($doc['description'] ?? 'Doctor at Zenith General Practice') ?></p>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- Staff -->
  <div class="team-section grey-bg">
    <div class="container">
      <h2 class="team-title"><i class="fas fa-user-nurse"></i> Staff</h2>
      <div class="card-deck">
        <?php while ($st = $staff->fetch_assoc()): ?>
        <div class="team-card">
          <img src="<?= htmlspecialchars('../' . $st['profile_picture']) ?>" alt="Staff">
          <div class="card-body">
            <h5><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></h5>
            <p>Staff Member</p>
            <p><?= htmlspecialchars($st['description'] ?? 'Team member supporting our clinic operations') ?></p>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- Admins -->
  <div class="team-section white-bg">
    <div class="container">
      <h2 class="team-title"><i class="fas fa-user-shield"></i> Admins</h2>
      <div class="card-deck">
        <?php while ($ad = $admins->fetch_assoc()): ?>
        <div class="team-card">
          <img src="<?= htmlspecialchars('../' . $ad['profile_picture']) ?>" alt="Admin">
          <div class="card-body">
            <h5><?= htmlspecialchars($ad['first_name'] . ' ' . $ad['last_name']) ?></h5>
            <p>IT Admin</p>
            <p><?= htmlspecialchars($ad['description'] ?? 'Ensuring smooth system management') ?></p>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <hr class="section-divider">

  <footer class="footer">
  <div class="footer-container">
    <!-- Column 1: Clinic Info -->
    <div class="footer-column">
      <img src="../assets/logos/logo-light.png" alt="Zenith Logo" class="footer-logo" />
      <br>
      <h4>Zenith General Practice</h4>
      <p class="clinic-desc">Providing compassionate, high-quality healthcare to individuals and families across the region.</p>
    </div>

<!-- Column 2: Contact Info -->
<div class="footer-column">
  <h4>Contact Us</h4>
  <p><i class="fas fa-map-marker-alt"></i> 21 Oxford Street, Manchester M1 5QA</p>
  <p><i class="fas fa-envelope"></i> info@zenithclinic.co.uk</p>
  <p><i class="fas fa-phone"></i> 0161 555 7890</p>
</div>


    <div class="footer-column">
  <h4>Connect With Us</h4>
  <div class="social-icons">
    <a href="#" class="social-circle" title="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="#" class="social-circle" title="Instagram"><i class="fab fa-instagram"></i></a>
    <a href="#" class="social-circle" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
  </div>

  <div class="opening-hours">
    <p><i class="fas fa-clock"></i> <strong>Mon – Fri:</strong> 9:00 AM – 6:00 PM</p>
    <p><i class="fas fa-clock"></i> <strong>Saturday:</strong> Closed</p>
    <p><i class="fas fa-clock"></i> <strong>Sunday:</strong> Closed</p>
  </div>
</div>




    <!-- Column 4: Newsletter -->
    <div class="footer-column newsletter">
      <h4>Subscribe</h4>
      <p>Join our mailing list for the latest health tips & news.</p>
      <form class="newsletter-form" action="/subscribe" method="POST">
        <input type="email" name="email" placeholder="Your email" required />
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="footer-bottom">
    <p>&copy; 2025 Zenith General Practice | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p>
  </div>
</footer>

</body>
</html>
