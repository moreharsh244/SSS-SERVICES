<?php
if (session_status() === PHP_SESSION_NONE) {
	session_name('SSS_USER_SESS');
	session_start();
}
$email = $_SESSION['username'] ?? null;
if($email){
	include '../admin/conn.php';
	if(isset($con) && $con){
		// check which columns already exist
		$colQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME IN ('remember_token','remember_expiry')";
		$colRes = mysqli_query($con, $colQ);
		$cols = [];
		if($colRes){
			while($r = mysqli_fetch_assoc($colRes)) $cols[] = $r['COLUMN_NAME'];
		}
		if(!in_array('remember_token', $cols)){
			@mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN remember_token VARCHAR(255) NULL");
		}
		if(!in_array('remember_expiry', $cols)){
			@mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN remember_expiry DATETIME NULL");
		}

		// now safely clear tokens
		@mysqli_query($con, "UPDATE cust_reg SET remember_token=NULL, remember_expiry=NULL WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1");
	}
}
setcookie('remember','', time()-3600, '/', '', false, true);

// Clear all session variables
$_SESSION = array();

// Destroy the session with proper cookie handling
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header('Location: ../index.php');
exit;
?>