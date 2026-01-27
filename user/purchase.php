<?php
define('page','purchase');
include('header.php');

    $pid=$_POST['pid'];
    $pname=$_POST['pname'];
    $pprice=$_POST['pprice'];
    $qty=$_POST['qty'];
    $username=$_SESSION['username'];
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Purchase Summary</h4>
                </div>
                <div class="card-body">
                    <form action="purchase_order.php" method="post">
                        <input type="hidden" name="pid" value="<?php echo $pid; ?>">
                    <p><strong>Username:</strong> 
                    <input type="text" name="username" value="<?php echo $username; ?>" readonly></p>
                    <p><strong>Product Name:</strong><input type="text" name="pname" value="<?php echo $pname; ?>" readonly></p>
                    <p><strong>Price:</strong> <input type="text" name="pprice" value="<?php echo $pprice; ?>" readonly></p>
                    <p><strong>Quantity:</strong> <input type="text" name="qty" value="<?php echo $qty; ?>" readonly></p>
                    <hr>
                    <h5>Total Amount: <?php echo $pprice * $qty; ?></h5>
                    <button type="submit" class="btn btn-success mt-3">Confirm Purchase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php   

include('footer.php');
?>