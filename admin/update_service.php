<?php
include('conn.php');
if($_SERVER['REQUEST_METHOD']!=='POST') exit('Invalid');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'pending');
if($id>0){
  // ensure table exists
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

  $u = "UPDATE service_requests SET status='".mysqli_real_escape_string($con,$status)."' WHERE id=$id LIMIT 1";
  @mysqli_query($con, $u);
}
header('Location: service_requests.php');
exit;
?>