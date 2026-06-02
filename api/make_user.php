<?php
// 1. Generate the secure hash for 'admin123'
$plain_password = 'admin123';
$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

// 2. Output the SQL statement you can copy-paste
echo "<h3>Copy and run this SQL query in your Neon Console:</h3>";
echo "<pre>";
echo "INSERT INTO users (username, password, role, status, email, full_name)\n";
echo "VALUES ('dr_testi', '$hashed_password', 'admin', 'Active', 'testi@example.com', 'Dr. Testi');";
echo "</pre>";
?>