<?php
include('header.php');
include('conn.php');
include('../delivery/helpers.php');
ensure_builds_history_table($con);

$view = isset($_GET['view']) ? trim($_GET['view']) : 'active';

// Handle accept action POSTed from this page (accept a pending build)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  $action = trim($_POST['action']);
  
  if($id>0){
    // ensure builds table has status column
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='builds' AND COLUMN_NAME='status' LIMIT 1";
    $cres = mysqli_query($con, $col_check);
    if(!$cres || mysqli_num_rows($cres)===0){ @mysqli_query($con, "ALTER TABLE builds ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'"); }

    // fetch build
    $bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
    if($bq && mysqli_num_rows($bq)>0){
      $build = mysqli_fetch_assoc($bq);
      $build_status = $build['status'] ?? 'pending';
      
      if($action === 'accept' && $build_status === 'pending'){
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
      } elseif($action === 'complete' && $build_status !== 'pending'){
        // move to history and delete from active
        $user_id = intval($build['user_id'] ?? 0);
        $user_name = mysqli_real_escape_string($con, $build['user_name'] ?? '');
        $name = mysqli_real_escape_string($con, $build['name'] ?? '');
        $description = mysqli_real_escape_string($con, $build['description'] ?? '');
        $total = floatval($build['total'] ?? 0);
        $status = mysqli_real_escape_string($con, $build['status'] ?? 'pending');
        $created_at = mysqli_real_escape_string($con, $build['created_at'] ?? '');

        $ins = "INSERT INTO builds_history (id, user_id, user_name, name, description, total, status, created_at)
                VALUES ($id, $user_id, '$user_name', '$name', '$description', $total, '$status', ".
                (!empty($created_at) ? "'{$created_at}'" : "NULL").
                ")
                ON DUPLICATE KEY UPDATE status=VALUES(status), user_name=VALUES(user_name), description=VALUES(description)";
        @mysqli_query($con, $ins);

        @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
        header('Location: builds.php?view=history&msg=completed'); exit;
      }
    }
  }
  header('Location: builds.php'); exit;
}

// count queries
$active_count = 0;
$history_count = 0;
$c1 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds WHERE LOWER(IFNULL(status,'pending')) <> 'completed'");
if($c1 && mysqli_num_rows($c1)>0){ $active_count = (int)(mysqli_fetch_assoc($c1)['total'] ?? 0); }
$c2 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds_history");
if($c2 && mysqli_num_rows($c2)>0){ $history_count = (int)(mysqli_fetch_assoc($c2)['total'] ?? 0); }

// fetch data
$res = false;
$hist_res = false;
if($view === 'history'){
  $hist_res = @mysqli_query($con, "SELECT * FROM builds_history ORDER BY completed_at DESC");
} else {
  $res = @mysqli_query($con, "SELECT * FROM builds WHERE LOWER(IFNULL(status,'pending')) <> 'completed' ORDER BY created_at DESC");
}

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-hammer text-primary me-2"></i>Build Requests</h4>
        <div class="small-muted">User-submitted PC customizations</div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="btn-group me-2" role="group" aria-label="Build filters">
          <a href="builds.php" class="btn btn-sm btn-outline-secondary <?php echo $view==='active'?'active':''; ?>">Active <span class="ms-1"><?php echo $active_count; ?></span></a>
          <a href="builds.php?view=history" class="btn btn-sm btn-outline-secondary <?php echo $view==='history'?'active':''; ?>">History <span class="ms-1"><?php echo $history_count; ?></span></a>
        </div>
        <span class="badge badge-total"><i class="bi bi-list-check me-1"></i> <?php echo ($view==='history') ? $history_count : $active_count; ?></span>
      </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg']==='completed'): ?>
      <div class="alert alert-success mb-3">Build marked as complete and moved to history.</div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Build Name</th>
            <th>User</th>
            <th>Status</th>
            <th>Total</th>
            <th><?php echo $view === 'history' ? 'Completed' : 'Created'; ?></th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($view === 'history'){
  if($hist_res && mysqli_num_rows($hist_res)>0){
    $i=1;
    while($r = mysqli_fetch_assoc($hist_res)){
      $id = (int)$r['id'];
      $bname = htmlspecialchars($r['name']);
      $uname = htmlspecialchars($r['user_name'] ?: 'User#'.$r['user_id']);
      $status = htmlspecialchars($r['status'] ?? 'pending');
      $total = number_format((float)$r['total'],2);
      $date = $r['completed_at'];
      echo "<tr>";
      echo "<td>{$i}</td>";
      echo "<td class='fw-semibold'>{$bname}</td>";
      echo "<td>{$uname}</td>";
      echo "<td><span class='badge ".($status==='pending'?'bg-warning text-dark':($status==='accepted'?'bg-info text-dark':($status==='processed'||$status==='completed'?'bg-success':'bg-secondary')))."'>".htmlspecialchars(ucfirst($status))."</span></td>";
      echo "<td>\${$total}</td>";
      echo "<td>{$date}</td>";
      echo "<td class='text-center'>";
      echo "<a class='btn btn-sm btn-outline-primary' href='view_build.php?id={$id}'>View</a>";
      echo "</td>";
      echo "</tr>";
      $i++;
    }
  } else {
    echo "<tr><td colspan='7' class='text-center small-muted py-4'><i class='bi bi-exclamation-circle me-1'></i>No builds in history</td></tr>";
  }
} else {
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
      echo "<a class='btn btn-sm btn-outline-primary' href='view_build.php?id={$id}'>View</a>";
      if($status === 'pending'){
        echo "<form action='builds.php' method='post' class='d-inline-block'>";
        echo "<input type='hidden' name='id' value='{$id}'>";
        echo "<input type='hidden' name='action' value='accept'>";
        echo "<button class='btn btn-sm btn-success' type='submit'>Accept</button>";
        echo "</form>";
      } elseif($status !== 'pending'){
        echo "<form action='builds.php' method='post' class='d-inline-block' onsubmit='return confirm(\"Mark as complete and move to history?\");'>";
        echo "<input type='hidden' name='id' value='{$id}'>";
        echo "<input type='hidden' name='action' value='complete'>";
        echo "<button class='btn btn-sm btn-outline-warning' type='submit'>Complete</button>";
        echo "</form>";
      }
      echo "</td>";
      echo "</tr>";
      $i++;
    }
  } else {
    echo "<tr><td colspan='7' class='text-center small-muted py-4'><i class='bi bi-exclamation-circle me-1'></i>No builds found</td></tr>";
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
