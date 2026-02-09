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
        `assigned_agent` VARCHAR(100) DEFAULT NULL,
        `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $create);

    // ensure assigned_agent column exists
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase' AND COLUMN_NAME='assigned_agent'";
    $col_res = mysqli_query($con, $col_check);
    if(!$col_res || mysqli_num_rows($col_res)===0){
        @mysqli_query($con, "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
    }

    $sql = "INSERT INTO `purchase` (`pname`,`user`,`pprice`,`qty`,`prod_id`,`status`) VALUES ('$pname','$username','$pprice','$qty','$pid','pending')";
    if(mysqli_query($con,$sql)){
        // award loyalty points: 1 point per 10 currency units
        $points = floor(($pprice * $qty) / 10);
        if($points > 0 && !empty($username)){
            // ensure column exists
            $colQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='loyalty_points'";
            $colR = mysqli_query($con,$colQ);
            if(!$colR || mysqli_num_rows($colR)===0){ @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN loyalty_points INT DEFAULT 0"); }
            mysqli_query($con, "UPDATE cust_reg SET loyalty_points = COALESCE(loyalty_points,0) + $points WHERE c_email='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
        }
        echo '<script>alert("Purchase Successful");window.location.href="view_products.php";</script>'; 
    }else{
        echo '<script>alert("Purchase Failed: '.mysqli_error($con).'");window.location.href="view_products.php";</script>'; 
    }
        
?>