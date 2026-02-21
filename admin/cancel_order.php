<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
if (!isset($_SESSION['is_login'])) {
    header('location:login.php');
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}

include('conn.php');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: orders_list.php');
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if($order_id <= 0){
    header('Location: orders_list.php?error=' . rawurlencode('Unable to process cancellation. Invalid order ID.'));
    exit;
}

// Check if order exists and is not already cancelled or delivered
$sql = "SELECT * FROM purchase WHERE pid='$order_id' AND LOWER(IFNULL(delivery_status,'pending')) NOT IN ('cancelled', 'delivered') LIMIT 1";
$res = mysqli_query($con, $sql);
if(!$res || mysqli_num_rows($res) === 0){
    header('Location: orders_list.php?error=' . rawurlencode('This order cannot be cancelled. It may be already delivered or cancelled.'));
    exit;
}
$row = mysqli_fetch_assoc($res);

// Create history table if missing
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

// Ensure assigned_agent column exists for older history tables
$hist_col = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase_history' AND COLUMN_NAME='assigned_agent'";
$hist_res = mysqli_query($con, $hist_col);
if(!$hist_res || mysqli_num_rows($hist_res) === 0){
    @mysqli_query($con, "ALTER TABLE purchase_history ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

// If order has a product_id, restore stock
if(!empty($row['prod_id'])){
    $prod_id = intval($row['prod_id']);
    $qty_to_restore = intval($row['qty'] ?? 1);
    @mysqli_query($con, "UPDATE products SET pqty = pqty + $qty_to_restore WHERE pid = $prod_id");
}

// Prepare data for history
$pname = mysqli_real_escape_string($con, $row['pname']);
$user = mysqli_real_escape_string($con, $row['user']);
$pprice = (float)($row['pprice'] ?? 0);
$qty = intval($row['qty'] ?? 1);
$prod_id = isset($row['prod_id']) && $row['prod_id'] !== null ? intval($row['prod_id']) : 'NULL';
$pdate = mysqli_real_escape_string($con, $row['pdate']);
$agent = mysqli_real_escape_string($con, $row['assigned_agent'] ?? '');

// Insert into history with cancelled status
$ins = "INSERT INTO purchase_history (pid,pname,`user`,pprice,qty,prod_id,`status`,delivery_status,assigned_agent,pdate)
        VALUES ($order_id,'$pname','$user',$pprice,$qty,".($prod_id==='NULL'?'NULL':$prod_id).",'cancelled','cancelled','$agent','$pdate')
        ON DUPLICATE KEY UPDATE `status`=VALUES(`status`), delivery_status=VALUES(delivery_status), assigned_agent=VALUES(assigned_agent)";
@mysqli_query($con, $ins);

// Delete from active purchase table
@mysqli_query($con, "DELETE FROM purchase WHERE pid='$order_id' LIMIT 1");

// Add admin notification about the cancellation
$customer_name = $user;
if(function_exists('add_admin_notification')){
    add_admin_notification(
        'warning',
        'Order Cancelled',
        "Order #$order_id for $customer_name has been cancelled by admin.",
        'orders_list.php?view=history'
    );
}

header('Location: orders_list.php?success=' . rawurlencode('Order #' . $order_id . ' has been cancelled successfully.'));
exit;
?>
