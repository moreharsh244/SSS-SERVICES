<?php
include('conn.php');
$delete_id=$_POST['did'];
echo $delete_id;
$sqlq="delete from products where pid='$delete_id'";
$result=mysqli_query($con,$sqlq);
if($result){
    header('location:view_product.php');
}
?>