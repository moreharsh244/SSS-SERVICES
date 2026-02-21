<?php
define('page','myorder');
include('header.php');

$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';
$is_history = ($view === 'history');
$is_service = ($view === 'service');
?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    :root {
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --bg-surface: #f8fafc;
        --card-shadow: 0 10px 30px -5px rgba(0,0,0,0.08);
        --text-dark: #1e293b;
        --text-muted: #64748b;
    }

    body {
        background-color: var(--bg-surface);
        color: var(--text-dark);
    }

    /* Page Header */
    .page-header {
        background: white;
        padding: 40px 0 30px;
        margin-bottom: 30px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .page-title {
        font-weight: 800;
        font-size: 2rem;
        background: -webkit-linear-gradient(45deg, #1e293b, #4f46e5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }

    /* Navigation Tabs */
    .nav-pills-custom {
        background: #f1f5f9;
        padding: 5px;
        border-radius: 50px;
        display: inline-flex;
        gap: 5px;
    }

                --primary-grad: linear-gradient(135deg, #8b5cf6 0%, #0ea5e9 100%);
                --bg-surface: #eef4ff;
                --card-shadow: 0 12px 30px -5px rgba(30,64,175,0.12);
                --text-dark: #1f2a44;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s ease;
                background:
                    radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
                    radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
                    linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    .nav-link-custom:hover {
        color: #6366f1;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
                background: linear-gradient(120deg, #f5f3ff 0%, #eef6ff 55%, #f0fdf4 100%);
    .nav-link-custom.active {
        background: var(--primary-grad);
                border-bottom: 1px solid #bfdbfe;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
    }

    /* Table Styling */
    .orders-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }

    .custom-table thead th {
        background: #f8fafc;
                background: linear-gradient(90deg, #f5f3ff 0%, #e0f2fe 100%);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px 20px;
        border-bottom: 2px solid #e2e8f0;
    }
            .btn-view:hover { background: #7c3aed; color: white; transform: translateY(-2px); }

            .btn-outline-dark { border-color: #93c5fd; color: #0369a1; }
            .btn-outline-dark:hover { background: #e0f2fe; border-color: #60a5fa; color: #0c4a6e; }
    .custom-table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.95rem;
    }

    .custom-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Status Badges */
    .badge-soft {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .badge-pending, .badge-order_confirmed { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .badge-shipped, .badge-in_progress, .badge-out_for_delivery { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .badge-delivered, .badge-completed { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
    .badge-cancelled { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
    .badge-default { background: #f3f4f6; color: #4b5563; }

    /* Action Buttons */
    .btn-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
        border: none;
    }
    .btn-view { background: #eef2ff; color: #6366f1; }
    .btn-view:hover { background: #6366f1; color: white; transform: translateY(-2px); }
    
    .btn-cancel { background: #fef2f2; color: #ef4444; }
    .btn-cancel:hover { background: #ef4444; color: white; transform: translateY(-2px); }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-icon {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 15px;
    }

</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Your Activity</h1>
                <p class="text-muted mb-0">Track orders, history, and support requests.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="nav-pills-custom">
                    <a class="nav-link-custom <?php echo (!$is_history && !$is_service) ? 'active' : ''; ?>" href="myorder.php">
                        <i class="bi bi-box-seam me-1"></i> Active
                    </a>
                    <a class="nav-link-custom <?php echo $is_history ? 'active' : ''; ?>" href="myorder.php?view=history">
                        <i class="bi bi-clock-history me-1"></i> History
                    </a>
                    <a class="nav-link-custom <?php echo $is_service ? 'active' : ''; ?>" href="myorder.php?view=service">
                        <i class="bi bi-tools me-1"></i> Support
                    </a>
                </div>
                <a class="btn btn-outline-dark rounded-pill ms-2 px-4 fw-bold" href="view_products.php">Shop <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="orders-card">
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
                            include('../delivery/helpers.php');
                            ensure_service_requests_table($con);
                            ensure_service_requests_history_table($con);

                            $sessionUser = $_SESSION['username'] ?? '';
                            $sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
                            $sessionUid = $_SESSION['user_id'] ?? null;
                            $possibleUsers = [ $sessionUserEsc ];
                            if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
                            $userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";

                            $result = mysqli_query($con, "SELECT * FROM service_requests WHERE `user` IN ({$userList}) ORDER BY created_at DESC");
                            
                            if($result && mysqli_num_rows($result) > 0){
                                while($row = mysqli_fetch_assoc($result)){
                                    $status = strtolower(trim($row['status'] ?? 'pending'));
                                    $badge_class = 'badge-' . $status; // maps to css classes above
                                    $status_label = ucfirst(str_replace('_',' ',$status));
                                    
                                    // Status Icon Logic
                                    $status_icon = 'bi-circle';
                                    if($status == 'completed') $status_icon = 'bi-check-circle-fill';
                                    if($status == 'in_progress') $status_icon = 'bi-gear-wide-connected';
                                    if($status == 'pending') $status_icon = 'bi-hourglass-split';

                                    $assigned_agent = trim($row['assigned_agent'] ?? '');
                                    $agent_note = trim($row['agent_note'] ?? '');
                                    $response = $assigned_agent ? 'Agent: ' . htmlspecialchars($assigned_agent) : 'Pending Review';
                                    if($agent_note) $response .= ' - ' . htmlspecialchars(mb_strimwidth($agent_note, 0, 50, '...'));
                                    
                                    $can_cancel = ($status === 'pending' && $assigned_agent === '');
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['item'] ?? ''); ?></div>
                                    <div class="small text-muted">ID: #SR-<?php echo (int)$row['id']; ?></div>
                                </td>
                                <td><span class="fw-semibold text-primary"><?php echo htmlspecialchars($row['service_type'] ?? ''); ?></span></td>
                                <td>
                                    <span class="badge-soft <?php echo $badge_class; ?>">
                                        <i class="bi <?php echo $status_icon; ?>"></i> <?php echo $status_label; ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo $response; ?></td>
                                <td><?php echo !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '-'; ?></td>
                                <td class="text-end">
                                    <?php if($can_cancel): ?>
                                        <form action="cancel_order.php" method="post" onsubmit="return confirm('Cancel this request?');" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$row['id']; ?>">
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
                            $sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
                            $sessionUid = $_SESSION['user_id'] ?? null;
                            $possibleUsers = [ $sessionUserEsc ];
                            if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
                            $userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";

                            if($view === 'history'){
                                // History Logic
                                $db = '';
                                $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
                                $show_res = false;
                                if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
                                if($db){
                                    $tbl = mysqli_real_escape_string($con, 'purchase_history');
                                    $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
                                    if($qc && mysqli_num_rows($qc)>0){
                                        $sql = "SELECT * FROM `purchase_history` WHERE `user` IN ({$userList}) AND LOWER(IFNULL(delivery_status,'')) IN ('cancelled','delivered') ORDER BY pdate DESC";
                                        $show_res = true;
                                    }
                                }
                                if(!$show_res){ $result = false; } else { $result = mysqli_query($con,$sql); }
                            } else {
                                // Active Logic
                                $sql="SELECT * FROM `purchase` WHERE `user` IN ({$userList}) AND LOWER(IFNULL(delivery_status,'')) NOT IN ('cancelled','delivered') AND LOWER(IFNULL(status,'')) NOT IN ('cancelled','delivered') ORDER BY pdate DESC";
                                $result=mysqli_query($con,$sql);
                            }

                            if($result && mysqli_num_rows($result) > 0){
                                while($row=mysqli_fetch_assoc($result)){
                                    $raw_delivery = trim($row['delivery_status'] ?? '');
                                    $raw_status = trim($row['status'] ?? '');
                                    $status = strtolower($raw_delivery !== '' ? $raw_delivery : ($raw_status !== '' ? $raw_status : 'order_confirmed'));
                                    if($status === 'pending') $status = 'order_confirmed';
                                    if($status === 'shipped') $status = 'out_for_delivery';
                                    
                                    $badge_class = 'badge-' . $status;
                                    $status_label = ucfirst(str_replace('_', ' ', $status));
                                    if($status === 'order_confirmed') $status_label = 'Order Confirmed';
                                    if($status === 'out_for_delivery') $status_label = 'Out for Delivery';
                                    
                                    // Icons
                                    $icon = 'bi-hourglass-split';
                                    if($status == 'delivered') $icon = 'bi-check-circle-fill';
                                    if($status == 'out_for_delivery') $icon = 'bi-truck';
                                    if($status == 'cancelled') $icon = 'bi-x-circle-fill';

                                    $total = $row['pprice'] * $row['qty'];
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bi bi-box-seam text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['pname']); ?></div>
                                            <div class="small text-muted">ID: #ORD-<?php echo $row['pid']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-semibold"><?php echo (int)$row['qty']; ?></td>
                                <td>₹<?php echo number_format((float)$row['pprice'], 2); ?></td>
                                <td class="fw-bold text-primary">₹<?php echo number_format((float)$total, 2); ?></td>
                                <td>
                                    <span class="badge-soft <?php echo $badge_class; ?>">
                                        <i class="bi <?php echo $icon; ?>"></i> <?php echo $status_label; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <form action="myorder_details.php" method="post">
                                            <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                                            <button type="submit" class="btn-icon btn-view" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </form>
                                        <?php if($view !== 'history' && in_array($status, ['pending','order_confirmed'], true)): ?>
                                            <form action="cancel_order.php" method="post" onsubmit="return confirm('Cancel Order?');">
                                                <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                                                <button type="submit" class="btn-icon btn-cancel" title="Cancel Order">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6'><div class='empty-state'><i class='bi bi-cart-x empty-icon'></i><h5>No orders found</h5><a href='view_products.php' class='btn btn-sm btn-primary mt-2'>Start Shopping</a></div></td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include('footer.php'); ?>