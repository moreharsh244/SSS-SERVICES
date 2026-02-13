<?php
define('page','myorder_details');
include('header.php');
include('../admin/conn.php');
$order_id = intval($_POST['order_id'] ?? 0);
$row = false;
$is_history = false;
// build current user list for safety
$sessionUser = $_SESSION['username'] ?? '';
$sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
$sessionUid = $_SESSION['user_id'] ?? null;
$possibleUsers = [ $sessionUserEsc ];
if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
$userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";

if($order_id>0){
	// try active purchases first
	$sql = "SELECT * FROM `purchase` INNER JOIN products ON purchase.prod_id=products.pid WHERE purchase.pid='$order_id' AND purchase.user IN ({$userList}) LIMIT 1";
	$result = @mysqli_query($con, $sql);
	if($result && mysqli_num_rows($result)>0){
		$row = mysqli_fetch_assoc($result);
		$is_history = false;
	} else {
		// fallback to history table
		$db = '';
		$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
		if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
		if($db){
			$tbl = mysqli_real_escape_string($con, 'purchase_history');
			$qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
			if($qc && mysqli_num_rows($qc)>0){
				$sql2 = "SELECT * FROM `purchase_history` INNER JOIN products ON purchase_history.prod_id=products.pid WHERE purchase_history.pid='$order_id' AND purchase_history.user IN ({$userList}) LIMIT 1";
				$res2 = @mysqli_query($con, $sql2);
				if($res2 && mysqli_num_rows($res2)>0){
					$row = mysqli_fetch_assoc($res2);
					$is_history = true;
				}
			}
		}
	}
}
if(!$row){
	echo '<div class="container mt-5"><div class="alert alert-warning">Order not found.</div></div>';
	include('footer.php');
	exit;
}
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
echo '<p><strong>Status:</strong> '.htmlspecialchars($row['status'] ?? '').'</p>';
echo '<p><strong>Delivery Status:</strong> '.htmlspecialchars($row['delivery_status'] ?? '').'</p>';
 $total=$row['pprice'] * $row['qty'];
echo '<hr>';
echo '<h5>Total Amount: '.$total.'</h5>';
// allow cancel only for pending active orders
$delivery_state = strtolower(trim($row['delivery_status'] ?? ''));
$can_cancel = !$is_history && $delivery_state === 'pending';
if($can_cancel){
	echo '<form action="cancel_order.php" method="post" class="mt-3" onsubmit="return confirm(\'Are you sure you want to cancel this order?\');">';
	echo '<input type="hidden" name="order_id" value="'.intval($order_id).'">';
	echo '<button type="submit" class="btn btn-outline-danger btn-sm">Cancel Order</button>';
	echo '</form>';
}
echo '</div>';
echo '</div>';  

echo '</div>';

echo '</div>';
echo '</div>';  
include('footer.php');

?>