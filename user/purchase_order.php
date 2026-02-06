<?php
    include('../admin/conn.php');
    $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
    $pprice = floatval($_POST['pprice'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    $username = mysqli_real_escape_string($con, $_POST['username'] ?? ($_SESSION['username'] ?? ''));

    // ensure purchase table exists
    $create = "CREATE TABLE IF NOT EXISTS `purchase` (
        `pid` INT AUTO_INCREMENT PRIMARY KEY,
        `pname` VARCHAR(255) NOT NULL,
        `user` VARCHAR(255) NOT NULL,
        `pprice` DECIMAL(10,2) NOT NULL,
        `qty` INT NOT NULL DEFAULT 1,
        `prod_id` INT DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'pending',
        `delivery_status` VARCHAR(50) DEFAULT 'pending',
        `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $create);

    $sql = "INSERT INTO `purchase` (`pname`,`user`,`pprice`,`qty`,`prod_id`,`status`) VALUES ('$pname','$username','$pprice','$qty','$pid','pending')";
    if(mysqli_query($con,$sql)){
        echo '<script>alert("Purchase Successful");window.location.href="view_products.php";</script>'; 
    }else{
        echo '<script>alert("Purchase Failed: '.mysqli_error($con).'");window.location.href="view_products.php";</script>'; 
    }
        
?>