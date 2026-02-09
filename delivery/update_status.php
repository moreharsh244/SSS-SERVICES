<?php
session_start();
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
ensure_purchase_table($con);
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:index.php');
    exit;
}

$id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$dstatus = mysqli_real_escape_string($con, $_POST['delivery_status'] ?? 'pending');
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');

if($id > 0){
    $u = "UPDATE purchase SET delivery_status='$dstatus' WHERE pid='$id' AND assigned_agent='$agent' LIMIT 1";
    mysqli_query($con, $u);
    log_delivery_action($con, $_SESSION['username'] ?? '', 'status_update', 'Order #'.$id.' -> '.$dstatus);

    $d_lower = strtolower($dstatus);
    if($d_lower === 'delivered' || $d_lower === 'cancelled'){
        $sr = mysqli_query($con, "SELECT * FROM purchase WHERE pid='$id' AND assigned_agent='$agent' LIMIT 1");
        if($sr && mysqli_num_rows($sr)>0){
            $row = mysqli_fetch_assoc($sr);
            $create = "CREATE TABLE IF NOT EXISTS `purchase_history` (
                `pid` INT PRIMARY KEY,
                `pname` VARCHAR(255) NOT NULL,
                `user` VARCHAR(255) NOT NULL,
                `pprice` DECIMAL(10,2) NOT NULL,
                `qty` INT NOT NULL DEFAULT 1,
                `prod_id` INT DEFAULT NULL,
                `status` VARCHAR(50) DEFAULT 'pending',
                `delivery_status` VARCHAR(50) DEFAULT NULL,
                `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            @mysqli_query($con, $create);

            $pname = mysqli_real_escape_string($con, $row['pname']);
            $user = mysqli_real_escape_string($con, $row['user']);
            $pprice = (float)($row['pprice'] ?? 0);
            $qty = intval($row['qty'] ?? 1);
            $prod_id = isset($row['prod_id']) && $row['prod_id'] !== null ? intval($row['prod_id']) : 'NULL';
            $status = mysqli_real_escape_string($con, $row['status'] ?? 'pending');
            $pdate = mysqli_real_escape_string($con, $row['pdate']);

            $ins = "INSERT INTO purchase_history (pid,pname,`user`,pprice,qty,prod_id,`status`,delivery_status,pdate) VALUES ($id,'$pname','$user',$pprice,$qty,".($prod_id==='NULL'?'NULL':$prod_id).",'$status','$dstatus','$pdate') ON DUPLICATE KEY UPDATE pname=VALUES(pname), `status`=VALUES(`status`), delivery_status=VALUES(delivery_status)";
            @mysqli_query($con, $ins);

            @mysqli_query($con, "DELETE FROM purchase WHERE pid='$id' LIMIT 1");
        }
    }
    echo '<script>alert("Status Updated Successfully");window.location.href="index.php";</script>';
} else {
    echo '<script>alert("Status Update Failed");window.location.href="index.php";</script>';
}
?>