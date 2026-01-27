<?php
    include('../admin/conn.php');
    $pid=$_POST['pid'];
    $pname=$_POST['pname'];
    $pprice=$_POST['pprice'];
    $qty=$_POST['qty'];
    $username=$_POST['username'];
    $sql="INSERT INTO `purchase` ( `pname`, `user`, `pprice`, `qty`, `prod_id`, `status`) VALUES ('$pname', '$username', '$pprice', '$qty', '$pid', 'pending')";
    if(mysqli_query($con,$sql)){
        echo '<script>alert("Purchase Successful");window.location.href="view_products.php";</script>'; 
    }else{
        echo '<script>alert("Purchase Failed");window.location.href="view_products.php";</script>'; 
    }
        
?>