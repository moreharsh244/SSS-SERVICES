<?php
include('conn.php');
if($_SERVER['REQUEST_METHOD']!=='POST') exit('Invalid');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$dstatus = mysqli_real_escape_string($con, $_POST['delivery_status'] ?? 'pending');
// ensure column exists (defensive for older installs)
$col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase' AND COLUMN_NAME = 'delivery_status'";
$col_res = mysqli_query($con, $col_check);
if(!$col_res || mysqli_num_rows($col_res) === 0){
    @mysqli_query($con, "ALTER TABLE purchase ADD COLUMN delivery_status VARCHAR(50) DEFAULT 'pending'");
}
if($id>0){
  $u = "UPDATE purchase SET delivery_status='$dstatus' WHERE pid='$id' LIMIT 1";
  mysqli_query($con, $u);
}
header('Location: orders_list.php');
exit;
