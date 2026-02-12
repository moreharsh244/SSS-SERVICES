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
ensure_service_requests_history_table($con);

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:index.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'pending');
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');

if($id > 0){
    $u = "UPDATE service_requests SET status='$status' WHERE id='$id' AND assigned_agent='$agent' LIMIT 1";
    mysqli_query($con, $u);
    log_delivery_action($con, $_SESSION['username'] ?? '', 'service_update', 'Service request #'.$id.' -> '.$status);

    if(strtolower($status) === 'cancelled'){
        $sr = mysqli_query($con, "SELECT * FROM service_requests WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
        if($sr && mysqli_num_rows($sr)>0){
            $row = mysqli_fetch_assoc($sr);
            $user = mysqli_real_escape_string($con, $row['user'] ?? '');
            $item = mysqli_real_escape_string($con, $row['item'] ?? '');
            $stype = mysqli_real_escape_string($con, $row['service_type'] ?? '');
            $details = mysqli_real_escape_string($con, $row['details'] ?? '');
            $phone = mysqli_real_escape_string($con, $row['phone'] ?? '');
            $contact_time = mysqli_real_escape_string($con, $row['contact_time'] ?? '');
            $assigned_agent = mysqli_real_escape_string($con, $row['assigned_agent'] ?? '');
            $agent_note = mysqli_real_escape_string($con, $row['agent_note'] ?? '');
            $created_at = mysqli_real_escape_string($con, $row['created_at'] ?? '');
            $updated_at = mysqli_real_escape_string($con, $row['updated_at'] ?? '');

            $ins = "INSERT INTO service_requests_history (id, `user`, item, service_type, details, phone, contact_time, status, assigned_agent, assigned_at, agent_note, created_at, updated_at)
                    VALUES ($id,'$user','$item','$stype','$details','$phone','$contact_time','cancelled','$assigned_agent',".
                    (!empty($row['assigned_at']) ? "'".mysqli_real_escape_string($con, $row['assigned_at'])."'" : "NULL").
                    ",'$agent_note',".
                    (!empty($created_at) ? "'{$created_at}'" : "NULL").
                    ",".
                    (!empty($updated_at) ? "'{$updated_at}'" : "NULL").
                    ")
                    ON DUPLICATE KEY UPDATE status=VALUES(status), assigned_agent=VALUES(assigned_agent), agent_note=VALUES(agent_note), updated_at=VALUES(updated_at)";
            @mysqli_query($con, $ins);

            @mysqli_query($con, "DELETE FROM service_requests WHERE id='$id' LIMIT 1");
        }
    }
}

header('Location: index.php');
exit;
