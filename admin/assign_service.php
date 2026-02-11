<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
if (!isset($_SESSION['is_login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('location:login.php');
    exit;
}
include('conn.php');
include('../delivery/helpers.php');
ensure_delivery_tables($con);
ensure_service_requests_table($con);

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:service_requests.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$agent = trim($_POST['assigned_agent'] ?? '');
$agent_esc = mysqli_real_escape_string($con, $agent);

if($id > 0){
    $set_agent = ($agent_esc === '') ? "assigned_agent=NULL, assigned_at=NULL" : "assigned_agent='$agent_esc', assigned_at=NOW()";
    $u = "UPDATE service_requests SET $set_agent WHERE id='$id' LIMIT 1";
    mysqli_query($con, $u);

    if($agent_esc !== ''){
        @mysqli_query($con, "UPDATE service_requests SET status='in_progress' WHERE id='$id' AND LOWER(IFNULL(status,''))='pending' LIMIT 1");
        log_delivery_action($con, $agent, 'assign_service', 'Assigned service request #'.$id.' by admin '.$_SESSION['username']);
    }
}

header('Location: service_requests.php');
exit;
