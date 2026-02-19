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

    /* Vibrant Grainy Hero Section */
    .vibrant-hero {
        background: linear-gradient(135deg, #12c2e9 0%, #c471ed 50%, #f64f59 100%);
        border-radius: 24px;
        padding: 40px 40px 90px 40px; /* Extra bottom padding for floating cards */
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(196, 113, 237, 0.3);
        margin-top: 20px;
    }

    /* Grain Texture Overlay */
    .vibrant-hero::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.4'/%3E%3C/svg%3E");
        mix-blend-mode: overlay;
        pointer-events: none;
    }

    .hero-title {
        font-weight: 800;
        font-size: 2.2rem;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 2;
    }

    .glass-pill {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 2;
    }

    /* Overlapping Stat Cards */
    .stat-container {
        margin-top: -60px; /* Pulls cards up over the hero */
        position: relative;
        z-index: 10;
    }

    .stat-box {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--shadow-soft);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border-bottom: 4px solid transparent;
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .stat-box.pending { border-color: var(--accent-orange); }
    .stat-box.delivered { border-color: var(--accent-green); }
    .stat-box.cancelled { border-color: var(--accent-red); }

    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
    }

    .pending .icon-circle { background: rgba(255, 159, 67, 0.1); color: var(--accent-orange); }
    .delivered .icon-circle { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
    .cancelled .icon-circle { background: rgba(234, 84, 85, 0.1); color: var(--accent-red); }

    .stat-info h3 { font-size: 2rem; font-weight: 800; margin: 0; color: var(--text-main); line-height: 1.2; }
    .stat-info p { font-size: 0.9rem; font-weight: 600; color: var(--text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Modern Tables */
    .modern-panel {
        background: var(--card-bg);
        border-radius: 24px;
        box-shadow: var(--shadow-soft);
        padding: 25px;
        margin-bottom: 30px;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .panel-title {
        font-weight: 800;
        font-size: 1.3rem;
        color: var(--text-main);
        margin: 0;
    }

    .table-responsive { border-radius: 12px; }
    
    .table { margin-bottom: 0; }
    
    .table thead th {
        background: transparent;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        border-bottom: 2px solid #edf2f7;
        padding: 15px 20px;
    }

    .table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-bottom: 1px dashed #edf2f7;
        font-weight: 600;
        color: #4a5568;
    }

    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover { background-color: #f8fafc; }

    /* Glowing Badges */
    .badge-glow {
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-block;
        text-align: center;
    }
    .bg-soft-warning { background: rgba(255, 159, 67, 0.15); color: #e67e22; }
    .bg-soft-success { background: rgba(16, 185, 129, 0.15); color: #059669; }
    .bg-soft-danger { background: rgba(234, 84, 85, 0.15); color: #c0392b; }
    .bg-soft-primary { background: rgba(99, 102, 241, 0.15); color: #4338ca; }

    /* Form Controls */
    .custom-select {
        border: 2px solid #edf2f7;
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #4a5568;
        background-color: #f8fafc;
        transition: 0.2s;
        cursor: pointer;
    }
    .custom-select:focus { border-color: #c471ed; outline: none; box-shadow: 0 0 0 3px rgba(196, 113, 237, 0.2); }

    .btn-rounded {
        background: var(--text-main);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: 0.3s;
    }
    .btn-rounded:hover { background: #12c2e9; transform: translateY(-2px); color: white; box-shadow: 0 5px 15px rgba(18, 194, 233, 0.4); }

    .btn-outline-custom {
        border: 2px solid #edf2f7;
        background: transparent;
        color: var(--text-main);
        font-weight: 700;
        border-radius: 12px;
        padding: 8px 20px;
        transition: 0.3s;
    }
    .btn-outline-custom:hover { background: var(--text-main); color: white; border-color: var(--text-main); }

    .avatar-sm {
        width: 35px; height: 35px;
        border-radius: 10px;
        background: #edf2f7;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin-right: 10px;
    }
</style>

<div class="container py-4 mb-5">
    <div class="col-12 col-xl-11 mx-auto">
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

        <div class="vibrant-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="hero-title mb-2">Agent Dashboard</h2>
                <div class="glass-pill mt-2">
                    <i class="bi bi-person-bounding-box"></i> Welcome back, <?php echo htmlspecialchars($agent_raw); ?>
                </div>
            </div>
            <div class="glass-pill">
                <i class="bi bi-layers-fill fs-5"></i> 
                <span>Total Workload: <?php echo (int)($stats['total'] ?? 0); ?> Tasks</span>
            </div>
        </div>

        <div class="row g-4 stat-container mb-5">
            <div class="col-md-4">
                <div class="stat-box pending">
                    <div class="icon-circle"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-info">
                        <h3><?php echo (int)($stats['pending'] ?? 0); ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box delivered">
                    <div class="icon-circle"><i class="bi bi-check-all"></i></div>
                    <div class="stat-info">
                        <h3><?php echo (int)($stats['delivered'] ?? 0); ?></h3>
                        <p>Successful Deliveries</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box cancelled">
                    <div class="icon-circle"><i class="bi bi-x-octagon"></i></div>
                    <div class="stat-info">
                        <h3><?php echo (int)($stats['cancelled'] ?? 0); ?></h3>
                        <p>Cancelled Orders</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-panel">
            <div class="panel-header">
                <h5 class="panel-title"><i class="bi bi-truck me-2 text-primary"></i> Active Assignments</h5>
                <button class="btn btn-outline-custom" onclick="toggleHistory()">
                    <i class="bi bi-archive-fill me-1"></i> View Archive
                </button>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Update Action</th>
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
                            <td>
                                <div class="avatar-sm"><?php echo strtoupper(substr($user, 0, 1)); ?></div>
                                <?php echo $user; ?>
                            </td>
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
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5'><div class='opacity-50'><i class='bi bi-inbox fs-1 d-block mb-2'></i>No active orders assigned</div></td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="historyPanel" style="display: none;">
            <div class="modern-panel" style="background: #f8fafc; border: 1px dashed #cbd5e1; box-shadow: none;">
                <div class="panel-header">
                    <h5 class="panel-title text-muted"><i class="bi bi-clock-history me-2"></i> Delivery Archive</h5>
                    <button class="btn btn-sm btn-light rounded-circle" onclick="toggleHistory()"><i class="bi bi-x-lg"></i></button>
                </div>
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
                                echo "<tr>
                                    <td class='text-muted'>#".str_pad($row['pid'], 5, "0", STR_PAD_LEFT)."</td>
                                    <td>".htmlspecialchars($row['pname'])."</td>
                                    <td>".htmlspecialchars($row['user'])."</td>
                                    <td>₹".number_format(((float)$row['pprice'] * (int)$row['qty']), 2)."</td>
                                    <td><span class='badge-glow {$badge}'>".ucfirst($dstatus)."</span></td>
                                    <td class='text-muted'>".date('M d, Y', strtotime($row['pdate']))."</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Archive is empty</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modern-panel mt-4">
            <div class="panel-header">
                <h5 class="panel-title"><i class="bi bi-tools me-2 text-danger"></i> Hardware Support Tickets</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th><th>Client</th><th>Equipment</th><th>Service Type</th><th>Status</th><th>Update Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $req_res = mysqli_query($con, "SELECT * FROM service_requests WHERE assigned_agent='$agent' ORDER BY created_at DESC");
                    if($req_res && mysqli_num_rows($req_res) > 0){
                        while($row = mysqli_fetch_assoc($req_res)){
                            $status = strtolower($row['status'] ?? 'pending');
                            $status_map = ['pending'=>'bg-soft-warning', 'in_progress'=>'bg-soft-primary', 'completed'=>'bg-soft-success', 'cancelled'=>'bg-soft-danger'];
                            $badge = $status_map[$status] ?? 'bg-soft-warning';
                    ?>
                        <tr>
                            <td class="text-muted">#<?php echo str_pad($row['id'], 5, "0", STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="avatar-sm" style="background: #e0e7ff; color: #4338ca;"><?php echo strtoupper(substr($row['user'], 0, 1)); ?></div>
                                <?php echo htmlspecialchars($row['user']); ?>
                            </td>
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
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5'><div class='opacity-50'><i class='bi bi-pc-display fs-1 d-block mb-2'></i>No support tickets assigned</div></td></tr>";
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
        setTimeout(() => { historyPanel.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
    } else {
        historyPanel.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<?php include('footer.php'); ?>