<?php
define('page','myorder');
include('header.php');
$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';
$is_history = ($view === 'history');
$is_service = ($view === 'service');
?>
<div class="container">
    <div class="row">
        <div class="col-12 reveal">
            <div class="orders-header">
                <div>
                    <h2 class="mb-1">Your Orders</h2>
                    <p class="text-muted mb-0">Track your purchases, delivery status, and order history.</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-secondary <?php echo (!$is_history && !$is_service) ? 'active' : ''; ?>" href="myorder.php">Active Orders</a>
                    <a class="btn btn-sm btn-outline-secondary <?php echo $is_history ? 'active' : ''; ?>" href="myorder.php?view=history">Order History</a>
                    <a class="btn btn-sm btn-outline-secondary <?php echo $is_service ? 'active' : ''; ?>" href="myorder.php?view=service">Service Requests</a>
                    <a class="btn btn-sm btn-primary" href="view_products.php">Continue Shopping</a>
                </div>
            </div>

            <div class="orders-card">
                <div class="table-responsive">
                    <?php if($is_service): ?>
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Response</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
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
                                    // allow matching either by email/username or by fallback user_<id> used for legacy builds
                                    $possibleUsers = [ $sessionUserEsc ];
                                    if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
                                    $userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";

                                    $result = mysqli_query($con, "SELECT * FROM service_requests WHERE `user` IN ({$userList}) ORDER BY created_at DESC");
                                    if($result && mysqli_num_rows($result) > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $status = strtolower(trim($row['status'] ?? 'pending'));
                                            $badge_map = [
                                                'pending' => 'badge-soft badge-pending',
                                                'in_progress' => 'badge-soft badge-shipped',
                                                'completed' => 'badge-soft badge-delivered',
                                                'cancelled' => 'badge-soft badge-cancelled'
                                            ];
                                            $status_label = ucfirst(str_replace('_',' ',$status));
                                            $badge_class = $badge_map[$status] ?? 'badge-soft badge-default';
                                            $assigned_agent = trim($row['assigned_agent'] ?? '');
                                            $agent_note = trim($row['agent_note'] ?? '');
                                            $response = '';
                                            if($assigned_agent !== ''){
                                                $response = 'Assigned to ' . htmlspecialchars($assigned_agent);
                                            }
                                            if($agent_note !== ''){
                                                $response = ($response !== '' ? $response . ' - ' : '') . htmlspecialchars(mb_strimwidth($agent_note, 0, 80, '...'));
                                            }
                                            if($response === ''){
                                                $response = 'Pending Review';
                                            }
                                            $can_cancel = ($status === 'pending' && $assigned_agent === '');
                                ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($row['item'] ?? ''); ?></div>
                                                <div class="small text-muted">#<?php echo (int)$row['id']; ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['service_type'] ?? ''); ?></td>
                                            <td><span class="<?php echo $badge_class; ?>"><?php echo $status_label; ?></span></td>
                                            <td><?php echo $response; ?></td>
                                            <td><?php echo !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : ''; ?></td>
                                            <td class="text-end">
                                                <?php if($can_cancel): ?>
                                                    <form action="cancel_order.php" method="post" onsubmit="return confirm('Are you sure you want to cancel this service request?');" class="d-inline">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$row['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center small-muted py-4'>No service requests available.</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    include('../admin/conn.php');
                                    $sessionUser = $_SESSION['username'] ?? '';
                                    $sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
                                    $sessionUid = $_SESSION['user_id'] ?? null;
                                    // allow matching either by email/username or by fallback user_<id> used for legacy builds
                                    $possibleUsers = [ $sessionUserEsc ];
                                    if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
                                    $userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";
                                    if($view === 'history'){
                                        // show delivered/archive orders from purchase_history if table exists
                                        $db = '';
                                        $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
                                        $show_res = false;
                                        if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
                                        if($db){
                                            $tbl = mysqli_real_escape_string($con, 'purchase_history');
                                            $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
                                            if($qc && mysqli_num_rows($qc)>0){
                                                // show cancelled and delivered orders in user history (match possible user identifiers)
                                                $sql = "SELECT * FROM `purchase_history` WHERE `user` IN ({$userList}) AND LOWER(IFNULL(delivery_status,'')) IN ('cancelled','delivered') ORDER BY pdate DESC";
                                                $show_res = true;
                                            }
                                        }
                                        if(!$show_res){ $result = false; }
                                        else { $result = mysqli_query($con,$sql); }
                                    } else {
                                        $sql="SELECT * FROM `purchase` WHERE `user` IN ({$userList}) AND LOWER(IFNULL(delivery_status,'')) NOT IN ('cancelled','delivered') AND LOWER(IFNULL(status,'')) NOT IN ('cancelled','delivered') ORDER BY pdate DESC";
                                        $result=mysqli_query($con,$sql);
                                    }
                                    if($result && mysqli_num_rows($result) > 0){
                                        while($row=mysqli_fetch_assoc($result)){
                                            $raw_delivery = trim($row['delivery_status'] ?? '');
                                            $raw_status = trim($row['status'] ?? '');
                                            $status = strtolower($raw_delivery !== '' ? $raw_delivery : ($raw_status !== '' ? $raw_status : 'pending'));
                                            $badge_map = [
                                                'pending' => 'badge-soft badge-pending',
                                                'shipped' => 'badge-soft badge-shipped',
                                                'delivered' => 'badge-soft badge-delivered',
                                                'cancelled' => 'badge-soft badge-cancelled'
                                            ];
                                            $status_label = ucfirst($status);
                                            $badge_class = $badge_map[$status] ?? 'badge-soft badge-default';
                                            $total = $row['pprice'] * $row['qty'];
                                ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($row['pname']); ?></div>
                                            </td>
                                            <td><?php echo (int)$row['qty']; ?></td>
                                            <td>₹<?php echo number_format((float)$row['pprice'], 2); ?></td>
                                            <td>₹<?php echo number_format((float)$total, 2); ?></td>
                                            <td><span class="<?php echo $badge_class; ?>"><?php echo $status_label; ?></span></td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    <form action="myorder_details.php" method="post">
                                                        <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                                                        <button type="submit" class="btn btn-outline-primary btn-sm">View Details</button>
                                                    </form>
                                                    <?php if($view !== 'history' && $status === 'pending'): ?>
                                                        <form action="cancel_order.php" method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                                            <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center small-muted py-4'>No orders available.</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>