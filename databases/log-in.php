<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include "connection.php";

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit;
    }

    $sql = "SELECT user_id, username, password FROM admin_user WHERE username = ?";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // If using plain-text passwords:
        if ($password === trim($user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'Admin';

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'role' => 'Admin',
                
            ]);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    $stmt->close();
    $con->close();
}
?>
