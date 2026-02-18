<?php
if (session_status() === PHP_SESSION_NONE) {
	session_name('SSS_ADMIN_SESS');
	session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to home page
header('Location: ../index.php');
exit;
?>