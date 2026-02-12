<?php
include('conn.php');
$delete_id = isset($_POST['did']) ? intval($_POST['did']) : 0;
if($delete_id <= 0){
    header('location:view_product.php?error=' . rawurlencode('Invalid product id'));
    exit;
}

$check_sql = "SELECT COUNT(*) AS cnt FROM purchase WHERE prod_id=$delete_id";
$check_res = mysqli_query($con, $check_sql);
$check_row = $check_res ? mysqli_fetch_assoc($check_res) : null;
$purchase_count = $check_row ? (int)$check_row['cnt'] : 0;

if($purchase_count > 0){
    header('location:view_product.php?error=' . rawurlencode('Cannot delete product with existing purchases'));
    exit;
}

$sqlq = "DELETE FROM products WHERE pid=$delete_id";
$result = mysqli_query($con, $sqlq);
if($result){
    header('location:view_product.php?success=' . rawurlencode('Product deleted'));
}else{
    header('location:view_product.php?error=' . rawurlencode('Delete failed'));
}
?>