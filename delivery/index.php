<?php
include('header.php');
?>
<div class="container">
    <div class="col-12 col-lg-10 mx-auto">
        <?php
        include '../admin/conn.php';
        include 'helpers.php';
        ensure_purchase_table($con);
        $agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');
        $statSql = "SELECT COUNT(*) AS total,
            SUM(CASE WHEN LOWER(IFNULL(delivery_status,'pending'))='pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN LOWER(IFNULL(delivery_status,''))='shipped' THEN 1 ELSE 0 END) AS shipped,
            SUM(CASE WHEN LOWER(IFNULL(delivery_status,''))='delivered' THEN 1 ELSE 0 END) AS delivered
            FROM purchase WHERE assigned_agent='$agent'";
        $statRes = mysqli_query($con, $statSql);
        $stats = ['total'=>0,'pending'=>0,'shipped'=>0,'delivered'=>0];
        if($statRes && mysqli_num_rows($statRes)>0){ $stats = mysqli_fetch_assoc($statRes); }
        ?>

        <div class="delivery-hero mb-4 fade-in reveal">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Delivery Dashboard</h2>
                    <div class="text-muted">Track and update your assigned deliveries.</div>
                </div>
                <div class="stat-pill">
                    <i class="bi bi-truck"></i>
                    Assigned: <?php echo (int)($stats['total'] ?? 0); ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="delivery-card p-3 fade-in reveal">
                    <div class="small text-muted">Pending</div>
                    <div class="h3 mb-0"><?php echo (int)($stats['pending'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="delivery-card p-3 fade-in reveal">
                    <div class="small text-muted">Shipped</div>
                    <div class="h3 mb-0"><?php echo (int)($stats['shipped'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="delivery-card p-3 fade-in reveal">
                    <div class="small text-muted">Delivered</div>
                    <div class="h3 mb-0"><?php echo (int)($stats['delivered'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="delivery-panel reveal">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Active Deliveries</h5>
                <span class="text-muted small">Only orders assigned to you</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-delivery">
                    <thead>
                            <tr>
                                    <th>Order #</th>
                                    <th>Product</th>
                                    <th>User</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Delivery Status</th>
                                    <th>Action</th>
                            </tr>
                    </thead>
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
                    $dstatus = strtolower($row['delivery_status'] ?? 'pending');
                    $badge = 'badge-pending';
                    if($dstatus === 'shipped'){ $badge = 'badge-shipped'; }
                    if($dstatus === 'delivered'){ $badge = 'badge-delivered'; }
                    if($dstatus === 'cancelled'){ $badge = 'badge-cancelled'; }
            ?>
                <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $pname; ?></td>
                    <td><?php echo $user; ?></td>
                    <td><?php echo $qty; ?></td>
                    <td>₹<?php echo $total; ?></td>
                    <td>
                            <form action="update_status.php" method="post" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                                    <select name="delivery_status" class="form-select form-select-sm">
                                            <option value="pending" <?php echo $dstatus==='pending'?'selected':''; ?>>Pending</option>
                                            <option value="shipped" <?php echo $dstatus==='shipped'?'selected':''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $dstatus==='delivered'?'selected':''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $dstatus==='cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                    </td>
                    <td>
                            <button type="submit" class="btn btn-delivery btn-sm">Update</button>
                    </td>
                            </form>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No active deliveries</td></tr>";
            }
            ?>
            </table>
        </div>
        </div>
    </div>
</div>
<?php
include('footer.php');  
?>