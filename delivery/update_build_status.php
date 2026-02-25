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
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:index.php');
    exit;
}

$id = isset($_POST['build_id']) ? intval($_POST['build_id']) : 0;
$requested_status = strtolower(trim($_POST['build_status'] ?? 'out_for_delivery'));
if(!in_array($requested_status, ['out_for_delivery', 'delivered', 'cancelled'], true)){
    $requested_status = 'out_for_delivery';
}
$status = mysqli_real_escape_string($con, $requested_status);
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');

if($id > 0){
    $sr = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
    if(!$sr || mysqli_num_rows($sr) === 0){
        echo '<script>alert("Status Update Failed");window.location.href="index.php";</script>';
        exit;
    }
    $row = mysqli_fetch_assoc($sr);
    $u = "UPDATE builds SET status='$status' WHERE id='$id' AND assigned_agent='$agent' LIMIT 1";
    mysqli_query($con, $u);
    echo '<script>window.location.href="index.php";</script>';
    exit;
}
echo '<script>alert("Invalid build ID");window.location.href="index.php";</script>';
