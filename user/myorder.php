<?php
define('page','myorder');
include('header.php');
$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';
?>
<div class="container">
    <div class="row">
        <div class="col-12 reveal">
            <div class="orders-header">
                <div>
                    <h2 class="mb-1">Your Orders</h2>
                    <p class="text-muted mb-0">Track purchases, delivery status, and history.</p>
                </div>
                <div class="d-flex gap-2">
                    <?php if($view === 'history'): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="myorder.php">Active Orders</a>
                    <?php else: ?>
                        <a class="btn btn-sm btn-outline-secondary" href="myorder.php?view=history">Order History</a>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-primary" href="view_products.php">Shop More</a>
                </div>
            </div>

            <div class="orders-card">
                <div class="table-responsive">
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
                                    $sql="SELECT * FROM `purchase` WHERE `user` IN ({$userList}) ORDER BY pdate DESC";
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
                                                    <form action="cancel_order.php" method="post" onsubmit="return confirm('Cancel this order?');">
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
                                    echo "<tr><td colspan='6' class='text-center small-muted py-4'>No orders found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>