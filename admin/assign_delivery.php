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
ensure_purchase_table($con);

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:orders_list.php');
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$agent = trim($_POST['assigned_agent'] ?? '');
$agent_esc = mysqli_real_escape_string($con, $agent);

if($order_id > 0){
    $u = "UPDATE purchase SET assigned_agent=".($agent_esc === '' ? "NULL" : "'$agent_esc'")." WHERE pid='$order_id' LIMIT 1";
    mysqli_query($con, $u);
    if($agent_esc !== ''){
        @mysqli_query($con, "UPDATE purchase SET delivery_status='shipped' WHERE pid='$order_id' LIMIT 1");
        log_delivery_action($con, $agent, 'assign_order', 'Assigned order #'.$order_id.' by admin '.$_SESSION['username']);
    }
}

header('Location: orders_list.php');
exit;
