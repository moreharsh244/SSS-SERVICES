<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    session_start();
}
if(!isset($_SESSION['is_login'])){
    header('location:login.php');
    exit;
}
include('../admin/conn.php');
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: myorder.php');
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if($order_id <= 0){
    header('Location: myorder.php?toast=' . rawurlencode('Invalid order.'));
    exit;
}

$sessionUser = $_SESSION['username'] ?? '';
$sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
$sessionUid = $_SESSION['user_id'] ?? null;
$possibleUsers = [ $sessionUserEsc ];
if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
$userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";

// only allow cancel when delivery_status is pending
$sql = "SELECT * FROM purchase WHERE pid='$order_id' AND user IN ({$userList}) AND LOWER(IFNULL(delivery_status,'pending'))='pending' LIMIT 1";
$res = mysqli_query($con, $sql);
if(!$res || mysqli_num_rows($res) === 0){
    header('Location: myorder.php?toast=' . rawurlencode('Order cannot be cancelled.'));
    exit;
}
$row = mysqli_fetch_assoc($res);

// create history table if missing
$create = "CREATE TABLE IF NOT EXISTS `purchase_history` (
  `pid` INT PRIMARY KEY,
  `pname` VARCHAR(255) NOT NULL,
  `user` VARCHAR(255) NOT NULL,
  `pprice` DECIMAL(10,2) NOT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  `prod_id` INT DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'pending',
  `delivery_status` VARCHAR(50) DEFAULT NULL,
  `assigned_agent` VARCHAR(100) DEFAULT NULL,
  `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@mysqli_query($con, $create);

// ensure assigned_agent column exists for older history tables
$hist_col = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase_history' AND COLUMN_NAME='assigned_agent'";
$hist_res = mysqli_query($con, $hist_col);
if(!$hist_res || mysqli_num_rows($hist_res) === 0){
    @mysqli_query($con, "ALTER TABLE purchase_history ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

$pname = mysqli_real_escape_string($con, $row['pname']);
$user = mysqli_real_escape_string($con, $row['user']);
$pprice = (float)($row['pprice'] ?? 0);
$qty = intval($row['qty'] ?? 1);
$prod_id = isset($row['prod_id']) && $row['prod_id'] !== null ? intval($row['prod_id']) : 'NULL';
$pdate = mysqli_real_escape_string($con, $row['pdate']);
$agent = mysqli_real_escape_string($con, $row['assigned_agent'] ?? '');

$ins = "INSERT INTO purchase_history (pid,pname,`user`,pprice,qty,prod_id,`status`,delivery_status,assigned_agent,pdate)
        VALUES ($order_id,'$pname','$user',$pprice,$qty,".($prod_id==='NULL'?'NULL':$prod_id).",'cancelled','cancelled','$agent','$pdate')
        ON DUPLICATE KEY UPDATE `status`=VALUES(`status`), delivery_status=VALUES(delivery_status), assigned_agent=VALUES(assigned_agent)";
@mysqli_query($con, $ins);

@mysqli_query($con, "DELETE FROM purchase WHERE pid='$order_id' LIMIT 1");

header('Location: myorder.php?view=history&toast=' . rawurlencode('Order cancelled.'));
exit;
?>