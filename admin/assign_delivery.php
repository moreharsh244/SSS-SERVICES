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
    // Check if this order is a build component or a whole build
    $is_build = false;
    $build_id = 0;
    $build_check = mysqli_query($con, "SELECT build_id FROM build_items WHERE product_id='$order_id' LIMIT 1");
    if($build_check && mysqli_num_rows($build_check) > 0){
        $build_row = mysqli_fetch_assoc($build_check);
        $build_id = (int)$build_row['build_id'];
        // If this product is a build component, assign agent to the build, not the product
        $is_build = true;
    }
        if($is_build && $build_id > 0){
            // Always set assigned_agent and status for the build
            $u = "UPDATE builds SET assigned_agent=".($agent_esc === '' ? "NULL" : "'$agent_esc'").", status=".($agent_esc !== '' ? "'out_for_delivery'" : "'pending'")." WHERE id='$build_id' LIMIT 1";
            mysqli_query($con, $u);
            if($agent_esc !== ''){
                log_delivery_action($con, $agent, 'assign_build', 'Assigned build #'.$build_id.' by admin '.$_SESSION['username']);
            }
        } else {
            // Regular product order assignment
            $u = "UPDATE purchase SET assigned_agent=".($agent_esc === '' ? "NULL" : "'$agent_esc'")." WHERE pid='$order_id' LIMIT 1";
            mysqli_query($con, $u);
            if($agent_esc !== ''){
                @mysqli_query($con, "UPDATE purchase SET status='out_for_delivery', delivery_status='out_for_delivery' WHERE pid='$order_id' LIMIT 1");
                log_delivery_action($con, $agent, 'assign_order', 'Assigned order #'.$order_id.' by admin '.$_SESSION['username']);
            } else {
                @mysqli_query($con, "UPDATE purchase SET status='order_confirmed', delivery_status='order_confirmed' WHERE pid='$order_id' LIMIT 1");
            }
        }
    }

    $back = $_SERVER['HTTP_REFERER'] ?? 'orders_list.php';
    $sep = (strpos($back, '?') !== false) ? '&' : '?';
    header('Location: ' . $back . $sep . 'toast=' . rawurlencode('Order assigned successfully.') . '&toast_type=success');
    exit;
