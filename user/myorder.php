<?php
define('page','myorder');
include('header.php');

// Basic view selection
$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';
$is_history = ($view === 'history');
$is_service = ($view === 'service');

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root{ --primary-grad: linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%); --bg-surface:#f8fafc; --card-shadow: 0 10px 30px -5px rgba(0,0,0,0.08);} 
.orders-card{background:#fff;border-radius:14px;padding:18px;border:1px solid #eef2ff;box-shadow:var(--card-shadow);} 
.nav-link-custom{display:inline-block;padding:8px 14px;margin-right:8px;border-radius:999px;color:#64748b;text-decoration:none;font-weight:600}
.nav-link-custom.active{background:var(--primary-grad);color:#fff}
.custom-table thead th{background:linear-gradient(90deg,#f5f3ff 0%,#e0f2fe 100%);font-weight:600;font-size:0.85rem;text-transform:uppercase}
.badge-soft{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.82rem;font-weight:700;line-height:1}
.badge-order_confirmed,.badge-pending{background:rgba(245,158,11,0.16);color:#92400e}
.badge-accepted,.badge-in_progress{background:rgba(14,165,233,0.16);color:#0c4a6e}
.badge-out_for_delivery,.badge-shipped{background:rgba(59,130,246,0.16);color:#1d4ed8}
.badge-delivered,.badge-completed{background:rgba(34,197,94,0.16);color:#166534}
.badge-cancelled{background:rgba(239,68,68,0.16);color:#991b1b}
.btn-icon{border:none;background:#f1f5f9;padding:8px;border-radius:8px}
.btn-icon:hover{background:#eef2ff}
</style>

<div class="container pb-5">
    <div class="orders-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a class="nav-link-custom <?php echo !$is_service && !$is_history ? 'active' : ''; ?>" href="myorder.php">Active</a>
                <a class="nav-link-custom <?php echo $is_history ? 'active' : ''; ?>" href="myorder.php?view=history">History</a>
                <a class="nav-link-custom <?php echo $is_service ? 'active' : ''; ?>" href="myorder.php?view=service">Support</a>
            </div>
            <a class="btn btn-outline-dark rounded-pill ms-2 px-4 fw-bold" href="view_products.php">Shop <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="table-responsive">

<?php if($is_service): ?>
            <table class="table custom-table mb-0">
                <thead>
                    <tr>
                        <th>Request Details</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Admin Response</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php
include('../admin/conn.php');
$sessionUser = $_SESSION['username'] ?? '';
$sessionUid = $_SESSION['user_id'] ?? null;
$possibleUsers = [$sessionUser];
if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
$userList = "'".implode("','", array_map(function($v){ global $con; return mysqli_real_escape_string($con,$v); }, $possibleUsers))."'";

$service_rows = [];
$res = mysqli_query($con, "SELECT * FROM service_requests WHERE `user` IN ({$userList})");
if($res && mysqli_num_rows($res)>0){
    while($r = mysqli_fetch_assoc($res)){
        $service_rows[] = $r;
    }
}

// Include completed/cancelled requests archived by delivery/admin flows
$history_exists = false;
$hcq = mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='service_requests_history' LIMIT 1");
if($hcq && mysqli_num_rows($hcq)>0){
    $history_exists = true;
}
if($history_exists){
    $hres = mysqli_query($con, "SELECT * FROM service_requests_history WHERE `user` IN ({$userList})");
    if($hres && mysqli_num_rows($hres)>0){
        while($hr = mysqli_fetch_assoc($hres)){
            $service_rows[] = $hr;
        }
    }
}

if(!empty($service_rows)){
    usort($service_rows, function($a, $b){
        $at = strtotime($a['created_at'] ?? '1970-01-01');
        $bt = strtotime($b['created_at'] ?? '1970-01-01');
        return $bt <=> $at;
    });

    foreach($service_rows as $r){
        $status = strtolower(trim($r['status'] ?? 'pending'));
        $status_label = ucfirst(str_replace('_',' ',$status));
        $assigned_agent = trim($r['assigned_agent'] ?? '');
        $agent_note = trim($r['agent_note'] ?? '');
        $response = $assigned_agent ? 'Agent: '.htmlspecialchars($assigned_agent) : 'Pending Review';
        if($agent_note) $response .= ' - '.htmlspecialchars(mb_strimwidth($agent_note,0,50,'...'));
?>
    <tr>
        <td>
            <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['item'] ?? ''); ?></div>
            <div class="small text-muted">ID: #SR-<?php echo (int)$r['id']; ?></div>
        </td>
        <td><span class="fw-semibold text-primary"><?php echo htmlspecialchars($r['service_type'] ?? ''); ?></span></td>
        <td><span class="badge-soft badge-<?php echo $status; ?>"><?php echo $status_label; ?></span></td>
        <td class="text-muted small"><?php echo $response; ?></td>
        <td><?php echo !empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : '-'; ?></td>
        <td class="text-end">
            <?php if($status==='pending' && empty($assigned_agent)): ?>
                <form action="cancel_order.php" method="post" class="d-inline">
                    <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                    <button type="submit" class="btn-icon btn-cancel" title="Cancel Request"><i class="bi bi-x-lg"></i></button>
                </form>
            <?php else: ?>
                <button class="btn-icon btn-view" disabled style="opacity:0.5; cursor:not-allowed;"><i class="bi bi-lock"></i></button>
            <?php endif; ?>
        </td>
    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='6'><div class='empty-state'><i class='bi bi-tools empty-icon'></i><h5>No service requests found</h5></div></td></tr>";
}
?>
                </tbody>
            </table>

<?php else: ?>
            <table class="table custom-table mb-0">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php
include('../admin/conn.php');
$sessionUser = $_SESSION['username'] ?? '';
$sessionUid = $_SESSION['user_id'] ?? null;
$possibleUsers = [$sessionUser];
if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
$userList = "'".implode("','", array_map(function($v){ global $con; return mysqli_real_escape_string($con,$v); }, $possibleUsers))."'";

// Load builds (history includes builds_history)
$builds = [];
$seen_build_ids = [];
if($is_history){
    $build_q = "SELECT * FROM builds_history WHERE user_name IN ({$userList}) OR user_id IN (".intval($sessionUid).") ORDER BY completed_at DESC";
    $build_res = mysqli_query($con, $build_q);
    if($build_res && mysqli_num_rows($build_res)>0){ while($b = mysqli_fetch_assoc($build_res)){ if(empty($b['created_at']) && !empty($b['completed_at'])) $b['created_at'] = $b['completed_at']; $builds[]=$b; $seen_build_ids[]=intval($b['id']); } }
    $active_build_q = "SELECT * FROM builds WHERE (user_name IN ({$userList}) OR user_id IN ('".intval($sessionUid)."')) AND LOWER(IFNULL(status,'')) IN ('delivered','completed') ORDER BY created_at DESC";
    $active_res = mysqli_query($con, $active_build_q);
    if($active_res && mysqli_num_rows($active_res)>0){ while($ab = mysqli_fetch_assoc($active_res)){ $aid=intval($ab['id']); if(in_array($aid,$seen_build_ids,true)) continue; if(empty($ab['created_at']) && !empty($ab['completed_at'])) $ab['created_at']=$ab['completed_at']; $builds[]=$ab; $seen_build_ids[]=$aid; } }
} else {
    $build_q = "SELECT * FROM builds WHERE user_name IN ({$userList}) OR user_id IN ('".intval($sessionUid)."') ORDER BY created_at DESC";
    $build_res = mysqli_query($con, $build_q);
    if($build_res && mysqli_num_rows($build_res)>0){ while($b = mysqli_fetch_assoc($build_res)){ $builds[]=$b; } }
}

// Render builds
if(count($builds)>0){
    foreach($builds as $build){
        $status = strtolower($build['status'] ?? 'pending');
        $assigned_agent = $build['assigned_agent'] ?? '';
        if($is_history && !in_array($status,['completed','delivered'],true)) continue;
        if(!$is_history && in_array($status,['completed','delivered'],true)) continue;
        $can_cancel_build = (!$is_history) && empty(trim((string)$assigned_agent)) && !in_array($status, ['out_for_delivery','delivered','completed','cancelled'], true);
        $badge_class = 'badge-order_confirmed'; $status_label='Order Confirmed'; $icon='bi-hourglass-split';
        if($status==='out_for_delivery'||($status==='accepted' && $assigned_agent)){ $badge_class='badge-out_for_delivery'; $status_label='Out for Delivery'; $icon='bi-truck'; }
        elseif($status==='accepted'){ $badge_class='badge-accepted'; $status_label='Accepted'; $icon='bi-check-circle-fill'; }
        elseif($status==='delivered'){ $badge_class='badge-delivered'; $status_label='Delivered'; $icon='bi-check-circle-fill'; }
        elseif($status==='completed'){ $badge_class='badge-delivered'; $status_label='Completed'; $icon='bi-check-circle-fill'; }
        elseif($status==='cancelled'){ $badge_class='badge-cancelled'; $status_label='Cancelled'; $icon='bi-x-circle-fill'; }
        $total = number_format((float)($build['total'] ?? 0),2);
?>
    <tr>
        <td colspan="2">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:45px;height:45px;"><i class="bi bi-pc-display text-primary fs-5"></i></div>
                <div>
                    <div class="fw-bold text-dark">Build: <?php echo htmlspecialchars($build['name']); ?></div>
                    <div class="small text-muted">ID: #BUILD-<?php echo $build['id']; ?></div>
                </div>
            </div>
        </td>
        <td>-</td>
        <td class="fw-bold text-primary">₹<?php echo $total; ?></td>
        <td><span class="badge-soft <?php echo $badge_class; ?>"> <i class="bi <?php echo $icon; ?>"></i> <?php echo $status_label; ?></span></td>
        <td class="text-end">
            <div class="d-inline-flex gap-2">
                <form action="myorder_details.php" method="post"><input type="hidden" name="build_id" value="<?php echo $build['id']; ?>"><button type="submit" class="btn-icon btn-view"><i class="bi bi-eye"></i></button></form>
                <?php if($can_cancel_build): ?>
                    <form action="cancel_order.php" method="post"><input type="hidden" name="build_id" value="<?php echo $build['id']; ?>"><button type="submit" class="btn-icon btn-cancel"><i class="bi bi-trash"></i></button></form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php
    }
}

// Purchases: merge history + active delivered rows when showing history
$like_user = mysqli_real_escape_string($con, $sessionUser);
$uid_like = intval($sessionUid);
$purchase_rows = [];
if($is_history){
    $ph_sql = "SELECT * FROM purchase_history WHERE ( `user` IN ({$userList}) OR `user` LIKE '%".$like_user."%' OR `user` LIKE '%user_".$uid_like."%' ) AND LOWER(IFNULL(delivery_status,'')) IN ('delivered','cancelled') ORDER BY pdate DESC";
    $ph_r = mysqli_query($con, $ph_sql);
    if($ph_r && mysqli_num_rows($ph_r)>0){ while($r=mysqli_fetch_assoc($ph_r)) $purchase_rows[]=$r; }
    $active_p_sql = "SELECT * FROM purchase WHERE ( `user` IN ({$userList}) OR `user` LIKE '%".$like_user."%' OR `user` LIKE '%user_".$uid_like."%' ) AND ( LOWER(IFNULL(delivery_status,'')) IN ('delivered') OR LOWER(IFNULL(status,'')) IN ('delivered','completed') ) ORDER BY pdate DESC";
    $active_pr = mysqli_query($con, $active_p_sql);
    if($active_pr && mysqli_num_rows($active_pr)>0){ while($ar=mysqli_fetch_assoc($active_pr)) $purchase_rows[]=$ar; }
} else {
    $purchase_sql = "SELECT * FROM purchase WHERE ( `user` IN ({$userList}) OR `user` LIKE '%".$like_user."%' OR `user` LIKE '%user_".$uid_like."%' ) AND LOWER(IFNULL(delivery_status,'')) NOT IN ('cancelled','delivered') AND LOWER(IFNULL(status,'')) NOT IN ('cancelled','delivered') ORDER BY pdate DESC";
    $pr = mysqli_query($con, $purchase_sql);
    if($pr && mysqli_num_rows($pr)>0){ while($r=mysqli_fetch_assoc($pr)) $purchase_rows[]=$r; }
}

// Avoid showing products that are part of builds already listed
$shown_build_pids = [];
foreach($builds as $b){
    $items_q = "SELECT product_id FROM build_items WHERE build_id='".intval($b['id'])."'";
    $items_r = mysqli_query($con, $items_q);
    if($items_r && mysqli_num_rows($items_r)>0){ while($it=mysqli_fetch_assoc($items_r)) $shown_build_pids[] = intval($it['product_id']); }
}

if(!empty($purchase_rows)){
    foreach($purchase_rows as $row){
        $pid = intval($row['prod_id']);
        if(in_array($pid, $shown_build_pids, true)) continue;
        $raw_delivery = trim($row['delivery_status'] ?? '');
        $raw_status = trim($row['status'] ?? '');
        $status = strtolower($raw_delivery !== '' ? $raw_delivery : ($raw_status !== '' ? $raw_status : 'order_confirmed'));
        if($status==='pending') $status='order_confirmed';
        if($status==='shipped') $status='out_for_delivery';
        $badge_class = 'badge-'.$status;
        $status_label = ucfirst(str_replace('_',' ',$status));
        if($status==='order_confirmed') $status_label='Order Confirmed';
        if($status==='out_for_delivery') $status_label='Out for Delivery';
        $icon='bi-hourglass-split'; if($status=='delivered') $icon='bi-check-circle-fill'; if($status=='out_for_delivery') $icon='bi-truck'; if($status=='cancelled') $icon='bi-x-circle-fill';
        $total = $row['pprice'] * $row['qty'];
?>
    <tr>
        <td>
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:45px;height:45px;"><i class="bi bi-box-seam text-primary fs-5"></i></div>
                <div>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['pname']); ?></div>
                    <div class="small text-muted">ID: #ORD-<?php echo $row['pid']; ?></div>
                </div>
            </div>
        </td>
        <td class="fw-semibold"><?php echo (int)$row['qty']; ?></td>
        <td>₹<?php echo number_format((float)$row['pprice'],2); ?></td>
        <td class="fw-bold text-primary">₹<?php echo number_format((float)$total,2); ?></td>
        <td><span class="badge-soft <?php echo $badge_class; ?>"><i class="bi <?php echo $icon; ?>"></i> <?php echo $status_label; ?></span></td>
        <td class="text-end">
            <div class="d-inline-flex gap-2">
                <form action="myorder_details.php" method="post"><input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>"><button type="submit" class="btn-icon btn-view"><i class="bi bi-eye"></i></button></form>
                <?php if($view !== 'history' && in_array($status, ['pending','order_confirmed'], true)): ?>
                    <form action="cancel_order.php" method="post"><input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>"><button type="submit" class="btn-icon btn-cancel"><i class="bi bi-trash"></i></button></form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php
    }
}
?>
                </tbody>
            </table>
<?php endif; ?>

        </div>
    </div>
</div>

<?php include(__DIR__ . '/../footer.php'); ?>
