<?php
include('header.php');
include('conn.php');

// Handle accept action POSTed from this page (accept a pending build)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept'){
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  if($id>0){
    // ensure builds table has status column
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='builds' AND COLUMN_NAME='status' LIMIT 1";
    $cres = mysqli_query($con, $col_check);
    if(!$cres || mysqli_num_rows($cres)===0){ @mysqli_query($con, "ALTER TABLE builds ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'"); }

    // fetch build
    $bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
    if($bq && mysqli_num_rows($bq)>0){
      $build = mysqli_fetch_assoc($bq);
      if(($build['status'] ?? 'pending') === 'pending'){
        // fetch items
        $items_q = "SELECT bi.*, p.pname AS product_name FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'";
        $items_r = mysqli_query($con, $items_q);
        // ensure purchase table exists
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

        if($items_r && mysqli_num_rows($items_r)>0){
          // determine correct identifier for purchase.user — prefer user's email (so orders are visible in user My Orders)
          $userIdentifier = '';
          // if build stored an email-like user_name, use it
          $maybe = trim($build['user_name'] ?? '');
          if(!empty($maybe) && filter_var($maybe, FILTER_VALIDATE_EMAIL)){
            $userIdentifier = $maybe;
          }
          // otherwise, lookup by user_id to fetch c_email (preferred) or c_name
          if($userIdentifier === '' && !empty($build['user_id'])){
            $uid = intval($build['user_id']);
            $ru = mysqli_query($con, "SELECT c_email, c_name FROM cust_reg WHERE cid=$uid LIMIT 1");
            if($ru && mysqli_num_rows($ru)>0){ $ur = mysqli_fetch_assoc($ru); $userIdentifier = $ur['c_email'] ?? $ur['c_name'] ?? ''; }
          }
          if($userIdentifier === ''){ $userIdentifier = 'user_'.$build['user_id']; }
          $user = mysqli_real_escape_string($con, $userIdentifier);
          while($it = mysqli_fetch_assoc($items_r)){
            $pname = mysqli_real_escape_string($con, ($it['product_name'] ?: ('PID:'.$it['product_id'])) );
            $price = floatval($it['price']);
            $pid = intval($it['product_id']);
            $ins = "INSERT INTO purchase (pname, user, pprice, qty, prod_id, status, delivery_status) VALUES ('{$pname}', '{$user}', '{$price}', 1, '{$pid}', 'pending', 'pending')";
            mysqli_query($con, $ins);
          }
        }

        // update build status
        mysqli_query($con, "UPDATE builds SET status='accepted' WHERE id='$id' LIMIT 1");
        // after accepting and creating purchase rows, redirect admin to orders list to view the created orders
        header('Location: orders_list.php'); exit;
      }
    }
  }
  header('Location: builds.php'); exit;
}

$q = "SELECT * FROM builds ORDER BY created_at DESC";
$res = mysqli_query($con, $q);

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-hammer text-primary me-2"></i>Build Requests</h4>
        <div class="small-muted">User-submitted PC customizations</div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge badge-total"><i class="bi bi-list-check me-1"></i> <?php echo ($res) ? mysqli_num_rows($res) : 0; ?></span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Build Name</th>
            <th>User</th>
            <th>Status</th>
            <th>Total</th>
            <th>Created</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($res && mysqli_num_rows($res)>0){
  $i=1;
  while($r = mysqli_fetch_assoc($res)){
    $id = (int)$r['id'];
    $bname = htmlspecialchars($r['name']);
    $uname = htmlspecialchars($r['user_name'] ?: 'User#'.$r['user_id']);
    $status = htmlspecialchars($r['status'] ?? 'pending');
    $total = number_format((float)$r['total'],2);
    $created = $r['created_at'];
    echo "<tr>";
    echo "<td>{$i}</td>";
    echo "<td class='fw-semibold'>{$bname}</td>";
    echo "<td>{$uname}</td>";
    echo "<td><span class='badge ".($status==='pending'?'bg-warning text-dark':($status==='accepted'?'bg-info text-dark':($status==='processed'?'bg-success':'bg-secondary')))."'>".htmlspecialchars(ucfirst($status))."</span></td>";
    echo "<td>\${$total}</td>";
    echo "<td>{$created}</td>";
    echo "<td class='text-center'>";
    echo "<a class='btn btn-sm btn-outline-primary me-2' href='view_build.php?id={$id}'>View</a>";
    if($status === 'pending'){
      echo "<form action='builds.php' method='post' class='d-inline-block'>";
      echo "<input type='hidden' name='id' value='{$id}'>";
      echo "<input type='hidden' name='action' value='accept'>";
      echo "<button class='btn btn-sm btn-success' type='submit'>Accept</button>";
      echo "</form>";
    }
    echo "</td>";
    echo "</tr>";
    $i++;
  }
} else {
  echo "<tr><td colspan='6' class='text-center small-muted py-4'><i class='bi bi-exclamation-circle me-1'></i>No builds found</td></tr>";
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include('footer.php');

?>
