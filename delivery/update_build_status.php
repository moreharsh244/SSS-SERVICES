<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_DELIVERY_SESS');
    session_start();
}
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
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('location:index.php');
    exit;
}

$id = isset($_POST['build_id']) ? intval($_POST['build_id']) : 0;
$requested_status = strtolower(trim($_POST['build_status'] ?? 'out_for_delivery'));
if(!in_array($requested_status, ['out_for_delivery', 'delivered', 'cancelled'], true)){
    $requested_status = 'out_for_delivery';
}
$status = mysqli_real_escape_string($con, $requested_status);
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');

if($id > 0){
    $sr = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
    if(!$sr || mysqli_num_rows($sr) === 0){
        header('Location: index.php?toast=' . rawurlencode('Status update failed.') . '&toast_type=error');
        exit;
    }
    $row = mysqli_fetch_assoc($sr);
    $u = "UPDATE builds SET status='$status' WHERE id='$id' AND assigned_agent='$agent' LIMIT 1";
    mysqli_query($con, $u);
    // If delivered, reduce product quantity for all build components
    if($status === 'delivered'){
        $items_res = mysqli_query($con, "SELECT product_id, qty FROM build_items WHERE build_id='$id'");
        if($items_res && mysqli_num_rows($items_res) > 0){
            while($item = mysqli_fetch_assoc($items_res)){
                $pid = (int)$item['product_id'];
                $qty = (int)$item['qty'];
                // Reduce quantity in products table
                @mysqli_query($con, "UPDATE products SET qty = GREATEST(qty - $qty, 0) WHERE pid='$pid'");
            }
        }

        // Determine user identifier for this build
        $bq = mysqli_query($con, "SELECT user_name, user_id, name, description, total, created_at FROM builds WHERE id='$id' LIMIT 1");
        $userIdentifier = '';
        $buildRow = null;
        if($bq && mysqli_num_rows($bq) > 0){
            $buildRow = mysqli_fetch_assoc($bq);
            $maybe = trim($buildRow['user_name'] ?? '');
            if (!empty($maybe) && filter_var($maybe, FILTER_VALIDATE_EMAIL)) { $userIdentifier = $maybe; }
            if ($userIdentifier === '' && !empty($buildRow['user_id'])) { $userIdentifier = 'user_'.intval($buildRow['user_id']); }
        }

        // Gather product IDs for this build
        $pids = [];
        $bi_res = mysqli_query($con, "SELECT product_id FROM build_items WHERE build_id='$id'");
        if($bi_res && mysqli_num_rows($bi_res) > 0){ while($bi = mysqli_fetch_assoc($bi_res)){ $pids[] = intval($bi['product_id']); } }

        if(!empty($pids)){
            // Ensure purchase_history table exists
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

            $pid_list = implode(',', array_map('intval', array_unique($pids)));
            $where_user = $userIdentifier !== '' ? "AND `user`='".mysqli_real_escape_string($con,$userIdentifier)."'" : "";
            $pr = mysqli_query($con, "SELECT * FROM purchase WHERE prod_id IN ($pid_list) $where_user");
            if($pr && mysqli_num_rows($pr) > 0){
                while($prow = mysqli_fetch_assoc($pr)){
                    $pid_row = intval($prow['pid']);
                    $pname = mysqli_real_escape_string($con, $prow['pname']);
                    $user = mysqli_real_escape_string($con, $prow['user']);
                    $pprice = (float)($prow['pprice'] ?? 0);
                    $pqty = intval($prow['qty'] ?? 1);
                    $prod_id = isset($prow['prod_id']) && $prow['prod_id'] !== null ? intval($prow['prod_id']) : 'NULL';
                    $pdate = mysqli_real_escape_string($con, $prow['pdate']);
                    $agent_name = mysqli_real_escape_string($con, $prow['assigned_agent'] ?? $agent);

                    $ins = "INSERT INTO purchase_history (pid,pname,`user`,pprice,qty,prod_id,`status`,delivery_status,assigned_agent,pdate) VALUES ($pid_row,'$pname','$user',$pprice,$pqty,".($prod_id==='NULL'?'NULL':$prod_id).",'delivered','delivered','$agent_name','$pdate') ON DUPLICATE KEY UPDATE pname=VALUES(pname), `status`=VALUES(`status`), delivery_status=VALUES(delivery_status), assigned_agent=VALUES(assigned_agent)";
                    @mysqli_query($con, $ins);
                    @mysqli_query($con, "DELETE FROM purchase WHERE pid='$pid_row' LIMIT 1");
                }
            }
        }

        // Archive build to builds_history and remove original build/items
        if($buildRow){
            $user_id = intval($buildRow['user_id'] ?? 0);
            $user_name = mysqli_real_escape_string($con, $buildRow['user_name'] ?? '');
            $name = mysqli_real_escape_string($con, $buildRow['name'] ?? '');
            $description = mysqli_real_escape_string($con, $buildRow['description'] ?? '');
            $total = floatval($buildRow['total'] ?? 0);
            $status_b = mysqli_real_escape_string($con, $buildRow['status'] ?? 'delivered');
            $created_at = mysqli_real_escape_string($con, $buildRow['created_at'] ?? '');
            $assigned_agent_arch = mysqli_real_escape_string($con, $buildRow['assigned_agent'] ?? $agent);

            $insb = "INSERT INTO builds_history (id, user_id, user_name, name, description, total, status, created_at, completed_at, assigned_agent) VALUES ($id, $user_id, '$user_name', '$name', '$description', $total, '$status_b', " . (!empty($created_at) ? "'{$created_at}'" : "NULL") . ", NOW(), '". $assigned_agent_arch ."') ON DUPLICATE KEY UPDATE status=VALUES(status), user_name=VALUES(user_name), description=VALUES(description), completed_at=VALUES(completed_at), assigned_agent=VALUES(assigned_agent)";
            @mysqli_query($con, $insb);
            @mysqli_query($con, "DELETE FROM build_items WHERE build_id='$id'");
            @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
            // Add admin notification about delivered build
            $notif_file = __DIR__ . '/../admin/admin_notifications.json';
            $notif_item = array(
                'id' => 'notif_'.uniqid(),
                'type' => 'build',
                'title' => 'Build Delivered: '.($name ?? 'Build #'.$id),
                'message' => 'Customer: '.($user_name ?? $userIdentifier).' | Total: ₹'.number_format($total,2),
                'link' => 'builds.php?view=history',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s')
            );
            if(is_writable(dirname($notif_file))){
                $existing = array();
                if(file_exists($notif_file)){
                    $txt = @file_get_contents($notif_file);
                    $existing = json_decode($txt, true) ?: array();
                }
                array_unshift($existing, $notif_item);
                @file_put_contents($notif_file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            }
        }
    }
    // Do NOT remove any other data from delivery portal
    header('Location: index.php?toast=' . rawurlencode('Build status updated successfully.') . '&toast_type=success');
    exit;
}
header('Location: index.php?toast=' . rawurlencode('Invalid build ID.') . '&toast_type=error');
exit;
