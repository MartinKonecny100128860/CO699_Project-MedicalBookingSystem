<?php
$entered_password = "Admin1234"; // This is the password you should be using
$stored_hash = '$2y$10$I9GDNEG4HaEM757ba86EKu8K8s7fdIurqQJRC.K5FcDC1bm3UJcWO'; // The stored password hash

if (password_verify($entered_password, $stored_hash)) {
    echo "✅ Password matches! You can log in.";
} else {
    echo "❌ Invalid password.";
}
?>
