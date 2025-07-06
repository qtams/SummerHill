<?php
$username = "Super Admin";
$password = "@CCSSADMIN";

include "connection.php";

// Use prepared statements to prevent SQL injection
$sql = "INSERT INTO admin_user (username, password) VALUES (?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $username, $password); // Bind values (s = string)

if ($stmt->execute()) {
    echo 'ADDED';
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$con->close();
?>
