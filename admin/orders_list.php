<?php
include('header.php');
include('conn.php');

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
    `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($con, $create);

$q = "SELECT purchase.*, products.pname AS prod_name, products.pimg AS prod_img FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid ORDER BY pdate DESC";
$res = mysqli_query($con, $q);
?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-bag-check text-primary me-2"></i>Orders List</h4>
        <div class="small-muted">Customer purchases</div>
      </div>
      <div>
        <span class="badge badge-total"><i class="bi bi-list-check me-1"></i> <?php echo ($res) ? mysqli_num_rows($res) : 0; ?></span>
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
            <th>Status</th>
            <th>Delivery</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($res && mysqli_num_rows($res)>0){
  $i=1;
  while($r = mysqli_fetch_assoc($res)){
    $id = (int)$r['pid'];
    $prod = htmlspecialchars($r['prod_name'] ?: $r['pname']);
    $user = htmlspecialchars($r['user']);
    $qty = (int)$r['qty'];
    $total = number_format((float)$r['pprice'] * $qty,2);
    $status = htmlspecialchars($r['status'] ?? 'pending');
    $dstatus = htmlspecialchars($r['delivery_status'] ?? 'pending');

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
    echo "<td>\${$total}</td>";
    echo "<td><span class='badge {$status_cls}'>{$status_label}</span></td>";
    echo "<td><span class='badge {$dstatus_cls}'>{$dstatus_label}</span></td>";
    echo "<td>{$date}</td>";
    // nicer actions: view button + compact select + update button
    echo "<td class='text-center'>";
    echo "<div class='d-inline-flex align-items-center'>";
    echo "<a href='view_purchase.php?id={$id}' class='btn btn-sm btn-primary me-2' title='View'><i class='bi bi-eye'></i> View</a>";
    echo "<form action='update_order.php' method='post' class='d-inline-flex align-items-center'>";
    echo "<input type='hidden' name='id' value='{$id}'>";
    echo "<div class='input-group input-group-sm' style='min-width:180px'>";
    echo "<select name='delivery_status' class='form-select form-select-sm'>";
    $opts = ['pending','shipped','delivered','cancelled'];
    foreach($opts as $o){ $sel = ($o===$dstatus)?'selected':''; echo "<option value='{$o}' {$sel}>".ucfirst($o)."</option>"; }
    echo "</select>";
    echo "<button class='btn btn-success' type='submit'>Update</button>";
    echo "</div>"; // input-group
    echo "</form>";
    echo "</div>";
    echo "</td>";
    echo "</tr>";
    $i++;
  }
} else {
  echo "<tr><td colspan='9' class='text-center small-muted py-4'>No orders found</td></tr>";
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include('footer.php');

?>
