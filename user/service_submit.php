<?php
include('../admin/conn.php');
if(session_status()===PHP_SESSION_NONE) session_start();
if($_SERVER['REQUEST_METHOD']!=='POST') exit('Invalid');
$service_type = mysqli_real_escape_string($con, $_POST['service_type'] ?? 'general');
$item = mysqli_real_escape_string($con, $_POST['item'] ?? '');
$details = mysqli_real_escape_string($con, $_POST['details'] ?? '');
$user = mysqli_real_escape_string($con, $_SESSION['username'] ?? 'guest');
// ensure service_requests table exists
$create = "CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user` VARCHAR(255) NOT NULL,
  `item` VARCHAR(255),
  `service_type` VARCHAR(100),
  `details` TEXT,
  `status` VARCHAR(50) DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@mysqli_query($con, $create);
$ins = "INSERT INTO service_requests (`user`, item, service_type, details) VALUES ('". $user ."', '". $item ."', '". $service_type ."', '". $details ."')";
@mysqli_query($con, $ins);
header('Location: service.php?ok=1');
exit;
?>