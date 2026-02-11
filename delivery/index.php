<?php
include('header.php');
?>
<div class="container">
    <div class="col-12 col-lg-10 mx-auto">
        <?php
        include '../admin/conn.php';
        include 'helpers.php';
        ensure_purchase_table($con);
        ensure_service_requests_table($con);
        $agent_raw = $_SESSION['username'] ?? '';
        $agent = mysqli_real_escape_string($con, $agent_raw);
        $statSql = "SELECT COUNT(*) AS total,
            SUM(CASE WHEN LOWER(IFNULL(delivery_status,'pending')) NOT IN ('delivered','cancelled') THEN 1 ELSE 0 END) AS pending
            FROM purchase WHERE assigned_agent='$agent'";
        $statRes = mysqli_query($con, $statSql);
        $stats = ['total'=>0,'pending'=>0,'delivered'=>0];
        if($statRes && mysqli_num_rows($statRes)>0){ $stats = mysqli_fetch_assoc($statRes); }

        // delivered/cancelled orders are archived in purchase_history
        $delivered_count = 0;
        $cancelled_count = 0;
        $db = '';
        $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
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
                    <div class="small text-muted">Delivered</div>
                    <div class="h3 mb-0"><?php echo (int)($stats['delivered'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="delivery-card p-3 fade-in reveal">
                    <div class="small text-muted">Cancelled</div>
                    <div class="h3 mb-0"><?php echo (int)($stats['cancelled'] ?? 0); ?></div>
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
            ?>
                <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $pname; ?></td>
                    <td><?php echo $user; ?></td>
                    <td><?php echo $qty; ?></td>
                    <td>₹<?php echo $total; ?></td>
                        <td><span class="badge <?php echo $badge; ?>">Pending</span></td>
                        <td>
                            <form action="update_status.php" method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                                <select name="delivery_status" class="form-select form-select-sm">
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-delivery btn-sm">Update</button>
                            </form>
                        </td>
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
        <div class="delivery-panel reveal mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Cancelled Deliveries</h5>
                <span class="text-muted small">Orders cancelled by you</span>
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
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $cancel_res = false;
                    if($db){
                        $tbl = mysqli_real_escape_string($con, 'purchase_history');
                        $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
                        if($qc && mysqli_num_rows($qc)>0){
                            $cancel_sql = "SELECT * FROM purchase_history WHERE assigned_agent='$agent' AND LOWER(IFNULL(delivery_status,''))='cancelled' ORDER BY pdate DESC";
                            $cancel_res = mysqli_query($con, $cancel_sql);
                        }
                    }
                    if($cancel_res && mysqli_num_rows($cancel_res)>0){
                        while($row = mysqli_fetch_assoc($cancel_res)){
                            $id = (int)$row['pid'];
                            $pname = htmlspecialchars($row['pname'] ?? '');
                            $user = htmlspecialchars($row['user'] ?? '');
                            $qty = (int)($row['qty'] ?? 0);
                            $total = number_format(((float)($row['pprice'] ?? 0) * $qty), 2);
                            $date = $row['pdate'];
                            echo "<tr>";
                            echo "<td>{$id}</td>";
                            echo "<td>{$pname}</td>";
                            echo "<td>{$user}</td>";
                            echo "<td>{$qty}</td>";
                            echo "<td>₹{$total}</td>";
                            echo "<td><span class='badge badge-cancelled'>Cancelled</span></td>";
                            echo "<td>{$date}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No cancelled deliveries</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="delivery-panel reveal mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Service Requests</h5>
                <span class="text-muted small">All user support requests</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-delivery">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $req_res = mysqli_query($con, "SELECT * FROM service_requests ORDER BY created_at DESC");
                    if($req_res && mysqli_num_rows($req_res) > 0){
                        while($row = mysqli_fetch_assoc($req_res)){
                            $id = (int)$row['id'];
                            $user = htmlspecialchars($row['user'] ?? '');
                            $item = htmlspecialchars($row['item'] ?? '');
                            $stype = htmlspecialchars($row['service_type'] ?? '');
                            $details = htmlspecialchars(mb_strimwidth($row['details'] ?? '', 0, 90, '...'));
                            $status = strtolower($row['status'] ?? 'pending');
                            $assigned = $row['assigned_agent'] ?? '';
                            $note = htmlspecialchars($row['agent_note'] ?? '');

                            $status_map = [
                                'pending' => 'badge-pending',
                                'in_progress' => 'badge-shipped',
                                'completed' => 'badge-delivered',
                                'cancelled' => 'badge-cancelled'
                            ];
                            $badge = $status_map[$status] ?? 'badge-pending';
                            $status_label = ucfirst(str_replace('_',' ', $status));

                            echo "<tr>";
                            echo "<td>{$id}</td>";
                            echo "<td>{$user}</td>";
                            echo "<td>{$item}</td>";
                            echo "<td>{$stype}</td>";
                            echo "<td>{$details}</td>";
                            echo "<td><span class='badge {$badge}'>".htmlspecialchars($status_label)."</span></td>";
                            echo "<td>".($assigned !== '' ? htmlspecialchars($assigned) : "-")."</td>";
                            echo "<td>".($note !== '' ? $note : "-")."</td>";
                            echo "<td>";
                            if($assigned !== '' && $assigned === $agent_raw){
                                echo "<form action='update_service_request.php' method='post' class='d-flex gap-2 align-items-start flex-wrap'>";
                                echo "<input type='hidden' name='id' value='{$id}'>";
                                echo "<select name='status' class='form-select form-select-sm' style='min-width:140px'>";
                                $opts = ['pending','in_progress','completed','cancelled'];
                                foreach($opts as $o){
                                    $sel = ($o === $status) ? 'selected' : '';
                                    echo "<option value='{$o}' {$sel}>".ucfirst(str_replace('_',' ',$o))."</option>";
                                }
                                echo "</select>";
                                echo "<input type='text' name='agent_note' class='form-control form-control-sm' placeholder='Add note' value='{$note}' style='min-width:180px'>";
                                echo "<button type='submit' class='btn btn-delivery btn-sm'>Update</button>";
                                echo "</form>";
                            } else {
                                echo "<span class='text-muted small'>".($assigned === '' ? "Unassigned" : "Assigned to {$assigned}")."</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No service requests</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
include('footer.php');  
?>