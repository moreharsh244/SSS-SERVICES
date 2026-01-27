<?php
include('../admin/conn.php');
$sqlq="UPDATE `purchase` SET `status`='$_POST[status]' WHERE pid='$_POST[order_id]'";
if(mysqli_query($con,$sqlq)){
    echo '<script>alert("Status Updated Successfully");window.location.href="index.php";</script>'; 
}else{
    echo '<script>alert("Status Update Failed");window.location.href="index.php";</script>'; 
}


?>