<?php
include('conn.php');

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

$uid = $_POST['update_id'] ?? null;
if (!$uid) {
    header('location:view_product.php?error=Missing+product+id');
    exit;
}
$pname = $_POST['pname'] ?? '';
$pcompany = $_POST['pcompany'] ?? '';
$pprice = $_POST['pprice'] ?? '';
$pqty = $_POST['pqty'] ?? '';
$pamount = $_POST['pamount'] ?? '';
$pdescription = $_POST['product_description'] ?? '';
$pcat = $_POST['pcat'] ?? '';
$pimg = $_POST['current_pimg'] ?? '';

if (!empty($_FILES['pimg']['name']) && $_FILES['pimg']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['pimg']['name']);
    $target_dir = "../productimg/";
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['pimg']['tmp_name'], $target_file)) {
        $pimg = $filename;
    } else {
        header('location:view_product.php?error=Image+upload+failed');
        exit;
    }
}
//echo $uid . ' ' . $pname . ' ' . $pcompany . ' ' . $pprice . ' ' . $pqty . ' ' . $pamount . ' ' . $pdescription;
$sqlq="UPDATE `products` SET `pname`='$pname',`pcompany`='$pcompany',`pqty`='$pqty',`pprice`='$pprice',`pamount`='$pamount',`pdisc`='$pdescription', `pcat`='$pcat', `pimg`='$pimg' WHERE pid='$uid'";
$result=mysqli_query($con,$sqlq);
if($result){
    // Check if stock is low and create notification
    $qty_int = intval($pqty);
    if($qty_int <= 5){
        $notif_title = "Low Stock: $pname";
        $notif_msg = "$pcompany - Only $qty_int units remaining";
        add_admin_notification($con, 'low_stock', $notif_title, $notif_msg, 'view_product.php');
    }
    
    header('location:view_product.php?success=Product+updated');
} else {
    echo "Error updating record: " . mysqli_error($con);
}
?>
