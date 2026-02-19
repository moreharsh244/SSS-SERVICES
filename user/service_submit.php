<?php
include('../admin/conn.php');

if (session_status() === PHP_SESSION_NONE) {
  session_name('SSS_USER_SESS');
  session_start();
}
if($_SERVER['REQUEST_METHOD']!=='POST') exit('Invalid');
$service_type = mysqli_real_escape_string($con, $_POST['service_type'] ?? 'general');
$item = mysqli_real_escape_string($con, $_POST['item'] ?? '');
$details = mysqli_real_escape_string($con, $_POST['details'] ?? '');
$phone = mysqli_real_escape_string($con, $_POST['phone'] ?? '');
$contact_time = mysqli_real_escape_string($con, $_POST['contact_time'] ?? '');
$user = mysqli_real_escape_string($con, $_SESSION['username'] ?? 'guest');
// ensure service_requests table exists
$create = "CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user` VARCHAR(255) NOT NULL,
  `item` VARCHAR(255),
  `service_type` VARCHAR(100),
  `details` TEXT,
  `phone` VARCHAR(50),
  `contact_time` VARCHAR(100),
  `status` VARCHAR(50) DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@mysqli_query($con, $create);
// add new columns for existing databases if missing
$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
if($db){
  $db_safe = mysqli_real_escape_string($con, $db);
  $col_phone = @mysqli_query($con, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db_safe' AND TABLE_NAME='service_requests' AND COLUMN_NAME='phone' LIMIT 1");
  if(!$col_phone || mysqli_num_rows($col_phone)==0){ @mysqli_query($con, "ALTER TABLE service_requests ADD COLUMN phone VARCHAR(50)"); }
  $col_time = @mysqli_query($con, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db_safe' AND TABLE_NAME='service_requests' AND COLUMN_NAME='contact_time' LIMIT 1");
  if(!$col_time || mysqli_num_rows($col_time)==0){ @mysqli_query($con, "ALTER TABLE service_requests ADD COLUMN contact_time VARCHAR(100)"); }
}
$ins = "INSERT INTO service_requests (`user`, item, service_type, details, phone, contact_time) VALUES ('". $user ."', '". $item ."', '". $service_type ."', '". $details ."', '". $phone ."', '". $contact_time ."')";
if(@mysqli_query($con, $ins)){
    // Create admin notification for new service request
    $notif_title = "New Support Request: $service_type";
    $notif_msg = "Customer: $user | Item: $item";
    add_admin_notification('service', $notif_title, $notif_msg, 'service_requests.php');
}
header('Location: service.php?ok=1');
exit;
?>