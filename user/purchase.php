<?php
define('page','purchase');
include('header.php');
    $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
    $pname = isset($_POST['pname']) ? $_POST['pname'] : '';
    $pprice = isset($_POST['pprice']) ? $_POST['pprice'] : 0;
    $qty = isset($_POST['qty']) ? $_POST['qty'] : 1;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $total = (float)$pprice * (int)$qty;
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Purchase Summary</h4>
                </div>
                <div class="card-body">
                    <form action="purchase_order.php" method="post">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="form-control-plaintext" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" name="pname" value="<?php echo htmlspecialchars($pname); ?>" class="form-control-plaintext" readonly>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label class="form-label">Price</label>
                                        <input type="text" name="pprice" value="<?php echo htmlspecialchars($pprice); ?>" class="form-control-plaintext" readonly>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Quantity</label>
                                        <input type="text" name="qty" value="<?php echo htmlspecialchars($qty); ?>" class="form-control-plaintext" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="mb-3">Order Summary</h5>
                                        <p class="mb-1"><strong>Unit Price:</strong> <span class="float-end">₹ <?php echo number_format((float)$pprice,2); ?></span></p>
                                        <p class="mb-1"><strong>Quantity:</strong> <span class="float-end"><?php echo (int)$qty; ?></span></p>
                                    </div>
                                    <div>
                                        <hr>
                                        <h4 class="text-success">Total: ₹ <?php echo number_format($total,2); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-4 btn-lg w-100">Confirm Purchase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php   

include('footer.php');
?>