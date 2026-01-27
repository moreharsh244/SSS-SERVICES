<?php
define('page','myorder_details');
include('header.php');
include('../admin/conn.php');
$order_id=$_POST['order_id'];   
$sql="SELECT * FROM `purchase` INNER JOIN products ON purchase.prod_id=products.pid where purchase.pid='$order_id'";
$result=mysqli_query($con,$sql);
$row=mysqli_fetch_assoc($result);
echo '<div class="container justify-content-center mt-5">';
echo '<div class="row ">';        
echo '<div class="col-md-6">';
echo '<div class="card">';
echo '<div class="card-header bg-primary text-white">';
echo '<h4>Order Details</h4>';
echo '</div>';
echo '<div class="card-body">'; 
echo '<p><strong>Purchase Date:</strong> '.$row['pdate'].'</p>';
echo '<p><strong>Product Name:</strong> '.$row['pname'].'</p>';
echo '<p><strong>Quantity:</strong> '.$row['qty'].'</p>';   
echo '<p><strong>Price:</strong> '.$row['pprice'].'</p>';
echo '<p><strong>Company:</strong> '.$row['pcompany'].'</p>';
echo '<p><strong>Description:</strong> '.$row['pdes'].'</p>';
echo '<p><strong>Status:</strong> '.$row['status'].'</p>';
echo '<p><strong>Delivery Status:</strong> '.$row['delivery_status'].'</p>';
 $total=$row['pprice'] * $row['qty'];
echo '<hr>';
echo '<h5>Total Amount: '.$total.'</h5>';
echo '</div>';
echo '</div>';  

echo '</div>';

echo '</div>';
echo '</div>';  
include('footer.php');

?>