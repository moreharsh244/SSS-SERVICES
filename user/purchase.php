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

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --accent-color: #f43f5e;
        --bg-surface: #f8fafc;
        --card-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
        --border-color: #e2e8f0;
        --text-dark: #0f172a;
    }

    body {
        background-color: var(--bg-surface);
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-dark);
    }

    /* Page Layout */
    .checkout-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .page-title {
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 5px;
        background: -webkit-linear-gradient(45deg, #1e293b, #4f46e5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Left Section: Details & Payment */
    .details-card {
        background: white;
        border-radius: 24px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid white;
        height: 100%;
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px dashed var(--border-color);
    }
    
    .section-icon {
        width: 40px; height: 40px;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 15px;
    }

    .section-label {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
    }

    /* Product Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-box {
        background: #f8fafc;
        padding: 15px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 5px;
        letter-spacing: 0.5px;
    }

    .info-content {
        font-weight: 600;
        color: #0f172a;
        font-size: 1rem;
    }

    /* Custom Radio Cards for Payment */
    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .payment-option-input { display: none; }

    .payment-card {
        cursor: pointer;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .payment-card:hover {
        border-color: #a5b4fc;
        transform: translateY(-3px);
    }

    .payment-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #64748b;
        transition: color 0.3s;
    }

    .payment-text {
        font-weight: 600;
        color: #475569;
        display: block;
    }

    /* Checked State */
    .payment-option-input:checked + .payment-card {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.2);
    }
    
    .payment-option-input:checked + .payment-card .payment-icon {
        color: #4f46e5;
        transform: scale(1.1);
    }
    
    .payment-option-input:checked + .payment-card .payment-text {
        color: #4338ca;
    }

    .check-circle {
        position: absolute;
        top: 10px; right: 10px;
        color: #4f46e5;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s;
    }
    
    .payment-option-input:checked + .payment-card .check-circle {
        opacity: 1;
        transform: scale(1);
    }

    /* Payment Ref Input */
    #online_payment_fields {
        display: none;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        padding: 20px;
        border-radius: 16px;
        margin-top: 10px;
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Right Section: Summary */
    .summary-card {
        background: white;
        border-radius: 24px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid white;
        position: sticky;
        top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 0.95rem;
        color: #64748b;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed var(--border-color);
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
    }

    .btn-checkout {
        background: var(--primary-grad);
        color: white;
        width: 100%;
        padding: 16px;
        border-radius: 14px;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: 0.5px;
        margin-top: 25px;
        transition: all 0.3s;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    }

    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5);
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .payment-grid { grid-template-columns: 1fr; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="checkout-container">
    
    <div class="mb-5 text-center text-md-start">
        <h1 class="page-title">Secure Checkout</h1>
        <p class="text-muted">Completing purchase for <strong class="text-dark"><?php echo htmlspecialchars($username); ?></strong></p>
    </div>

    <form action="purchase_order.php" method="post" id="purchaseForm">
        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
        <input type="hidden" name="pname" value="<?php echo htmlspecialchars($pname); ?>">
        <input type="hidden" name="pprice" value="<?php echo htmlspecialchars($pprice); ?>">
        <input type="hidden" name="qty" value="<?php echo htmlspecialchars($qty); ?>">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">

        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="details-card">
                    
                    <div class="section-header">
                        <div class="section-icon"><i class="bi bi-box-seam-fill"></i></div>
                        <div class="section-label">Order Details</div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box" style="grid-column: 1 / -1;">
                            <div class="info-label">Product Name</div>
                            <div class="info-content text-primary"><?php echo htmlspecialchars($pname); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Unit Price</div>
                            <div class="info-content">₹<?php echo number_format((float)$pprice, 2); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Quantity</div>
                            <div class="info-content">x <?php echo (int)$qty; ?> Units</div>
                        </div>
                    </div>

                    <div class="section-header mt-5">
                        <div class="section-icon" style="color: #10b981; background: #ecfdf5;"><i class="bi bi-wallet2"></i></div>
                        <div class="section-label">Payment Method</div>
                    </div>

                    <div class="payment-grid">
                        <label>
                            <input type="radio" name="payment_method" value="online" class="payment-option-input" id="pay_online" checked>
                            <div class="payment-card">
                                <i class="bi bi-check-circle-fill check-circle"></i>
                                <i class="bi bi-qr-code-scan payment-icon"></i>
                                <span class="payment-text">UPI / Online Pay</span>
                            </div>
                        </label>

                        <label>
                            <input type="radio" name="payment_method" value="cod" class="payment-option-input" id="pay_cod">
                            <div class="payment-card">
                                <i class="bi bi-check-circle-fill check-circle"></i>
                                <i class="bi bi-cash-stack payment-icon"></i>
                                <span class="payment-text">Cash on Delivery</span>
                            </div>
                        </label>
                    </div>

                    <div id="online_payment_fields">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i class="bi bi-receipt me-1"></i> Transaction Reference ID
                        </label>
                        <input type="text" name="payment_ref" class="form-control form-control-lg bg-white border-warning" placeholder="Enter UPI Ref No / Transaction ID">
                        <div class="small text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i> Please complete payment via QR code and enter the reference number here.
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h4 class="fw-bold mb-4">Order Summary</h4>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="text-dark fw-bold">₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (GST included)</span>
                        <span class="text-success fw-bold">₹0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="text-success fw-bold">Free</span>
                    </div>

                    <div class="total-row">
                        <span>Total Pay</span>
                        <span style="color: #4f46e5;">₹<?php echo number_format($total, 2); ?></span>
                    </div>

                    <button type="submit" class="btn-checkout">
                        Confirm Purchase <i class="bi bi-arrow-right ms-2"></i>
                    </button>

                    <div class="mt-4 text-center">
                        <small class="text-muted d-block mb-2"><i class="bi bi-shield-lock-fill text-success me-1"></i> 100% Secure Payment</small>
                        <div class="d-flex justify-content-center gap-2 opacity-50">
                            <i class="bi bi-credit-card fs-5"></i>
                            <i class="bi bi-paypal fs-5"></i>
                            <i class="bi bi-bank fs-5"></i>
                        </div>
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
            } else {
                paymentFields.style.display = 'none';
                refInput.required = false;
                refInput.value = ''; 
            }
        }

        onlineRadio.addEventListener('change', togglePaymentFields);
        codRadio.addEventListener('change', togglePaymentFields);

        // Initial run
        togglePaymentFields();
    });
</script>

<?php include('footer.php'); ?>