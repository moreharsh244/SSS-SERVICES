<?php
include('header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    :root {
        --bg-body: #f4f7fe;
        --card-bg: #ffffff;
        --text-main: #2b3674;
        --text-muted: #a3aed1;
        --accent-orange: #ff9f43;
        --accent-green: #10b981;
        --accent-red: #ea5455;
        --shadow-soft: 0 18px 40px rgba(112, 144, 176, 0.12);
        --shadow-hover: 0 20px 45px rgba(112, 144, 176, 0.22);
    }

    body {
        background-color: var(--bg-body);
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-main);
    }

    /* Modern Tables - compact and attractive */
    .modern-panel {
      background: var(--card-bg);
      border-radius: 18px;
      box-shadow: var(--shadow-soft);
      padding: 18px 16px;
      margin-bottom: 18px;
      min-height: 220px;
      max-width: 1200px;
      margin-left: auto;
      margin-right: auto;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .panel-title {
      font-weight: 700;
      font-size: 1.08rem;
      color: var(--text-main);
      margin: 0;
    }

    .table-responsive { border-radius: 8px; }
    .table { margin-bottom: 0; }
    .table thead th {
      background: transparent;
      color: var(--text-muted);
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.72rem;
      letter-spacing: 0.5px;
      border-bottom: 1px solid #edf2f7;
      padding: 8px 10px;
    }
    .table tbody td {
      padding: 10px 10px;
      vertical-align: middle;
      border-bottom: 1px dashed #edf2f7;
      font-weight: 600;
      color: #4a5568;
      font-size: 0.97rem;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover { background-color: #f8fafc; }

    /* Glowing Badges */
    .badge-glow {
      padding: 6px 12px;
      border-radius: 18px;
      font-weight: 700;
      font-size: 0.72rem;
      display: inline-block;
      text-align: center;
    }
    .bg-soft-warning { background: rgba(255, 159, 67, 0.15); color: #e67e22; }
    .bg-soft-success { background: rgba(16, 185, 129, 0.15); color: #059669; }
    .bg-soft-danger { background: rgba(234, 84, 85, 0.15); color: #c0392b; }
    .bg-soft-primary { background: rgba(99, 102, 241, 0.15); color: #4338ca; }

    /* Form Controls */
    .custom-select {
      border: 1px solid #edf2f7;
      border-radius: 8px;
      padding: 6px 8px;
      font-size: 0.82rem;
      font-weight: 600;
      color: #4a5568;
      background-color: #f8fafc;
      transition: 0.2s;
      cursor: pointer;
    }
    .custom-select:focus { border-color: #c471ed; outline: none; box-shadow: 0 0 0 2px rgba(196, 113, 237, 0.15); }

    .btn-rounded {
      background: var(--text-main);
      color: white;
      border: none;
      padding: 6px 14px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.82rem;
      transition: 0.3s;
    }
    .btn-rounded:hover { background: #12c2e9; transform: translateY(-2px); color: white; box-shadow: 0 3px 10px rgba(18, 194, 233, 0.25); }

    .btn-outline-custom {
      border: 1px solid #edf2f7;
      background: transparent;
      color: var(--text-main);
      font-weight: 700;
      border-radius: 8px;
      padding: 6px 14px;
      transition: 0.3s;
    }
    .btn-outline-custom:hover { background: var(--text-main); color: white; border-color: var(--text-main); }

    .avatar-sm {
      width: 28px; height: 28px;
      border-radius: 7px;
      background: #edf2f7;
      color: var(--text-main);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      margin-right: 7px;
      font-size: 0.95rem;
    }
</style>

<style>
@keyframes pop {
  0% { transform: scale(0.7); opacity: 0.2; }
  60% { transform: scale(1.15); opacity: 1; }
  100% { transform: scale(1); }
}
.row > .col-md-4 > div:hover {
  box-shadow: 0 8px 32px rgba(44,62,80,0.18);
  transform: translateY(-2px) scale(1.03);
}
</style>

<div class="container py-4 mb-5">
    <div class="col-12 col-xl-11 mx-auto">
        <?php
        include '../admin/conn.php';
        include 'helpers.php';
        ensure_purchase_table($con);
        ensure_service_requests_table($con);
        ensure_builds_history_table($con);
        
        $agent_raw = $_SESSION['username'] ?? '';
        $agent = mysqli_real_escape_string($con, $agent_raw);
        
        // Stats Logic (include builds and support requests)
        $pending_orders = 0;
        $pending_builds = 0;
        $pending_services = 0;
        $statSql = "SELECT COUNT(*) AS cnt FROM purchase WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,'pending')) NOT IN ('delivered','cancelled')";
        $statRes = mysqli_query($con, $statSql);
        if($statRes && mysqli_num_rows($statRes)>0){ $pending_orders = (int)mysqli_fetch_assoc($statRes)['cnt']; }
        $pending_builds_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds WHERE assigned_agent='$agent' AND (status IS NULL OR status IN ('pending','out_for_delivery'))");
        if($pending_builds_res && mysqli_num_rows($pending_builds_res)>0){ $pending_builds = (int)mysqli_fetch_assoc($pending_builds_res)['cnt']; }
        $pending_services_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests WHERE assigned_agent='$agent' AND status IN ('pending','in_progress')");
        if($pending_services_res && mysqli_num_rows($pending_services_res)>0){ $pending_services = (int)mysqli_fetch_assoc($pending_services_res)['cnt']; }
        $pending_total = $pending_orders + $pending_builds + $pending_services;

        // Completed and Cancelled counts (include builds and support requests)
        $delivered_count = 0;
        $cancelled_count = 0;
        // Product orders
        $delivered_sql = "SELECT COUNT(*) AS cnt FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,''))='delivered'";
        $delivered_res = mysqli_query($con, $delivered_sql);
        if($delivered_res && mysqli_num_rows($delivered_res)>0){ $delivered_count += (int)mysqli_fetch_assoc($delivered_res)['cnt']; }
        $cancelled_sql = "SELECT COUNT(*) AS cnt FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,''))='cancelled'";
        $cancelled_res = mysqli_query($con, $cancelled_sql);
        if($cancelled_res && mysqli_num_rows($cancelled_res)>0){ $cancelled_count += (int)mysqli_fetch_assoc($cancelled_res)['cnt']; }
        // Builds (count both active table and archived builds_history)
        $builds_delivered_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds WHERE assigned_agent='$agent' AND status IN ('delivered','completed')");
        if($builds_delivered_res && mysqli_num_rows($builds_delivered_res)>0){ $delivered_count += (int)mysqli_fetch_assoc($builds_delivered_res)['cnt']; }
        $builds_cancelled_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds WHERE assigned_agent='$agent' AND status='cancelled'");
        if($builds_cancelled_res && mysqli_num_rows($builds_cancelled_res)>0){ $cancelled_count += (int)mysqli_fetch_assoc($builds_cancelled_res)['cnt']; }
        // include archived builds count
        $bh_del = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(status,'')) IN ('delivered','completed')");
        if($bh_del && mysqli_num_rows($bh_del)>0){ $delivered_count += (int)mysqli_fetch_assoc($bh_del)['cnt']; }
        $bh_can = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(status,''))='cancelled'");
        if($bh_can && mysqli_num_rows($bh_can)>0){ $cancelled_count += (int)mysqli_fetch_assoc($bh_can)['cnt']; }
        // Support requests (include history table)
        $services_delivered_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests WHERE assigned_agent='$agent' AND status='completed'");
        if($services_delivered_res && mysqli_num_rows($services_delivered_res)>0){ $delivered_count += (int)mysqli_fetch_assoc($services_delivered_res)['cnt']; }
        $services_delivered_hist_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests_history WHERE assigned_agent='$agent' AND status='completed'");
        if($services_delivered_hist_res && mysqli_num_rows($services_delivered_hist_res)>0){ $delivered_count += (int)mysqli_fetch_assoc($services_delivered_hist_res)['cnt']; }
        $services_cancelled_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests WHERE assigned_agent='$agent' AND status='cancelled'");
        if($services_cancelled_res && mysqli_num_rows($services_cancelled_res)>0){ $cancelled_count += (int)mysqli_fetch_assoc($services_cancelled_res)['cnt']; }
        $services_cancelled_hist_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests_history WHERE assigned_agent='$agent' AND status='cancelled'");
        if($services_cancelled_hist_res && mysqli_num_rows($services_cancelled_hist_res)>0){ $cancelled_count += (int)mysqli_fetch_assoc($services_cancelled_hist_res)['cnt']; }
        $db = ''; $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
        if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
        ?>
         

<script>
function showOnlyHistory() {
    // Hide all panels except history
    document.querySelectorAll('.modern-panel').forEach(panel => panel.style.display = 'none');
    document.getElementById('historyPanel').style.display = 'block';
    document.getElementById('buildHistoryPanel').style.display = 'block';
    document.getElementById('deliveryHistoryPanel').style.display = 'block';
    document.getElementById('serviceHistoryPanel').style.display = 'block';
    // Make service history panel height match others
    setTimeout(() => {
        document.getElementById('historyPanel').scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Balance service panel height
        let deliveryPanel = document.getElementById('deliveryHistoryPanel');
        let servicePanel = document.getElementById('serviceHistoryPanel');
        if (deliveryPanel && servicePanel) {
            servicePanel.style.minHeight = deliveryPanel.offsetHeight + 'px';
        }
    }, 100);
}
function restorePanels() {
    document.getElementById('historyPanel').style.display = 'none';
    document.getElementById('buildHistoryPanel').style.display = 'none';
    document.getElementById('deliveryHistoryPanel').style.display = 'none';
    document.getElementById('serviceHistoryPanel').style.display = 'none';
    document.querySelectorAll('.modern-panel').forEach(panel => panel.style.display = '' );
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>



<!-- Order Stats Box - Enhanced Attractive Style -->
<div class="row" style="max-width:1100px;margin:auto;">
  <div class="col-md-4 mb-2">
    <div style="background:linear-gradient(135deg,#fffbe6 0%,#ffe9d2 100%);border-radius:18px;box-shadow:0 4px 18px rgba(255,159,67,0.10);padding:22px 0 16px 0;display:flex;flex-direction:column;align-items:center;transition:box-shadow 0.2s;cursor:pointer;">
      <div style="font-size:2.5rem;color:#ff9f43;margin-bottom:10px;animation:pop 0.7s;"><i class="bi bi-clock-history"></i></div>
      <div style="font-weight:700;font-size:1.15rem;color:#2b3674;letter-spacing:0.5px;">Pending Orders</div>
      <div style="font-size:1.8rem;font-weight:900;color:#2b3674;"> <?php echo $pending_total; ?> </div>
    </div>
  </div>
  <div class="col-md-4 mb-2">
    <div style="background:linear-gradient(135deg,#eafff7 0%,#d2fff3 100%);border-radius:18px;box-shadow:0 4px 18px rgba(16,185,129,0.10);padding:22px 0 16px 0;display:flex;flex-direction:column;align-items:center;transition:box-shadow 0.2s;cursor:pointer;">
      <div style="font-size:2.5rem;color:#10b981;margin-bottom:10px;animation:pop 0.7s;"><i class="bi bi-check2-circle"></i></div>
      <div style="font-weight:700;font-size:1.15rem;color:#2b3674;letter-spacing:0.5px;">Completed Orders</div>
      <div style="font-size:1.8rem;font-weight:900;color:#2b3674;">
        <?php echo $delivered_count; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-2">
    <div style="background:linear-gradient(135deg,#ffe6ea 0%,#ffd2e2 100%);border-radius:18px;box-shadow:0 4px 18px rgba(234,84,85,0.10);padding:22px 0 16px 0;display:flex;flex-direction:column;align-items:center;transition:box-shadow 0.2s;cursor:pointer;">
      <div style="font-size:2.5rem;color:#ea5455;margin-bottom:10px;animation:pop 0.7s;"><i class="bi bi-x-circle"></i></div>
      <div style="font-weight:700;font-size:1.15rem;color:#2b3674;letter-spacing:0.5px;">Cancelled Orders</div>
      <div style="font-size:1.8rem;font-weight:900;color:#2b3674;">
        <?php echo $cancelled_count; ?>
      </div>
    </div>
  </div>
</div>

<!-- Product Delivery Orders -->
<div class="modern-panel mt-4" style="min-height:350px;">
  <div class="panel-header">
    <span class="panel-title"><i class="bi bi-box-seam me-2 text-primary"></i> Product Delivery Orders</span>
    <ul class="nav nav-tabs" id="productTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="product-active-tab" data-bs-toggle="tab" data-bs-target="#product-active" type="button" role="tab">Active Assignments</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="product-history-tab" data-bs-toggle="tab" data-bs-target="#product-history" type="button" role="tab">History</button>
      </li>
    </ul>
  </div>
  <div class="tab-content" id="productTabsContent">
    <div class="tab-pane fade show active" id="product-active" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Order ID</th><th>Product</th><th>Customer</th><th>Qty</th><th>Amount</th><th>Status</th><th>Update Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Filter out build components from product delivery orders
          $excluded_pids = [];
          $build_ids_res = mysqli_query($con, "SELECT id FROM builds WHERE assigned_agent='$agent'");
          $build_ids = [];
          if($build_ids_res && mysqli_num_rows($build_ids_res) > 0){
            while($b = mysqli_fetch_assoc($build_ids_res)){
              $build_ids[] = (int)$b['id'];
            }
          }
          if(!empty($build_ids)){
            $build_items_res = mysqli_query($con, "SELECT product_id FROM build_items WHERE build_id IN (".implode(',', $build_ids).")");
            if($build_items_res && mysqli_num_rows($build_items_res) > 0){
              while($bi = mysqli_fetch_assoc($build_items_res)){
                $excluded_pids[] = (int)$bi['product_id'];
              }
            }
          }
          $sql = "SELECT * FROM purchase WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,'pending')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
          $result = mysqli_query($con, $sql);
          if($result && mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
              $id = (int)$row['pid'];
              if(in_array($id, $excluded_pids)) continue; // skip build components
              $pname = htmlspecialchars($row['pname'] ?? '');
              $user = htmlspecialchars($row['user'] ?? '');
              $qty = (int)($row['qty'] ?? 0);
              $total = number_format(((float)($row['pprice'] ?? 0) * $qty), 2);
              $dstatus = strtolower(trim($row['delivery_status'] ?? 'order_confirmed'));
              if($dstatus === 'pending') $dstatus = 'order_confirmed';
              if($dstatus === 'shipped') $dstatus = 'out_for_delivery';
              $badge = 'bg-soft-warning';
              if($dstatus === 'out_for_delivery') $badge = 'bg-soft-primary';
              if($dstatus === 'delivered') $badge = 'bg-soft-success';
              if($dstatus === 'cancelled') $badge = 'bg-soft-danger';
              $status_label = ucwords(str_replace('_', ' ', $dstatus));
          ?>
            <tr>
              <td class="text-muted">#<?php echo str_pad($id, 5, "0", STR_PAD_LEFT); ?></td>
              <td><span style="color: var(--text-main);"><?php echo $pname; ?></span></td>
              <td><div class="avatar-sm"><?php echo strtoupper(substr($user, 0, 1)); ?></div><?php echo $user; ?></td>
              <td><?php echo $qty; ?></td>
              <td style="color: var(--accent-green);">₹<?php echo $total; ?></td>
              <td><span class="badge-glow <?php echo $badge; ?>"><?php echo $status_label; ?></span></td>
              <td>
                <form action="update_status.php" method="post" class="d-flex align-items-center gap-2 m-0">
                  <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                  <select name="delivery_status" class="custom-select" style="width: 130px;">
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <button type="submit" class="btn-rounded">Save</button>
                </form>
              </td>
            </tr>
          <?php }
          } else {
            echo "<tr><td colspan='7' class='text-center py-5'><div class='opacity-50'><i class='bi bi-inbox fs-1 d-block mb-2'></i>No active orders assigned</div></td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="product-history" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th><th>Product</th><th>Customer</th><th>Total</th><th>Final Status</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $history_res = false;
          if($db){
            $tbl = mysqli_real_escape_string($con, 'purchase_history');
            $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
            if($qc && mysqli_num_rows($qc)>0){
              $history_sql = "SELECT * FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,'')) IN ('delivered','cancelled') ORDER BY pdate DESC";
              $history_res = mysqli_query($con, $history_sql);
            }
          }
          if($history_res && mysqli_num_rows($history_res)>0){
            while($row = mysqli_fetch_assoc($history_res)){
              $dstatus = strtolower($row['delivery_status'] ?? '');
              $badge = ($dstatus === 'delivered') ? 'bg-soft-success' : 'bg-soft-danger';
          ?>
            <tr>
              <td class='text-muted'>#<?php echo str_pad($row['pid'], 5, "0", STR_PAD_LEFT); ?></td>
              <td><?php echo htmlspecialchars($row['pname']); ?></td>
              <td><?php echo htmlspecialchars($row['user']); ?></td>
              <td>₹<?php echo number_format(((float)$row['pprice'] * (int)$row['qty']), 2); ?></td>
              <td><span class='badge-glow <?php echo $badge; ?>'><?php echo ucfirst($dstatus); ?></span></td>
              <td class='text-muted'><?php echo date('M d, Y', strtotime($row['pdate'])); ?></td>
            </tr>
          <?php }
          } else {
          ?>
            <tr><td colspan='6' class='text-center py-4 text-muted'>Archive is empty</td></tr>
          <?php }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Custom PC Build Orders -->
<div class="modern-panel mt-4" style="min-height:350px;">
  <div class="panel-header">
    <span class="panel-title"><i class="bi bi-cpu me-2 text-danger"></i> Custom PC Build Orders</span>
    <ul class="nav nav-tabs" id="buildTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="build-active-tab" data-bs-toggle="tab" data-bs-target="#build-active" type="button" role="tab">Active Builds</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="build-history-tab" data-bs-toggle="tab" data-bs-target="#build-history" type="button" role="tab">Build History</button>
      </li>
    </ul>
  </div>
  <div class="tab-content" id="buildTabsContent">
    <div class="tab-pane fade show active" id="build-active" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Build ID</th><th>Build Name</th><th>Customer</th><th>Total</th><th>Status</th><th>Created</th><th>View</th><th>Update Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $builds_res = mysqli_query($con, "SELECT * FROM builds WHERE assigned_agent='$agent' AND (status IS NULL OR status IN ('pending','out_for_delivery')) ORDER BY created_at DESC");
          if($builds_res && mysqli_num_rows($builds_res) > 0){
            while($build = mysqli_fetch_assoc($builds_res)){
              $bid = (int)$build['id'];
              $bname = htmlspecialchars($build['name'] ?? '');
              $buser = htmlspecialchars($build['user_name'] ?? 'User#'.$build['user_id']);
              $btotal = number_format((float)($build['total'] ?? 0), 2);
              $bstatus = strtolower($build['status'] ?? 'pending');
              $bbadge = 'bg-soft-warning';
              if($bstatus === 'out_for_delivery') $bbadge = 'bg-soft-primary';
              if($bstatus === 'delivered') $bbadge = 'bg-soft-success';
              if($bstatus === 'cancelled') $bbadge = 'bg-soft-danger';
              $bstatus_label = ucwords(str_replace('_', ' ', $bstatus));
          ?>
            <tr>
              <td class="text-muted">#<?php echo str_pad($bid, 5, "0", STR_PAD_LEFT); ?></td>
              <td><?php echo $bname; ?></td>
              <td><?php echo $buser; ?></td>
              <td style="color: var(--accent-green);">₹<?php echo $btotal; ?></td>
              <td><span class="badge-glow <?php echo $bbadge; ?>"><?php echo $bstatus_label; ?></span></td>
              <td class="text-muted"><?php echo htmlspecialchars($build['created_at']); ?></td>
              <td><a href="view_build.php?id=<?php echo $bid; ?>" class="btn btn-outline-custom btn-sm">View</a></td>
              <td>
                <form action="update_build_status.php" method="post" class="d-flex align-items-center gap-2 m-0">
                  <input type="hidden" name="build_id" value="<?php echo $bid; ?>">
                  <select name="build_status" class="custom-select" style="width: 130px;">
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <button type="submit" class="btn-rounded">Save</button>
                </form>
              </td>
            </tr>
          <?php }
          } else {
            echo "<tr><td colspan='8' class='text-center py-5'><div class='opacity-50'><i class='bi bi-inbox fs-1 d-block mb-2'></i>No builds assigned</div></td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="build-history" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Build ID</th><th>Build Name</th><th>Customer</th><th>Total</th><th>Status</th><th>Created</th><th>View</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Read archived builds history and fallback to delivered/cancelled/completed rows still present in builds table
          $history_rows = [];
          $seen_history_ids = [];

          $builds_hist = mysqli_query($con, "SELECT * FROM builds_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(status,'')) IN ('delivered','completed','cancelled') ORDER BY completed_at DESC");
          if($builds_hist && mysqli_num_rows($builds_hist) > 0){
            while($build = mysqli_fetch_assoc($builds_hist)){
              $history_rows[] = $build;
              $seen_history_ids[] = (int)$build['id'];
            }
          }

          $builds_fallback = mysqli_query($con, "SELECT * FROM builds WHERE assigned_agent='$agent' AND LOWER(IFNULL(status,'')) IN ('delivered','completed','cancelled') ORDER BY created_at DESC");
          if($builds_fallback && mysqli_num_rows($builds_fallback) > 0){
            while($build = mysqli_fetch_assoc($builds_fallback)){
              $bid = (int)$build['id'];
              if(in_array($bid, $seen_history_ids, true)) continue;
              $history_rows[] = $build;
            }
          }

          if(count($history_rows) > 0){
            usort($history_rows, function($a, $b){
              $at = strtotime($a['completed_at'] ?? $a['created_at'] ?? '1970-01-01');
              $bt = strtotime($b['completed_at'] ?? $b['created_at'] ?? '1970-01-01');
              return $bt <=> $at;
            });

            foreach($history_rows as $build){
              $bid = (int)$build['id'];
              $bname = htmlspecialchars($build['name'] ?? '');
              $buser = htmlspecialchars($build['user_name'] ?? 'User#'.$build['user_id']);
              $btotal = number_format((float)($build['total'] ?? 0), 2);
              $bstatus = strtolower($build['status'] ?? 'delivered');
              $bbadge = 'bg-soft-success';
              if($bstatus === 'cancelled') $bbadge = 'bg-soft-danger';
              $bstatus_label = ucwords(str_replace('_', ' ', $bstatus));
              $display_date = !empty($build['completed_at']) ? $build['completed_at'] : ($build['created_at'] ?? '');
          ?>
            <tr>
              <td class="text-muted">#<?php echo str_pad($bid, 5, "0", STR_PAD_LEFT); ?></td>
              <td><?php echo $bname; ?></td>
              <td><?php echo $buser; ?></td>
              <td style="color: var(--accent-green);">₹<?php echo $btotal; ?></td>
              <td><span class="badge-glow <?php echo $bbadge; ?>"><?php echo $bstatus_label; ?></span></td>
              <td class="text-muted"><?php echo htmlspecialchars($display_date); ?></td>
              <td><a href="view_build.php?id=<?php echo $bid; ?>" class="btn btn-outline-custom btn-sm">View</a></td>
            </tr>
          <?php }
          } else {
            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No build history found</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Support Requests -->
<div class="modern-panel mt-4" style="min-height:350px;">
  <div class="panel-header">
    <span class="panel-title"><i class="bi bi-tools me-2 text-danger"></i> Support Requests</span>
    <ul class="nav nav-tabs" id="supportTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="support-active-tab" data-bs-toggle="tab" data-bs-target="#support-active" type="button" role="tab">Active Requests</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="support-history-tab" data-bs-toggle="tab" data-bs-target="#support-history" type="button" role="tab">History</button>
      </li>
    </ul>
  </div>
  <div class="tab-content" id="supportTabsContent">
    <div class="tab-pane fade show active" id="support-active" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Ticket ID</th><th>Client</th><th>Equipment</th><th>Service Type</th><th>Status</th><th>Update Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $req_res = mysqli_query($con, "SELECT * FROM service_requests WHERE assigned_agent='$agent' AND status IN ('pending','in_progress') ORDER BY created_at DESC");
          if($req_res && mysqli_num_rows($req_res) > 0){
            while($row = mysqli_fetch_assoc($req_res)){
              $status = strtolower($row['status'] ?? 'pending');
              $status_map = ['pending'=>'bg-soft-warning', 'in_progress'=>'bg-soft-primary', 'completed'=>'bg-soft-success', 'cancelled'=>'bg-soft-danger'];
              $badge = $status_map[$status] ?? 'bg-soft-warning';
          ?>
            <tr>
              <td class="text-muted">#<?php echo str_pad($row['id'], 5, "0", STR_PAD_LEFT); ?></td>
              <td><div class="avatar-sm" style="background: #e0e7ff; color: #4338ca;"><?php echo strtoupper(substr($row['user'], 0, 1)); ?></div><?php echo htmlspecialchars($row['user']); ?></td>
              <td><?php echo htmlspecialchars($row['item']); ?></td>
              <td style="color: #c471ed;"><?php echo htmlspecialchars($row['service_type']); ?></td>
              <td><span class="badge-glow <?php echo $badge; ?>"><?php echo ucfirst(str_replace('_',' ', $status)); ?></span></td>
              <td>
                <form action='update_service_request.php' method='post' class='d-flex align-items-center gap-2 m-0'>
                  <input type='hidden' name='id' value='<?php echo $row['id']; ?>'>
                  <select name='status' class='custom-select' style='width:130px;'>
                    <?php foreach(['pending','in_progress','completed','cancelled'] as $o): ?>
                      <option value='<?php echo $o; ?>' <?php echo ($o==$status)?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$o)); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type='submit' class='btn-rounded'>Update</button>
                </form>
              </td>
            </tr>
          <?php }
          } else {
            echo "<tr><td colspan='6' class='text-center py-5'><div class='opacity-50'><i class='bi bi-pc-display fs-1 d-block mb-2'></i>No support tickets assigned</div></td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="support-history" role="tabpanel">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Ticket ID</th><th>Client</th><th>Equipment</th><th>Service Type</th><th>Status</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $rows = [];
            $service_res = mysqli_query($con, "SELECT * FROM service_requests WHERE assigned_agent='$agent' AND status IN ('completed','cancelled') ORDER BY created_at DESC");
            if($service_res && mysqli_num_rows($service_res) > 0){
              while($row = mysqli_fetch_assoc($service_res)){
                $rows[] = $row;
              }
            }
            $service_hist_res = mysqli_query($con, "SELECT * FROM service_requests_history WHERE assigned_agent='$agent' AND status IN ('completed','cancelled') ORDER BY created_at DESC");
            if($service_hist_res && mysqli_num_rows($service_hist_res) > 0){
              while($row = mysqli_fetch_assoc($service_hist_res)){
                $rows[] = $row;
              }
            }
            if(count($rows) > 0){
              // Sort all rows by created_at DESC
              usort($rows, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
              });
              foreach($rows as $row){
                $status = strtolower($row['status'] ?? 'pending');
                $status_map = ['completed'=>'bg-soft-success', 'cancelled'=>'bg-soft-danger'];
                $badge = $status_map[$status] ?? 'bg-soft-warning';
            ?>
              <tr>
                <td class='text-muted'>#<?php echo str_pad($row['id'], 5, "0", STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($row['user']); ?></td>
                <td><?php echo htmlspecialchars($row['item']); ?></td>
                <td style='color: #c471ed;'><?php echo htmlspecialchars($row['service_type']); ?></td>
                <td><span class='badge-glow <?php echo $badge; ?>'><?php echo ucfirst(str_replace('_',' ', $status)); ?></span></td>
                <td class='text-muted'><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
              </tr>
            <?php }
            } else {
              echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No completed/cancelled service tickets</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include(__DIR__ . '/../footer.php'); ?>
</div>
</div>