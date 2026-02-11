<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_DELIVERY_SESS');
    session_start();
}
if(!isset($_SESSION['is_login'])){
    header('location:login.php');
    exit;
}
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'delivery'){
    header('location:login.php');
    exit;
}
include('../admin/conn.php');
include 'helpers.php';
ensure_delivery_tables($con);
ensure_service_requests_table($con);

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:index.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'pending');
$note = mysqli_real_escape_string($con, $_POST['agent_note'] ?? '');
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');

if($id > 0){
    $u = "UPDATE service_requests SET status='$status', agent_note='$note' WHERE id='$id' AND assigned_agent='$agent' LIMIT 1";
    mysqli_query($con, $u);
    log_delivery_action($con, $_SESSION['username'] ?? '', 'service_update', 'Service request #'.$id.' -> '.$status);
}

header('Location: index.php');
exit;
