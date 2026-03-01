<?php
include(__DIR__ . '/header.php');
include(__DIR__ . '/conn.php');

// Ensure $view is always defined
$view = isset($_GET['view']) ? $_GET['view'] : 'list';
// --- CLEANED LOGIC START ---
$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }

$table_missing = false;
$res = false;

// Count Queries (store orders included)
$count_all = 0; $count_pending = 0; $count_assigned = 0; $count_history = 0;
$count_res = @mysqli_query($con, "SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(IFNULL(purchase.delivery_status,'order_confirmed')) IN ('pending','order_confirmed') THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN IFNULL(purchase.assigned_agent,'') <> '' THEN 1 ELSE 0 END) AS assigned FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid");
if($count_res){ $cr = mysqli_fetch_assoc($count_res); $count_all = $cr['total']??0; $count_pending = $cr['pending']??0; $count_assigned = $cr['assigned']??0; }

// Data Fetching: always join products for store orders
// Fetch delivery agents for dropdown
$agents = [];
$agent_res = mysqli_query($con, "SELECT username, full_name FROM del_login WHERE role='delivery' AND is_active=1 ORDER BY full_name ASC, username ASC");
if($agent_res && mysqli_num_rows($agent_res)>0){
    while($row = mysqli_fetch_assoc($agent_res)){
        $agents[$row['username']] = $row['full_name'] ? $row['full_name'] : $row['username'];
    }
}
if($view === 'history'){
    $q = "SELECT ph.*, products.pname AS prod_name, products.pimg AS prod_img FROM purchase_history ph LEFT JOIN products ON ph.prod_id = products.pid WHERE LOWER(IFNULL(ph.delivery_status,'')) IN ('delivered','cancelled') ORDER BY ph.pdate DESC";
    $res = mysqli_query($con, $q);
} elseif($view === 'pending'){
    $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid WHERE LOWER(IFNULL(purchase.delivery_status,'order_confirmed')) IN ('pending','order_confirmed') ORDER BY pdate DESC";
    $res = mysqli_query($con, $q);
} elseif($view === 'assigned'){
    $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid WHERE IFNULL(purchase.assigned_agent,'')<>'' AND LOWER(IFNULL(purchase.delivery_status,'pending')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
    $res = mysqli_query($con, $q);
} else {
    $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid WHERE LOWER(IFNULL(purchase.delivery_status,'')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
    $res = mysqli_query($con, $q);
}

// Exclude all products related to assigned builds from orders table
$excluded_pids = [];
$assigned_build_ids = [];
$builds_res = mysqli_query($con, "SELECT id FROM builds WHERE assigned_agent IS NOT NULL AND assigned_agent <> ''");
if($builds_res && mysqli_num_rows($builds_res) > 0){
    while($b = mysqli_fetch_assoc($builds_res)){
        $assigned_build_ids[] = (int)$b['id'];
    }
}
if(!empty($assigned_build_ids)){
    // Get all product_ids for assigned builds
    $build_items_res = mysqli_query($con, "SELECT product_id FROM build_items WHERE build_id IN (".implode(',', $assigned_build_ids).")");
    if($build_items_res && mysqli_num_rows($build_items_res) > 0){
        while($bi = mysqli_fetch_assoc($build_items_res)){
            $excluded_pids[] = (int)$bi['product_id'];
        }
    }
}
// Also exclude purchase orders for these product_ids
if(!empty($excluded_pids)){
    $excluded_pids = array_unique($excluded_pids);
}
?>
<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background:
        radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
        radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
        radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
        linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
}
.table-fixed { table-layout: fixed; width: 100%; }
.col-product  { width: 32%; }
.col-customer { width: 20%; }
.col-status   { width: 15%; }
.col-finance  { width: 15%; }
.col-action   { width: 18%; }
.table td { vertical-align: middle; padding: 12px 10px; white-space: normal; word-wrap: break-word; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; }
.prod-cell { display: flex; align-items: center; gap: 10px; }
.prod-img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #eee; }
.prod-info { overflow: hidden; }
.prod-name { font-weight: 600; color: #333; line-height: 1.2; margin-bottom: 2px; display: block;}
.prod-meta { font-size: 0.75rem; color: #888; }
.info-primary { font-weight: 500; color: #222; display: block; line-height: 1.2; }
.info-secondary { font-size: 0.75rem; color: #888; display: block; margin-top: 2px; }
.badge-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
.status-pill { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border: 1px solid #e9ecef; color: #555; margin-bottom: 4px; }
.status-pill.pending, .status-pill.order_confirmed { border-color: #ffeeba; background: #fff3cd; color: #856404; }
.status-pill.out_for_delivery, .status-pill.shipped { border-color: #dbeafe; background: #eff6ff; color: #1d4ed8; }
.status-pill.delivered { border-color: #c3e6cb; background: #d4edda; color: #155724; }
.status-pill.cancelled { border-color: #f5c6cb; background: #f8d7da; color: #721c24; }
.compact-form .form-select { font-size: 0.9rem; padding: 6px 28px 6px 10px; background-position: right 4px center; }
.compact-form .btn { padding: 6px 12px; }
.compact-form{ gap: 8px; }
.nav-tabs .nav-link { border: none; color: #666; font-size: 0.9rem; font-weight: 500; }
.nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 2px solid #0d6efd; background: transparent; }
.nav-tabs .nav-link:hover { color: #0d6efd; }
.orders-shell{ max-width: 1800px; margin: 0 auto; width: 100%; }
</style>
<div class="container-fluid py-5 px-3" style="min-height:90vh;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11 col-xl-10">
            <div class="card shadow-lg border-0 rounded-4 mb-5" style="background:rgba(255,255,255,0.97);">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2 rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0 fw-bold text-primary"><i class="bi bi-box-seam me-2"></i>Orders</h3>
                        <span class="badge bg-light text-dark border fs-6">Total: <?php echo $count_all; ?></span>
                    </div>
                    <ul class="nav nav-tabs mt-2">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $view==='list'?'active':''; ?>" href="orders_list.php">All</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $view==='pending'?'active':''; ?>" href="orders_list.php?view=pending">
                                Pending <span class="badge bg-danger rounded-pill ms-1" style="font-size:0.7em"><?php echo $count_pending; ?></span>
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
                                <th class="col-product text-uppercase text-secondary" style="font-size:0.85rem">Item Details</th>
                                <th class="col-customer text-uppercase text-secondary" style="font-size:0.85rem">Customer</th>
                                <th class="col-finance text-uppercase text-secondary" style="font-size:0.85rem">Total / Date</th>
                                <th class="col-status text-uppercase text-secondary" style="font-size:0.85rem">Status</th>
                                <th class="col-action text-uppercase text-secondary text-end" style="font-size:0.85rem">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($res && mysqli_num_rows($res) > 0) {
                                while($r = mysqli_fetch_assoc($res)){
                                    $pid = $r['pid'];
                                    $assigned = $r['assigned_agent'] ?? '';
                                    // If this is a build component and its build is assigned, skip
                                    if(in_array($r['prod_id'], $excluded_pids) || in_array($pid, $excluded_pids)) continue;
                                    $pname = htmlspecialchars($r['prod_name'] ?: $r['pname']);
                                    $img = (!empty($r['prod_img'])) ? '../productimg/'.rawurlencode($r['prod_img']) : '';
                                    $qty = $r['qty'];
                                    $price = $r['pprice'];
                                    $total = number_format($price * $qty, 2);
                                    $c_name = isset($r['customer_name']) && $r['customer_name'] ? htmlspecialchars($r['customer_name']) : (isset($r['user']) ? htmlspecialchars($r['user']) : 'Guest');
                                    $c_email = htmlspecialchars($r['user']);
                                    $d_status = strtolower($r['delivery_status'] ?? 'order_confirmed');
                                    if($d_status === 'pending') $d_status = 'order_confirmed';
                                    if($d_status === 'shipped') $d_status = 'out_for_delivery';
                                    $agent_name = '';
                                    $agent_created_by_admin = false;
                                    if($assigned) {
                                        $agent_res = mysqli_query($con, "SELECT full_name, role FROM del_login WHERE username='".mysqli_real_escape_string($con, $assigned)."' LIMIT 1");
                                        if($agent_res && ($agent_row = mysqli_fetch_assoc($agent_res))) {
                                            $agent_name = $agent_row['full_name'];
                                            if(isset($agent_row['role']) && $agent_row['role'] === 'delivery') {
                                                $agent_created_by_admin = true;
                                            }
                                        }
                                    }
                                    $date = date('d M, Y', strtotime($r['pdate']));
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
                                        <div class="text-truncate" style="font-size:0.8rem; color:#666;">
                                            <i class="bi bi-person-check-fill me-1"></i>
                                            <?php echo $agent_name ? htmlspecialchars($agent_name) : $assigned; ?>
                                            <?php if($agent_created_by_admin): ?>
                                                <span class="badge bg-info bg-opacity-10 text-info ms-2">Created by Admin</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size:0.8rem; color:#bbb;">Unassigned</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($view !== 'history' && $d_status !== 'delivered' && $d_status !== 'cancelled'): ?>
                                        <div class="d-flex justify-content-end gap-1 align-items-center">
                                            <form action="assign_delivery.php" method="post" class="compact-form d-flex gap-1">
                                                <input type="hidden" name="order_id" value="<?php echo $pid; ?>">
                                                <select name="assigned_agent" class="form-select form-select-sm" style="width: auto; max-width: 110px;">
                                                    <option value="" selected disabled>Agent..</option>
                                                    <?php foreach($agents as $uname => $fname): ?>
                                                        <option value="<?php echo htmlspecialchars($uname); ?>" <?php echo ($assigned == $uname)?'selected':''; ?>>
                                                            <?php echo htmlspecialchars($fname); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Assign">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form action="cancel_order.php" method="post" class="compact-form">
                                                <input type="hidden" name="order_id" value="<?php echo $pid; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel Order">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.9rem;">—</span>
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
</div>

<?php include(__DIR__ . '/footer.php'); ?>