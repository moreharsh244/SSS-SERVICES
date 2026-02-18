<?php
include('header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    :root {
        /* Purple Gradient to match Admin/User portals */
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
        --primary-solid: #6366f1;
        --card-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.15);
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* Hero Section */
    .delivery-hero {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.8);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    /* Left accent bar instead of right */
    .delivery-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0; width: 6px;
        background: var(--primary-grad);
    }

    .hero-title {
        font-weight: 800;
        font-size: 1.8rem;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .stat-pill {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        color: #7c3aed;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #e9d5ff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .stat-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 20px 30px -10px rgba(124, 58, 237, 0.2);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--text-dark); line-height: 1; margin-bottom: 5px; }
    .stat-label { font-weight: 600; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

    /* Card Specifics */
    .card-pending .stat-icon-wrapper { background: #fff7ed; color: #f59e0b; } /* Orange */
    .card-delivered .stat-icon-wrapper { background: #f0fdf4; color: #10b981; } /* Emerald */
    .card-cancelled .stat-icon-wrapper { background: #fef2f2; color: #ef4444; } /* Red */

    /* Tables */
    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .custom-table thead th {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 18px 20px;
        border-bottom: 1px solid #cbd5e1;
    }

    .custom-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .custom-table tr:hover { background-color: #f8fafc; }

    /* Badges */
    .badge-soft { padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; border: 1px solid transparent; }
    .badge-pending { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
    .badge-shipped, .badge-in_progress { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .badge-delivered, .badge-completed { background: #ecfdf5; color: #047857; border-color: #d1fae5; }
    .badge-cancelled { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }

    /* Action Elements */
    .form-select-sm {
        border-radius: 8px;
        border-color: #cbd5e1;
        font-size: 0.85rem;
        padding-top: 6px; padding-bottom: 6px;
    }
    
    .btn-action {
        background: #0f172a; /* Dark button */
        color: white;
        border: none;
        padding: 7px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: 0.2s;
    }
    .btn-action:hover { background: #2563eb; color: white; transform: translateY(-1px); }

    .btn-view-history {
        border: 1px solid #cbd5e1;
        color: #475569;
        background: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 16px;
    }
    .btn-view-history:hover { background: #f1f5f9; color: #0f172a; border-color: #94a3b8; }

</style>

<div class="container py-4">
    <div class="col-12 col-lg-11 mx-auto">
        <?php
        include '../admin/conn.php';
        include 'helpers.php';
        ensure_purchase_table($con);
        ensure_service_requests_table($con);
        
        $agent_raw = $_SESSION['username'] ?? '';
        $agent = mysqli_real_escape_string($con, $agent_raw);
        
        // Stats Logic
        $statSql = "SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(IFNULL(delivery_status,'pending')) NOT IN ('delivered','cancelled') THEN 1 ELSE 0 END) AS pending FROM purchase WHERE assigned_agent='$agent'";
        $statRes = mysqli_query($con, $statSql);
        $stats = ['total'=>0,'pending'=>0,'delivered'=>0];
        if($statRes && mysqli_num_rows($statRes)>0){ $stats = mysqli_fetch_assoc($statRes); }

        $delivered_count = 0; $cancelled_count = 0;
        $db = ''; $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
        if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
        if($db){
            $tbl = mysqli_real_escape_string($con, 'purchase_history');
            $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
            if($qc && mysqli_num_rows($qc)>0){
                $dc = @mysqli_query($con, "SELECT COUNT(*) AS total FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,''))='delivered'");
                if($dc && mysqli_num_rows($dc)>0){ $delivered_count = (int)(mysqli_fetch_assoc($dc)['total'] ?? 0); }
                $cc = @mysqli_query($con, "SELECT COUNT(*) AS total FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,''))='cancelled'");
                if($cc && mysqli_num_rows($cc)>0){ $cancelled_count = (int)(mysqli_fetch_assoc($cc)['total'] ?? 0); }
            }
        }
        $stats['delivered'] = $delivered_count;
        $stats['cancelled'] = $cancelled_count;
        ?>

        <div class="delivery-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="hero-title mb-1">Logistics Hub</h2>
                <div class="text-muted fw-medium"><i class="bi bi-person-check me-1"></i> Agent: <?php echo htmlspecialchars($agent_raw); ?></div>
            </div>
            <div class="stat-pill">
                <i class="bi bi-box-seam"></i> Total Assigned: <?php echo (int)($stats['total'] ?? 0); ?>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card card-pending">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo (int)($stats['pending'] ?? 0); ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card card-delivered">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo (int)($stats['delivered'] ?? 0); ?></div>
                        <div class="stat-label">Successfully Delivered</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card card-cancelled">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-x-lg"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo (int)($stats['cancelled'] ?? 0); ?></div>
                        <div class="stat-label">Cancelled Orders</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Active Assignments</h5>
            </div>
            <button class="btn btn-view-history" onclick="toggleHistory()">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
        </div>

        <div class="table-card mb-5">
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT * FROM purchase WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,'pending')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
                    $result = mysqli_query($con, $sql);
                    if($result && mysqli_num_rows($result) > 0){
                        while($row = mysqli_fetch_assoc($result)){
                            $id = (int)$row['pid'];
                            $pname = htmlspecialchars($row['pname'] ?? '');
                            $user = htmlspecialchars($row['user'] ?? '');
                            $qty = (int)($row['qty'] ?? 0);
                            $total = number_format(((float)($row['pprice'] ?? 0) * $qty), 2);
                    ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?php echo $id; ?></td>
                            <td class="fw-bold text-dark"><?php echo $pname; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-light rounded-circle p-1 d-flex align-items-center justify-content-center" style="width:24px;height:24px;">
                                        <i class="bi bi-person-fill text-muted" style="font-size:0.8rem;"></i>
                                    </div>
                                    <?php echo $user; ?>
                                </div>
                            </td>
                            <td><?php echo $qty; ?></td>
                            <td class="text-primary fw-bold">₹<?php echo $total; ?></td>
                            <td><span class="badge-soft badge-pending">Pending</span></td>
                            <td>
                                <form action="update_status.php" method="post" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                                    <select name="delivery_status" class="form-select form-select-sm" style="width: 130px;">
                                        <option value="delivered">Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn-action">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5 text-muted'><i class='bi bi-box2-heart display-6 d-block mb-3 opacity-25'></i>No active orders assigned</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="historyPanel" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3 pt-4 border-top">
                <h5 class="fw-bold text-dark mb-0">Delivery Archive</h5>
                <button class="btn btn-sm btn-light text-muted" onclick="toggleHistory()"><i class="bi bi-x"></i> Hide</button>
            </div>
            <div class="table-card mb-5">
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th><th>Product</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th>
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
                                $badge = ($dstatus === 'delivered') ? 'badge-delivered' : 'badge-cancelled';
                                echo "<tr>
                                    <td class='text-muted'>#{$row['pid']}</td>
                                    <td class='fw-bold'>".htmlspecialchars($row['pname'])."</td>
                                    <td>".htmlspecialchars($row['user'])."</td>
                                    <td>₹".number_format(((float)$row['pprice'] * (int)$row['qty']), 2)."</td>
                                    <td><span class='badge-soft {$badge}'>".ucfirst($dstatus)."</span></td>
                                    <td class='text-muted small'>".date('M d, Y', strtotime($row['pdate']))."</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No history found</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center mb-3 mt-5">
            <h5 class="fw-bold text-dark mb-0">Support & Service</h5>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th><th>Customer</th><th>Item</th><th>Type</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $req_res = mysqli_query($con, "SELECT * FROM service_requests WHERE assigned_agent='$agent' ORDER BY created_at DESC");
                    if($req_res && mysqli_num_rows($req_res) > 0){
                        while($row = mysqli_fetch_assoc($req_res)){
                            $status = strtolower($row['status'] ?? 'pending');
                            $status_map = ['pending'=>'badge-pending', 'in_progress'=>'badge-shipped', 'completed'=>'badge-completed', 'cancelled'=>'badge-cancelled'];
                            $badge = $status_map[$status] ?? 'badge-pending';
                    ?>
                        <tr>
                            <td class="text-muted">#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user']); ?></td>
                            <td><?php echo htmlspecialchars($row['item']); ?></td>
                            <td><span class="text-primary fw-bold"><?php echo htmlspecialchars($row['service_type']); ?></span></td>
                            <td><span class="badge-soft <?php echo $badge; ?>"><?php echo ucfirst(str_replace('_',' ', $status)); ?></span></td>
                            <td>
                                <form action='update_service_request.php' method='post' class='d-flex gap-2'>
                                    <input type='hidden' name='id' value='<?php echo $row['id']; ?>'>
                                    <select name='status' class='form-select form-select-sm' style='width:130px;'>
                                        <?php foreach(['pending','in_progress','completed','cancelled'] as $o): ?>
                                            <option value='<?php echo $o; ?>' <?php echo ($o==$status)?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$o)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type='submit' class='btn-action'>Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No service requests assigned</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function toggleHistory() {
    const historyPanel = document.getElementById('historyPanel');
    if (historyPanel.style.display === 'none') {
        historyPanel.style.display = 'block';
        setTimeout(() => { historyPanel.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    } else {
        historyPanel.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<?php include('footer.php'); ?>