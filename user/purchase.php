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
            <div class="card shadow-sm reveal">
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
                        <hr class="my-4">
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_online" value="online" checked>
                                    <label class="form-check-label" for="pay_online">Online Payment</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod">
                                    <label class="form-check-label" for="pay_cod">Cash on Delivery</label>
                                </div>
                            </div>
                        </div>
                        <div id="online_payment_fields" class="p-3 bg-light rounded">
                            <div class="mb-3">
                                <label class="form-label">Payment Reference (UPI/Txn ID)</label>
                                <input type="text" name="payment_ref" class="form-control" placeholder="Enter payment reference">
                                <div class="form-text">Provide a reference so we can verify the payment.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-4 btn-lg w-100">Confirm Purchase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function(){
            var online = document.getElementById('pay_online');
            var cod = document.getElementById('pay_cod');
            var fields = document.getElementById('online_payment_fields');
            var refInput = fields.querySelector('input[name="payment_ref"]');

            function toggleFields(){
                if(online.checked){
                    fields.style.display = 'block';
                    refInput.required = true;
                }else{
                    fields.style.display = 'none';
                    refInput.required = false;
                    refInput.value = '';
                }
            }

            online.addEventListener('change', toggleFields);
            cod.addEventListener('change', toggleFields);
            toggleFields();
        })();
    </script>
<?php   

include('footer.php');
?>