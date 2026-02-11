<?php
include('conn.php');
include('../delivery/helpers.php');
ensure_service_requests_table($con);
if($_SERVER['REQUEST_METHOD']!=='POST') exit('Invalid');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'pending');
if($id>0){
  $u = "UPDATE service_requests SET status='".mysqli_real_escape_string($con,$status)."' WHERE id=$id LIMIT 1";
  @mysqli_query($con, $u);
}
header('Location: service_requests.php');
exit;
?>