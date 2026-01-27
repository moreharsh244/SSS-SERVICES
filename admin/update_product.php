<?php
include('conn.php');
$uid=$_POST['update_id'];
$pname=$_POST['pname'];
$pcompany=$_POST['pcompany'];           
$pprice=$_POST['pprice'];
$pqty=$_POST['pqty'];   
$pamount=$_POST['pamount'];
$pdescription=$_POST['product_description'];    
//echo $uid . ' ' . $pname . ' ' . $pcompany . ' ' . $pprice . ' ' . $pqty . ' ' . $pamount . ' ' . $pdescription;
$sqlq="UPDATE `products` SET `pname`='$pname',`pcompany`='$pcompany',`pqty`='$pqty',`pprice`='$pprice',`pamount`='$pamount',`pdisc`='$pdescription' WHERE pid='$uid'";
$result=mysqli_query($con,$sqlq);
if($result){        
    header('location:view_product.php');
} else {
    echo "Error updating record: " . mysqli_error($con);
}
?>
