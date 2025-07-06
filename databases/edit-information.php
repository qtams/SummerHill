<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['confirmPasswords'];

    include "connection.php";

    // Fetch current password from DB
    $sql_check = "SELECT password FROM admin_user WHERE user_id = ?";
    $stmt = $con->prepare($sql_check);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_password);
    $stmt->fetch();
    $stmt->close();

    $updated = false;
    $message = '';

    // Only update if password has changed
    if ($new_password !== $current_password) {
        $sql_update = "UPDATE admin_user SET password = ? WHERE user_id = ?";
        $stmt = $con->prepare($sql_update);
        $stmt->bind_param("si", $new_password, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $updated = true;
            $message = 'Password successfully updated.';
        }
        $stmt->close();
    }

    $con->close();

    if ($updated) {
        echo json_encode([
            'status' => 'success',
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'status' => 'info',
            'message' => 'No changes detected - password remains the same.'
        ]);
    }
}
?>