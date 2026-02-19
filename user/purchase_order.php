<?php
    include('../admin/conn.php');
    
    // Load notification functions
    function ensure_admin_notifications_table($con) {
        $create = "CREATE TABLE IF NOT EXISTS `admin_notifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type` VARCHAR(50) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT,
            `link` VARCHAR(255),
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(`is_read`),
            INDEX(`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $create);
    }
    
    function add_admin_notification($con, $type, $title, $message = '', $link = '') {
        ensure_admin_notifications_table($con);
        $type = mysqli_real_escape_string($con, $type);
        $title = mysqli_real_escape_string($con, $title);
        $message = mysqli_real_escape_string($con, $message);
        $link = mysqli_real_escape_string($con, $link);
        $sql = "INSERT INTO admin_notifications (type, title, message, link, is_read) 
                VALUES ('$type', '$title', '$message', '$link', 0)";
        return @mysqli_query($con, $sql);
    }
    
    $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
    $pprice = floatval($_POST['pprice'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    $username = mysqli_real_escape_string($con, $_POST['username'] ?? ($_SESSION['username'] ?? ''));
    $payment_method = mysqli_real_escape_string($con, $_POST['payment_method'] ?? 'cod');
    $payment_ref = mysqli_real_escape_string($con, trim($_POST['payment_ref'] ?? ''));
    $payment_status = ($payment_method === 'online') ? 'paid' : 'pending';

    // ensure purchase table exists
    $create = "CREATE TABLE IF NOT EXISTS `purchase` (
        `pid` INT AUTO_INCREMENT PRIMARY KEY,
        `pname` VARCHAR(255) NOT NULL,
        `user` VARCHAR(255) NOT NULL,
        `pprice` DECIMAL(10,2) NOT NULL,
        `qty` INT NOT NULL DEFAULT 1,
        `prod_id` INT DEFAULT NULL,
        `payment_method` VARCHAR(20) DEFAULT 'cod',
        `payment_ref` VARCHAR(100) DEFAULT NULL,
        `payment_status` VARCHAR(20) DEFAULT 'pending',
        `status` VARCHAR(50) DEFAULT 'pending',
        `delivery_status` VARCHAR(50) DEFAULT 'pending',
        `assigned_agent` VARCHAR(100) DEFAULT NULL,
        `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $create);

    // ensure new columns exist
    $columns_to_add = [
        'payment_method' => "ALTER TABLE purchase ADD COLUMN payment_method VARCHAR(20) DEFAULT 'cod'",
        'payment_ref' => "ALTER TABLE purchase ADD COLUMN payment_ref VARCHAR(100) DEFAULT NULL",
        'payment_status' => "ALTER TABLE purchase ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending'",
        'assigned_agent' => "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL"
    ];
    foreach($columns_to_add as $col => $ddl){
        $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase' AND COLUMN_NAME='$col'";
        $col_res = mysqli_query($con, $col_check);
        if(!$col_res || mysqli_num_rows($col_res)===0){
            @mysqli_query($con, $ddl);
        }
    }

    // Verify product exists if pid is provided
    $prod_id_value = 'NULL';
    if($pid > 0){
        $verify = mysqli_query($con, "SELECT pid FROM products WHERE pid = $pid LIMIT 1");
        if($verify && mysqli_num_rows($verify) > 0){
            $prod_id_value = $pid;
        }
    }

    $sql = "INSERT INTO `purchase` (`pname`,`user`,`pprice`,`qty`,`prod_id`,`payment_method`,`payment_ref`,`payment_status`,`status`,`delivery_status`) VALUES ('$pname','$username','$pprice','$qty',$prod_id_value,'$payment_method','$payment_ref','$payment_status','order_confirmed','order_confirmed')";
    if(mysqli_query($con,$sql)){
        // Create admin notification for new order
        $order_total = $pprice * $qty;
        $notif_title = "New Order: $pname";
        $notif_msg = "Customer: $username | Qty: $qty | Total: ₹" . number_format($order_total, 2);
        add_admin_notification($con, 'order', $notif_title, $notif_msg, 'orders_list.php');
        
        // award loyalty points: 1 point per 10 currency units
        $points = floor(($pprice * $qty) / 10);
        if($points > 0 && !empty($username)){
            // ensure column exists
            $colQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='loyalty_points'";
            $colR = mysqli_query($con,$colQ);
            if(!$colR || mysqli_num_rows($colR)===0){ @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN loyalty_points INT DEFAULT 0"); }
            mysqli_query($con, "UPDATE cust_reg SET loyalty_points = COALESCE(loyalty_points,0) + $points WHERE c_email='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
        }
        echo '<script>window.location.href="view_products.php?toast='.rawurlencode('Purchase successful').'";</script>';
    }else{
        echo '<script>window.location.href="view_products.php?toast='.rawurlencode('Purchase transaction failed. Please try again.').'.";</script>';
    }
        
?>