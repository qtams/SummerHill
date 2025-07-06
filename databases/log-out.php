<?php
session_start(); // Start the session to access session variables

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Optional: Delete session cookie for extra security
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login or homepage
header("Location: ../index.php");
exit();
?>
