<?php
session_start(); // Resume existing session

// 1. Clear all session variables
$_SESSION = array();

// 2. If a session cookie exists, destroy it by setting time in the past
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy session storage on server
session_destroy();

// 4. Ensure session write is closed
session_write_close();

// 5. Redirect to login
header("Location: ../instructor-login.php");
exit();
?>
