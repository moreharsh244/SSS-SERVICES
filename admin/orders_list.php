<?php
include('header.php');
include('conn.php');

$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';

// --- DATABASE LOGIC (Kept exactly as previously working) ---
// (Checking tables, agents, and handling view logic)
$create = "CREATE TABLE IF NOT EXISTS `purchase` (
    `pid` INT AUTO_INCREMENT PRIMARY KEY,
    `pname` VARCHAR(255) NOT NULL,
    `user` VARCHAR(255) NOT NULL,
    `pprice` DECIMAL(10,2) NOT NULL,
    `qty` INT NOT NULL DEFAULT 1,
    `prod_id` INT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `delivery_status` VARCHAR(50) DEFAULT 'pending',
    `assigned_agent` VARCHAR(100) DEFAULT NULL,
    `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($con, $create);

$col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase' AND COLUMN_NAME='assigned_agent'";
$col_res = mysqli_query($con, $col_check);
if(!$col_res || mysqli_num_rows($col_res)===0){
  @mysqli_query($con, "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

$agents = [];
$ares = mysqli_query($con, "SELECT username FROM del_login WHERE is_active=1 ORDER BY username");
if($ares){ while($ar = mysqli_fetch_assoc($ares)){ $agents[] = $ar['username']; } }

$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }

$table_missing = false;
$res = false;

// Count Queries
$count_all = 0; $count_pending = 0; $count_assigned = 0; $count_history = 0;
$count_res = @mysqli_query($con, "SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(IFNULL(delivery_status,'order_confirmed')) IN ('pending','order_confirmed') THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN IFNULL(assigned_agent,'') <> '' THEN 1 ELSE 0 END) AS assigned FROM purchase");
if($count_res){ $cr = mysqli_fetch_assoc($count_res); $count_all = $cr['total']??0; $count_pending = $cr['pending']??0; $count_assigned = $cr['assigned']??0; }

if($db){
  $tbl = 'purchase_history';
  $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$tbl' LIMIT 1");
  if($qc && mysqli_num_rows($qc)>0){
    $hc = @mysqli_query($con, "SELECT COUNT(*) AS total FROM purchase_history WHERE LOWER(IFNULL(delivery_status,'')) IN ('cancelled','delivered')");
    if($hc){ $count_history = mysqli_fetch_assoc($hc)['total']??0; }
  } else { $table_missing = true; }
}

// Data Fetching
if($view === 'history'){
  if(!$table_missing){
    $q = "SELECT h.*, p.pname AS prod_name, p.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase_history h LEFT JOIN products p ON h.prod_id = p.pid LEFT JOIN cust_reg c ON (h.user = c.c_email OR h.user = c.c_name) WHERE LOWER(IFNULL(h.delivery_status,'')) IN ('cancelled','delivered') ORDER BY pdate DESC";
    $res = @mysqli_query($con, $q);
  }
} elseif($view === 'pending'){
    $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) WHERE LOWER(IFNULL(purchase.delivery_status,'order_confirmed')) IN ('pending','order_confirmed') ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
} elseif($view === 'assigned'){
  $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) WHERE IFNULL(purchase.assigned_agent,'')<>'' AND LOWER(IFNULL(purchase.delivery_status,'pending')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
} else {
  $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }
    /* Fixed Table Layout to prevent scrolling */
    .table-fixed {
        table-layout: fixed;
        width: 100%;
    }
    
    /* Column Widths (Must add up to 100%) */
    .col-product  { width: 32%; }
    .col-customer { width: 20%; }
    .col-status   { width: 15%; }
    .col-finance  { width: 15%; }
    .col-action   { width: 18%; }

    /* Compact Cells */
    .table td {
        vertical-align: middle;
        padding: 12px 10px;
        white-space: normal; /* Allow text wrapping */
        word-wrap: break-word; /* Prevent overflow */
        font-size: 0.9rem;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Product Cell Styling */
    .prod-cell { display: flex; align-items: center; gap: 10px; }
    .prod-img {
        width: 40px; height: 40px;
        border-radius: 6px; object-fit: cover;
        flex-shrink: 0; /* Prevent image from shrinking */
        background: #eee;
    }
    .prod-info { overflow: hidden; }
    .prod-name { font-weight: 600; color: #333; line-height: 1.2; margin-bottom: 2px; display: block;}
    .prod-meta { font-size: 0.75rem; color: #888; }

    /* Customer & Finance Styling */
    .info-primary { font-weight: 500; color: #222; display: block; line-height: 1.2; }
    .info-secondary { font-size: 0.75rem; color: #888; display: block; margin-top: 2px; }

    /* Status Badges */
    .badge-dot {
        display: inline-block; width: 8px; height: 8px;
        border-radius: 50%; margin-right: 5px;
    }
    .status-pill {
        display: inline-flex; align-items: center;
        padding: 2px 8px; border-radius: 12px;
        font-size: 0.75rem; font-weight: 600;
        background: #f8f9fa; border: 1px solid #e9ecef; color: #555;
        margin-bottom: 4px;
    }
    .status-pill.pending, .status-pill.order_confirmed { border-color: #ffeeba; background: #fff3cd; color: #856404; }
    .status-pill.out_for_delivery, .status-pill.shipped { border-color: #dbeafe; background: #eff6ff; color: #1d4ed8; }
    .status-pill.delivered { border-color: #c3e6cb; background: #d4edda; color: #155724; }
    .status-pill.cancelled { border-color: #f5c6cb; background: #f8d7da; color: #721c24; }

    /* Action Form */
    .compact-form .form-select {
        font-size: 0.9rem; padding: 6px 28px 6px 10px;
        background-position: right 4px center; /* Move arrow closer */
    }
    .compact-form .btn { padding: 6px 12px; }
    .compact-form{ gap: 8px; }

    /* Tab Nav */
    .nav-tabs .nav-link { border: none; color: #666; font-size: 0.9rem; font-weight: 500; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 2px solid #0d6efd; background: transparent; }
    .nav-tabs .nav-link:hover { color: #0d6efd; }

    .orders-shell{
        max-width: 1800px;
        margin: 0 auto;
        width: 100%;
    }
</style>

<div class="container-fluid py-4 px-4">
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="orders-shell">
        <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Orders</h5>
                <span class="badge bg-light text-dark border">Total: <?php echo $count_all; ?></span>
            </div>
            
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $view==='list'?'active':''; ?>" href="orders_list.php">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $view==='pending'?'active':''; ?>" href="orders_list.php?view=pending">
                        Pending <span class="badge bg-danger rounded-pill ms-1" style="font-size:0.6em"><?php echo $count_pending; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $view==='assigned'?'active':''; ?>" href="orders_list.php?view=assigned">Assigned</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $view==='history'?'active':''; ?>" href="orders_list.php?view=history">History</a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <table class="table table-fixed mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="col-product text-uppercase text-secondary" style="font-size:0.75rem">Item Details</th>
                        <th class="col-customer text-uppercase text-secondary" style="font-size:0.75rem">Customer</th>
                        <th class="col-finance text-uppercase text-secondary" style="font-size:0.75rem">Total / Date</th>
                        <th class="col-status text-uppercase text-secondary" style="font-size:0.75rem">Status</th>
                        <th class="col-action text-uppercase text-secondary text-end" style="font-size:0.75rem">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($res && mysqli_num_rows($res) > 0) {
                        while($r = mysqli_fetch_assoc($res)){
                            $pid = $r['pid'];
                            $pname = htmlspecialchars($r['prod_name'] ?: $r['pname']);
                            $img = (!empty($r['prod_img'])) ? '../productimg/'.rawurlencode($r['prod_img']) : '';
                            $qty = $r['qty'];
                            $price = $r['pprice'];
                            $total = number_format($price * $qty, 2);
                            
                            $c_name = htmlspecialchars($r['customer_name'] ?: 'Guest');
                            $c_email = htmlspecialchars($r['user']);
                            
                            $d_status = strtolower($r['delivery_status'] ?? 'order_confirmed');
                            if($d_status === 'pending') $d_status = 'order_confirmed';
                            if($d_status === 'shipped') $d_status = 'out_for_delivery';
                            $assigned = htmlspecialchars($r['assigned_agent'] ?? '');
                            $date = date('d M, Y', strtotime($r['pdate']));

                            // Determine Status Class
                            $status_class = 'order_confirmed';
                            if($d_status == 'out_for_delivery') $status_class = 'out_for_delivery';
                            if($d_status == 'delivered') $status_class = 'delivered';
                            if($d_status == 'cancelled') $status_class = 'cancelled';
                            $status_label = ucwords(str_replace('_', ' ', $d_status));
                    ?>
                    <tr>
                        <td>
                            <div class="prod-cell">
                                <?php if($img): ?>
                                    <img src="<?php echo $img; ?>" class="prod-img" alt="img">
                                <?php else: ?>
                                    <div class="prod-img d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                                <div class="prod-info">
                                    <span class="prod-name text-truncate"><?php echo $pname; ?></span>
                                    <span class="prod-meta">#<?php echo $pid; ?> &bull; Qty: <?php echo $qty; ?></span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="info-primary text-truncate"><?php echo $c_name; ?></span>
                            <span class="info-secondary text-truncate" title="<?php echo $c_email; ?>"><?php echo $c_email; ?></span>
                        </td>

                        <td>
                            <span class="info-primary">₹<?php echo $total; ?></span>
                            <span class="info-secondary"><?php echo $date; ?></span>
                        </td>

                        <td>
                            <div class="status-pill <?php echo $status_class; ?>">
                                <?php echo $status_label; ?>
                            </div>
                            <?php if($assigned): ?>
                                <div class="text-truncate" style="font-size:0.75rem; color:#666;">
                                    <i class="bi bi-person-check-fill me-1"></i><?php echo $assigned; ?>
                                </div>
                            <?php else: ?>
                                <div style="font-size:0.75rem; color:#bbb;">Unassigned</div>
                            <?php endif; ?>
                        </td>

                        <td class="text-end">
                            <?php if($view !== 'history' && $d_status !== 'delivered' && $d_status !== 'cancelled'): ?>
                                <div class="d-flex justify-content-end gap-1 align-items-center">
                                    <form action="assign_delivery.php" method="post" class="compact-form d-flex gap-1">
                                        <input type="hidden" name="order_id" value="<?php echo $pid; ?>">
                                        <select name="assigned_agent" class="form-select form-select-sm" style="width: auto; max-width: 110px;">
                                            <option value="" selected disabled>Agent..</option>
                                            <?php foreach($agents as $ag): ?>
                                                <option value="<?php echo htmlspecialchars($ag); ?>" <?php echo ($assigned == $ag)?'selected':''; ?>>
                                                    <?php echo htmlspecialchars($ag); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Assign">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form action="cancel_order.php" method="post" class="compact-form" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                        <input type="hidden" name="order_id" value="<?php echo $pid; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel Order">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center py-5 text-muted">No orders found in this category.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/../footer.php'); ?>