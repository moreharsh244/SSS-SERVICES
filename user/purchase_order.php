
<?php
    include('../admin/conn.php');
    include_once('../admin/notifications.php');
    
    $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
    $pprice = floatval($_POST['pprice'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    $username = mysqli_real_escape_string($con, $_POST['username'] ?? ($_SESSION['username'] ?? ''));
    $payment_method = mysqli_real_escape_string($con, $_POST['payment_method'] ?? 'cod');
    $payment_ref = mysqli_real_escape_string($con, trim($_POST['payment_ref'] ?? ''));
    $payment_status = ($payment_method === 'online') ? 'paid' : 'pending';
    $delivery_address = trim((string)($_POST['delivery_address'] ?? ''));
    $delivery_city = trim((string)($_POST['delivery_city'] ?? ''));
    $delivery_state = trim((string)($_POST['delivery_state'] ?? ''));
    $delivery_pincode = trim((string)($_POST['delivery_pincode'] ?? ''));

    // Hard validation: delivery address is mandatory before purchase.
    if($delivery_address === '' || $delivery_city === '' || $delivery_state === '' || $delivery_pincode === ''){
        $profile_q = mysqli_query($con, "SELECT c_address, c_city, c_state, c_pincode FROM cust_reg WHERE c_email='".mysqli_real_escape_string($con, $username)."' LIMIT 1");
        if($profile_q && mysqli_num_rows($profile_q) > 0){
            $pr = mysqli_fetch_assoc($profile_q);
            if($delivery_address === '') $delivery_address = trim((string)($pr['c_address'] ?? ''));
            if($delivery_city === '') $delivery_city = trim((string)($pr['c_city'] ?? ''));
            if($delivery_state === '') $delivery_state = trim((string)($pr['c_state'] ?? ''));
            if($delivery_pincode === '') $delivery_pincode = trim((string)($pr['c_pincode'] ?? ''));
        }
    }
    if($delivery_address === '' || $delivery_city === '' || $delivery_state === '' || $delivery_pincode === ''){
        header('Location: profile.php?toast='.rawurlencode('Delivery address is required before purchase. Please complete your address first.') . '&toast_type=warning');
        exit;
    }
    $delivery_address_esc = mysqli_real_escape_string($con, $delivery_address);
    $delivery_city_esc = mysqli_real_escape_string($con, $delivery_city);
    $delivery_state_esc = mysqli_real_escape_string($con, $delivery_state);
    $delivery_pincode_esc = mysqli_real_escape_string($con, $delivery_pincode);

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
        `delivery_address` TEXT NULL,
        `delivery_city` VARCHAR(128) DEFAULT NULL,
        `delivery_state` VARCHAR(128) DEFAULT NULL,
        `delivery_pincode` VARCHAR(32) DEFAULT NULL,
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
        'assigned_agent' => "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL",
        'delivery_address' => "ALTER TABLE purchase ADD COLUMN delivery_address TEXT NULL",
        'delivery_city' => "ALTER TABLE purchase ADD COLUMN delivery_city VARCHAR(128) DEFAULT NULL",
        'delivery_state' => "ALTER TABLE purchase ADD COLUMN delivery_state VARCHAR(128) DEFAULT NULL",
        'delivery_pincode' => "ALTER TABLE purchase ADD COLUMN delivery_pincode VARCHAR(32) DEFAULT NULL"
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
        $verify = mysqli_query($con, "SELECT pid, pqty FROM products WHERE pid = $pid LIMIT 1");
        if($verify && mysqli_num_rows($verify) > 0){
            $prod = mysqli_fetch_assoc($verify);
            $available_qty = intval($prod['pqty'] ?? 0);
            if($available_qty <= 0){
                header('Location: view_products.php?toast='.rawurlencode('This product is out of stock'));
                exit;
            }
            if($qty > $available_qty){
                header('Location: view_products.php?toast='.rawurlencode('Only '.$available_qty.' item(s) available in stock'));
                exit;
            }
            $prod_id_value = $pid;
        } else {
            header('Location: view_products.php?toast='.rawurlencode('Product not found'));
            exit;
        }
    }

    $sql = "INSERT INTO `purchase` (`pname`,`user`,`pprice`,`qty`,`prod_id`,`payment_method`,`payment_ref`,`payment_status`,`delivery_address`,`delivery_city`,`delivery_state`,`delivery_pincode`,`status`,`delivery_status`) VALUES ('$pname','$username','$pprice','$qty',$prod_id_value,'$payment_method','$payment_ref','$payment_status','$delivery_address_esc','$delivery_city_esc','$delivery_state_esc','$delivery_pincode_esc','order_confirmed','order_confirmed')";
        if(mysqli_query($con,$sql)){
            // Create admin notification for new order
            $order_total = $pprice * $qty;
            $notif_title = "New Order: $pname";
            $notif_msg = "Customer: $username | Qty: $qty | Total: ₹" . number_format($order_total, 2);
            add_admin_notification('order', $notif_title, $notif_msg, 'orders_list.php');
            // award loyalty points: 1 point per 10 currency units
            $points = floor(($pprice * $qty) / 10);
            if($points > 0 && !empty($username)){
                // ensure column exists
                $colQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='loyalty_points'";
                $colR = mysqli_query($con,$colQ);
                if(!$colR || mysqli_num_rows($colR)===0){ @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN loyalty_points INT DEFAULT 0"); }
                mysqli_query($con, "UPDATE cust_reg SET loyalty_points = COALESCE(loyalty_points,0) + $points WHERE c_email='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
            }
            header('Location: view_products.php?toast='.rawurlencode('Purchase successful'));
            exit;
        }else{
            header('Location: view_products.php?toast='.rawurlencode('Purchase transaction failed. Please try again.'));
            exit;
        }
        
?>