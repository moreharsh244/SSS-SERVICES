<?php
define('page', 'myorder_details');
include('header.php');
include('../admin/conn.php');

$order_id = intval($_POST['order_id'] ?? 0);
$row = false;
$is_history = false;

// Build current user list for safety
$sessionUser = $_SESSION['username'] ?? '';
$sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
$sessionUid = $_SESSION['user_id'] ?? null;
$possibleUsers = [$sessionUserEsc];
if (!empty($sessionUid)) $possibleUsers[] = 'user_' . intval($sessionUid);
$userList = "'" . implode("','", array_map(function ($v) {
    return mysqli_real_escape_string($GLOBALS['con'], $v);
}, $possibleUsers)) . "'";

// Database Fetch Logic
if ($order_id > 0) {
    // Try active purchases first
    $sql = "SELECT * FROM `purchase` INNER JOIN products ON purchase.prod_id=products.pid WHERE purchase.pid='$order_id' AND purchase.user IN ({$userList}) LIMIT 1";
    $result = @mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $is_history = false;
    } else {
        // Fallback to history table
        $db = '';
        $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
        if ($rdb && mysqli_num_rows($rdb) > 0) {
            $db = mysqli_fetch_assoc($rdb)['dbname'];
        }
        if ($db) {
            $tbl = mysqli_real_escape_string($con, 'purchase_history');
            $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='" . mysqli_real_escape_string($con, $db) . "' AND TABLE_NAME='{$tbl}' LIMIT 1");
            if ($qc && mysqli_num_rows($qc) > 0) {
                $sql2 = "SELECT * FROM `purchase_history` INNER JOIN products ON purchase_history.prod_id=products.pid WHERE purchase_history.pid='$order_id' AND purchase_history.user IN ({$userList}) LIMIT 1";
                $res2 = @mysqli_query($con, $sql2);
                if ($res2 && mysqli_num_rows($res2) > 0) {
                    $row = mysqli_fetch_assoc($res2);
                    $is_history = true;
                }
            }
        }
    }
}

// If order not found
if (!$row) {
    echo '<div class="container mt-5"><div class="alert alert-warning shadow-sm border-0"><i class="fas fa-exclamation-triangle"></i> Order not found.</div></div>';
    include('footer.php');
    exit;
}

// Status Logic Processing
$status_value = strtolower(trim($row['status'] ?? ''));
$delivery_value = strtolower(trim($row['delivery_status'] ?? ''));
if ($status_value === 'pending') $status_value = 'order_confirmed';
if ($delivery_value === 'pending') $delivery_value = 'order_confirmed';
if ($status_value === 'shipped') $status_value = 'out_for_delivery';
if ($delivery_value === 'shipped') $delivery_value = 'out_for_delivery';

$status_label = ucwords(str_replace('_', ' ', ($status_value ?: 'order_confirmed')));
$delivery_label = ucwords(str_replace('_', ' ', ($delivery_value ?: $status_value ?: 'order_confirmed')));

// Determine Badge Colors based on status
function getBadgeColor($status) {
    $s = strtolower($status);
    if (strpos($s, 'confirm') !== false) return 'bg-info';
    if (strpos($s, 'delivery') !== false || strpos($s, 'ship') !== false) return 'bg-warning text-dark';
    if (strpos($s, 'deliver') !== false || strpos($s, 'success') !== false) return 'bg-success';
    if (strpos($s, 'cancel') !== false) return 'bg-danger';
    return 'bg-primary';
}

$status_badge = getBadgeColor($status_label);
$delivery_badge = getBadgeColor($delivery_label);

$total = $row['pprice'] * $row['qty'];

// Cancel Logic
$delivery_state = strtolower(trim($row['delivery_status'] ?? ''));
if ($delivery_state === 'pending') $delivery_state = 'order_confirmed';
$can_cancel = !$is_history && in_array($delivery_state, ['pending', 'order_confirmed'], true);

// Include FontAwesome for icons if not already in header.php
?>
<style>
    .order-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    .order-card:hover {
        transform: translateY(-5px);
    }
    .order-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 25px;
        border-bottom: none;
    }
    .order-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #eee;
    }
    .order-detail-row:last-child {
        border-bottom: none;
    }
    .icon-box {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #f3f4f6;
        color: #764ba2;
        margin-right: 15px;
    }
    .detail-label {
        font-weight: 600;
        color: #555;
        display: flex;
        align-items: center;
    }
    .detail-value {
        font-weight: 500;
        color: #222;
        text-align: right;
    }
    .total-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        border-left: 5px solid #667eea;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            
            <div class="card order-card">
                <div class="card-header order-header-gradient text-white text-center">
                    <h3 class="mb-1 fw-bold"><i class="fas fa-receipt me-2"></i> Order Details</h3>
                    <p class="mb-0 text-light opacity-75">Order ID: #<?= str_pad($order_id, 6, "0", STR_PAD_LEFT) ?></p>
                </div>
                
                <div class="card-body p-4">
                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-calendar-alt"></i></div>
                            Purchase Date
                        </span>
                        <span class="detail-value"><?= htmlspecialchars($row['pdate']) ?></span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-box-open"></i></div>
                            Product Name
                        </span>
                        <span class="detail-value text-primary fw-bold"><?= htmlspecialchars($row['pname']) ?></span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-industry"></i></div>
                            Company
                        </span>
                        <span class="detail-value"><?= htmlspecialchars($row['pcompany'] ?? '-') ?></span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-sort-numeric-up-alt"></i></div>
                            Quantity
                        </span>
                        <span class="detail-value"><?= htmlspecialchars($row['qty']) ?></span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-tag"></i></div>
                            Unit Price
                        </span>
                        <span class="detail-value">₹<?= number_format((float)$row['pprice'], 2) ?></span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-info-circle"></i></div>
                            Status
                        </span>
                        <span class="detail-value">
                            <span class="badge rounded-pill <?= $status_badge ?> px-3 py-2"><?= htmlspecialchars($status_label) ?></span>
                        </span>
                    </div>

                    <div class="order-detail-row">
                        <span class="detail-label">
                            <div class="icon-box"><i class="fas fa-shipping-fast"></i></div>
                            Delivery Status
                        </span>
                        <span class="detail-value">
                            <span class="badge rounded-pill <?= $delivery_badge ?> px-3 py-2"><?= htmlspecialchars($delivery_label) ?></span>
                        </span>
                    </div>

                    <div class="total-box d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-secondary">Total Amount</h4>
                        <h3 class="mb-0 text-success fw-bold">₹<?= number_format((float)$total, 2) ?></h3>
                    </div>

                    <?php if ($can_cancel): ?>
                    <div class="mt-4 text-center">
                        <form action="cancel_order.php" method="post" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                            <input type="hidden" name="order_id" value="<?= intval($order_id) ?>">
                            <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 shadow-sm">
                                <i class="fas fa-times-circle me-2"></i> Cancel Order
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include('footer.php'); ?>