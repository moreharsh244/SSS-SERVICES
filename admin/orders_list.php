<?php
include('header.php');
include('conn.php');
include('../delivery/helpers.php');
ensure_delivery_tables($con);

$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';

// ensure purchase table exists (defensive)
$create = "CREATE TABLE IF NOT EXISTS `purchase` (
    `pid` INT AUTO_INCREMENT PRIMARY KEY,
    `pname` VARCHAR(255) NOT NULL,
    `user` VARCHAR(255) NOT NULL,
    `pprice` DECIMAL(10,2) NOT NULL,
    `qty` INT NOT NULL DEFAULT 1,
    `prod_id` INT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `delivery_status` VARCHAR(50) DEFAULT 'pending',
    `assigned_agent` VARCHAR(100) DEFAULT NULL,
    `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($con, $create);

// ensure assigned_agent column exists
$col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase' AND COLUMN_NAME='assigned_agent'";
$col_res = mysqli_query($con, $col_check);
if(!$col_res || mysqli_num_rows($col_res)===0){
  @mysqli_query($con, "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

// active delivery agents list
$agents = [];
$ares = mysqli_query($con, "SELECT username FROM del_login WHERE is_active=1 ORDER BY username");
if($ares){
  while($ar = mysqli_fetch_assoc($ares)){
    $agents[] = $ar['username'];
  }
}

// Default: show active orders. If ?view=history, show archived (delivered) orders from purchase_history.
$res = false;
$table_missing = false;
$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }

// counts for quick filters
$count_all = 0;
$count_pending = 0;
$count_assigned = 0;
$count_history = 0;
$count_res = @mysqli_query($con, "SELECT COUNT(*) AS total,
  SUM(CASE WHEN LOWER(IFNULL(delivery_status,'pending'))='pending' THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN IFNULL(assigned_agent,'') <> '' THEN 1 ELSE 0 END) AS assigned
  FROM purchase");
if($count_res && mysqli_num_rows($count_res)>0){
  $cr = mysqli_fetch_assoc($count_res);
  $count_all = (int)($cr['total'] ?? 0);
  $count_pending = (int)($cr['pending'] ?? 0);
  $count_assigned = (int)($cr['assigned'] ?? 0);
}
if($db){
  $tbl = mysqli_real_escape_string($con, 'purchase_history');
  $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
  if($qc && mysqli_num_rows($qc)>0){
    $hc = @mysqli_query($con, "SELECT COUNT(*) AS total FROM purchase_history WHERE LOWER(IFNULL(delivery_status,'')) IN ('cancelled','delivered')");
    if($hc && mysqli_num_rows($hc)>0){ $count_history = (int)(mysqli_fetch_assoc($hc)['total'] ?? 0); }
  } else {
    $table_missing = true;
  }
}

if($view === 'history'){
  if($table_missing){
    $res = false;
  } else {
    $q = "SELECT h.*, p.pname AS prod_name, p.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase_history h LEFT JOIN products p ON h.prod_id = p.pid LEFT JOIN cust_reg c ON (h.user = c.c_email OR h.user = c.c_name) WHERE LOWER(IFNULL(h.delivery_status,'')) IN ('cancelled','delivered') ORDER BY pdate DESC";
    $res = @mysqli_query($con, $q);
  }
} elseif($view === 'pending'){
  $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) WHERE LOWER(IFNULL(purchase.delivery_status,'pending'))='pending' ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
} elseif($view === 'assigned'){
  $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) WHERE IFNULL(purchase.assigned_agent,'')<>'' AND LOWER(IFNULL(purchase.delivery_status,'pending')) NOT IN ('delivered','cancelled') ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
} else {
  $q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img, c.c_name AS customer_name, c.c_email AS customer_email FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid LEFT JOIN cust_reg c ON (purchase.user = c.c_email OR purchase.user = c.c_name) ORDER BY pdate DESC";
  $res = mysqli_query($con, $q);
}
?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card reveal">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-bag-check text-primary me-2"></i>Orders</h4>
        <div class="small-muted">Customer purchases and order management</div>
      </div>
      <div>
        <div class="btn-group me-2" role="group" aria-label="Order filters">
          <a href="orders_list.php" class="btn btn-sm btn-outline-secondary <?php echo $view==='list'?'active':''; ?>">All <span class="ms-1">(<?php echo $count_all; ?>)</span></a>
          <a href="orders_list.php?view=pending" class="btn btn-sm btn-outline-secondary <?php echo $view==='pending'?'active':''; ?>">Pending <span class="ms-1">(<?php echo $count_pending; ?>)</span></a>
          <a href="orders_list.php?view=assigned" class="btn btn-sm btn-outline-secondary <?php echo $view==='assigned'?'active':''; ?>">Assigned <span class="ms-1">(<?php echo $count_assigned; ?>)</span></a>
          <a href="orders_list.php?view=history" class="btn btn-sm btn-outline-secondary <?php echo $view==='history'?'active':''; ?>">History <span class="ms-1">(<?php echo $count_history; ?>)</span></a>
        </div>
        <span class="badge badge-total"><i class="bi bi-list-check me-1"></i> <?php echo ($view==='history') ? $count_history : (($res && is_object($res)) ? mysqli_num_rows($res) : 0); ?></span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>User</th>
            <th>Qty</th>
            <th>Total</th>
            <?php if($view !== 'history'): ?>
              <th>Status</th>
              <th>Delivery</th>
              <th>Agent</th>
            <?php else: ?>
              <th>Delivery</th>
              <th>Agent</th>
            <?php endif; ?>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
<?php
if($view === 'history'){
  if($table_missing){
    echo "<tr><td colspan='8' class='text-center small-muted py-4'>No history records found</td></tr>";
  } elseif($res && mysqli_num_rows($res)>0){
    $i=1;
    while($r = mysqli_fetch_assoc($res)){
      $id = (int)$r['pid'];
      $prod = htmlspecialchars($r['prod_name'] ?: $r['pname']);
      $user = htmlspecialchars($r['customer_name'] ?: $r['user']);
      $agent = htmlspecialchars($r['assigned_agent'] ?? '');
      $qty = (int)$r['qty'];
      $total = number_format((float)$r['pprice'] * $qty,2);
      $dstatus = htmlspecialchars($r['delivery_status'] ?? 'delivered');
      $date = $r['pdate'];
      $img = (!empty($r['prod_img'])) ? '../productimg/'.rawurlencode($r['prod_img']) : '';

      echo "<tr>";
      echo "<td>{$i}</td>";
      echo "<td class='fw-semibold'>".($img ? "<img src='{$img}' style='width:64px;height:48px;object-fit:cover;margin-right:8px' class='img-preview rounded' data-full='{$img}'>" : "")." {$prod}</td>";
      echo "<td>{$user}</td>";
      echo "<td>{$qty}</td>";
      echo "<td>₹{$total}</td>";
      $dlower = strtolower($dstatus);
      if($dlower === 'cancelled'){
        $hist_cls = 'bg-danger';
      } elseif($dlower === 'delivered'){
        $hist_cls = 'bg-success';
      } else {
        $hist_cls = 'bg-secondary';
      }
      echo "<td><span class='badge {$hist_cls}'>".htmlspecialchars(ucfirst($dstatus))."</span></td>";
      echo "<td>".($agent !== '' ? $agent : "-")."</td>";
      echo "<td>{$date}</td>";
      // no actions in history view
      echo "</tr>";
      $i++;
    }
  } else {
    echo "<tr><td colspan='8' class='text-center small-muted py-4'>No history records found</td></tr>";
  }
} else {
  if($res && mysqli_num_rows($res)>0){
    $i=1;
    while($r = mysqli_fetch_assoc($res)){
      $id = (int)$r['pid'];
      $prod = htmlspecialchars($r['prod_name'] ?: $r['pname']);
      $user = htmlspecialchars($r['customer_name'] ?: $r['user']);
      $qty = (int)$r['qty'];
      $total = number_format((float)$r['pprice'] * $qty,2);
      $status = htmlspecialchars($r['status'] ?? 'pending');
      $dstatus = htmlspecialchars($r['delivery_status'] ?? 'pending');
      $agent = htmlspecialchars($r['assigned_agent'] ?? '');

      // map status to bootstrap badge classes
      $badge_map = [
        'pending' => 'bg-warning text-dark',
        'shipped' => 'bg-info text-dark',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger'
      ];
      $status_cls = $badge_map[strtolower($status)] ?? 'bg-secondary';
      $dstatus_cls = $badge_map[strtolower($dstatus)] ?? 'bg-secondary';
      $status_label = ucfirst($status);
      $dstatus_label = ucfirst($dstatus);
      $date = $r['pdate'];
      $img = (!empty($r['prod_img'])) ? '../productimg/'.rawurlencode($r['prod_img']) : '';

      echo "<tr>";
      echo "<td>{$i}</td>";
      echo "<td class='fw-semibold'>".($img ? "<img src='{$img}' style='width:64px;height:48px;object-fit:cover;margin-right:8px' class='img-preview rounded' data-full='{$img}'>" : "")." {$prod}</td>";
      echo "<td>{$user}</td>";
      echo "<td>{$qty}</td>";
      echo "<td>₹{$total}</td>";
      echo "<td><span class='badge {$status_cls}'>{$status_label}</span></td>";
      echo "<td><span class='badge {$dstatus_cls}'>{$dstatus_label}</span></td>";
      echo "<td>";
      echo "<form action='assign_delivery.php' method='post' class='d-flex gap-2 align-items-center'>";
      echo "<input type='hidden' name='order_id' value='{$id}'>";
      echo "<select name='assigned_agent' class='form-select form-select-sm'>";
      echo "<option value=''>Unassigned</option>";
      foreach($agents as $ag){
        $ag_esc = htmlspecialchars($ag);
        $sel = ($ag === ($r['assigned_agent'] ?? '')) ? 'selected' : '';
        echo "<option value='{$ag_esc}' {$sel}>{$ag_esc}</option>";
      }
      echo "</select>";
      echo "<button class='btn btn-sm btn-outline-primary' type='submit'>Assign</button>";
      echo "</form>";
      echo "</td>";
      echo "<td>{$date}</td>";
      echo "</tr>";
      $i++;
    }
  } else {
    echo "<tr><td colspan='9' class='text-center small-muted py-4'>No orders found</td></tr>";
  }
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include('footer.php');

?>
