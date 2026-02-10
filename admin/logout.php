<?php
if (session_status() === PHP_SESSION_NONE) {
	session_name('SSS_ADMIN_SESS');
	session_start();
}
unset($_SESSION['role']);
session_destroy();
header('location:login.php');
exit;
?>