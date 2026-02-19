<?php
include('conn.php');

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
        add_admin_notification('low_stock', $notif_title, $notif_msg, 'view_product.php');
    }
    
    header('location:view_product.php?success=Product+updated');
} else {
    echo "Error updating record: " . mysqli_error($con);
}
?>
