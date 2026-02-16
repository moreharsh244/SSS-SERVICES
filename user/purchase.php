<?php
define('page','purchase');
include('header.php');

// Logic remains the same
$pid = isset($_POST['pid']) ? $_POST['pid'] : '';
$pname = isset($_POST['pname']) ? $_POST['pname'] : 'Unknown Product';
$pprice = isset($_POST['pprice']) ? $_POST['pprice'] : 0;
$qty = isset($_POST['qty']) ? $_POST['qty'] : 1;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$total = (float)$pprice * (int)$qty;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5; /* Indigo */
        --primary-hover: #4338ca;
        --bg-color: #f3f4f6;
        --card-border: #e5e7eb;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Left Side - Main Section */
    .checkout-section {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--card-border);
        overflow: hidden;
    }

    .section-title {
        font-weight: 700;
        color: #111827;
        margin-bottom: 20px;
        font-size: 1.25rem;
    }

    .info-group {
        background-color: #f9fafb;
        border-radius: 12px;
        padding: 15px;
        border: 1px solid var(--card-border);
        margin-bottom: 20px;
    }

    .info-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 1rem;
        color: #1f2937;
        font-weight: 500;
    }

    /* Custom Payment Radio Cards */
    .payment-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .payment-option-input {
        display: none; /* Hide default radio */
    }

    .payment-card {
        cursor: pointer;
        border: 2px solid var(--card-border);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: white;
        height: 100%;
    }

    .payment-card:hover {
        border-color: #c7d2fe;
        background-color: #eef2ff;
    }

    .payment-card i {
        font-size: 1.5rem;
        margin-bottom: 8px;
        color: #6b7280;
    }

    /* Style for when radio is checked */
    .payment-option-input:checked + .payment-card {
        border-color: var(--primary-color);
        background-color: #eef2ff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .payment-option-input:checked + .payment-card i,
    .payment-option-input:checked + .payment-card span {
        color: var(--primary-color);
        font-weight: 600;
    }

    /* Right Side - Summary Receipt */
    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--card-border);
        position: sticky;
        top: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        color: #4b5563;
    }

    .summary-divider {
        border-top: 2px dashed var(--card-border);
        margin: 20px 0;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.25rem;
        font-weight: 800;
        color: #111827;
    }

    .btn-checkout {
        background-color: var(--primary-color);
        border: none;
        padding: 14px;
        font-weight: 600;
        border-radius: 10px;
        transition: background 0.2s;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
    }

    .btn-checkout:hover {
        background-color: var(--primary-hover);
        transform: translateY(-1px);
    }
    
    /* Payment Ref Input Animation */
    #online_payment_fields {
        display: none;
        margin-top: 20px;
        animation: slideDown 0.3s ease-out forwards;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container py-5">
    
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Secure Checkout</h2>
        <p class="text-muted">Complete your purchase for <strong><?php echo htmlspecialchars($username); ?></strong></p>
    </div>

    <form action="purchase_order.php" method="post" id="purchaseForm">
        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
        <input type="hidden" name="pname" value="<?php echo htmlspecialchars($pname); ?>">
        <input type="hidden" name="pprice" value="<?php echo htmlspecialchars($pprice); ?>">
        <input type="hidden" name="qty" value="<?php echo htmlspecialchars($qty); ?>">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="checkout-section p-4">
                    
                    <h4 class="section-title"><i class="bi bi-bag-check me-2"></i>Order Details</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Product Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($pname); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-group">
                                <div class="info-label">Price</div>
                                <div class="info-value">₹<?php echo number_format((float)$pprice, 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-group">
                                <div class="info-label">Quantity</div>
                                <div class="info-value">x <?php echo (int)$qty; ?></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <h4 class="section-title"><i class="bi bi-credit-card me-2"></i>Payment Method</h4>
                    
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="online" class="payment-option-input" id="pay_online" checked>
                            <div class="payment-card">
                                <i class="bi bi-qr-code-scan"></i>
                                <span>UPI / Online</span>
                            </div>
                        </label>

                        <label>
                            <input type="radio" name="payment_method" value="cod" class="payment-option-input" id="pay_cod">
                            <div class="payment-card">
                                <i class="bi bi-cash-stack"></i>
                                <span>Cash on Delivery</span>
                            </div>
                        </label>
                    </div>

                    <div id="online_payment_fields" class="bg-light p-3 rounded-3 border">
                        <label class="form-label fw-bold text-primary">Transaction Reference / ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-hash"></i></span>
                            <input type="text" name="payment_ref" class="form-control border-start-0 ps-0" placeholder="e.g., UPI Ref No: 1234567890">
                        </div>
                        <small class="text-muted mt-1 d-block">Please enter the Transaction ID after completing the payment.</small>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="fw-bold mb-4">Summary</h5>
                    
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Tax / Fees</span>
                        <span class="text-success">Free</span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span class="text-muted">₹0.00</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="total-row">
                        <span>Total</span>
                        <span class="text-primary">₹<?php echo number_format($total, 2); ?></span>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-checkout w-100 text-white shadow-sm">
                            Confirm Purchase <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> SSL Secured Payment</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const onlineRadio = document.getElementById('pay_online');
        const codRadio = document.getElementById('pay_cod');
        const paymentFields = document.getElementById('online_payment_fields');
        const refInput = paymentFields.querySelector('input[name="payment_ref"]');

        function togglePaymentFields() {
            if (onlineRadio.checked) {
                paymentFields.style.display = 'block';
                refInput.required = true;
                // Optional: Scroll to fields on mobile
                if(window.innerWidth < 768) {
                    paymentFields.scrollIntoView({behavior: "smooth", block: "center"});
                }
            } else {
                paymentFields.style.display = 'none';
                refInput.required = false;
                refInput.value = ''; // Clear value when switching
            }
        }

        onlineRadio.addEventListener('change', togglePaymentFields);
        codRadio.addEventListener('change', togglePaymentFields);

        // Initial check
        togglePaymentFields();
    });
</script>

<?php include('footer.php'); ?>