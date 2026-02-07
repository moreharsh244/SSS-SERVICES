<?php
include('header.php');
include('conn.php');

// check table exists
$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
$tbl_missing = false;
if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
$res = false;
if($db){
  $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='service_requests' LIMIT 1");
  if($qc && mysqli_num_rows($qc)>0){
    $res = @mysqli_query($con, "SELECT * FROM service_requests ORDER BY created_at DESC");
  } else {
    $tbl_missing = true;
  }
} else {
  $tbl_missing = true;
}
?>
<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-tools text-primary me-2"></i>Service Requests</h4>
        <div class="small-muted">Requests from users</div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Item / Title</th>
            <th>Type</th>
            <th>Details</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($tbl_missing){
  echo "<tr><td colspan='8' class='text-center small-muted py-4'>No service requests found</td></tr>";
} elseif($res && mysqli_num_rows($res)>0){
  $i=1;
  while($r = mysqli_fetch_assoc($res)){
    $id = (int)$r['id'];
    $user = htmlspecialchars($r['user']);
    $item = htmlspecialchars($r['item']);
    $type = htmlspecialchars($r['service_type']);
    $details = htmlspecialchars(mb_strimwidth($r['details'],0,120,'...'));
    $status = htmlspecialchars($r['status']);
    $date = $r['created_at'];
    echo "<tr>";
    echo "<td>{$i}</td>";
    echo "<td>{$user}</td>";
    echo "<td class='fw-semibold'>{$item}</td>";
    echo "<td>{$type}</td>";
    echo "<td>{$details}</td>";
    echo "<td><span class='badge ".($status==='pending'?'bg-warning text-dark':($status==='in_progress'?'bg-info text-dark':($status==='completed'?'bg-success':'bg-secondary')))."'>{$status}</span></td>";
    echo "<td>{$date}</td>";
    echo "<td class='text-center'>";
    echo "<a href='view_service.php?id={$id}' class='btn btn-sm btn-primary me-2'>View</a>";
    echo "<form method='post' action='update_service.php' class='d-inline-flex'>";
    echo "<input type='hidden' name='id' value='{$id}'>";
    echo "<select name='status' class='form-select form-select-sm me-2'>";
    $opts = ['pending','in_progress','completed','cancelled'];
    foreach($opts as $o){ echo "<option value='{$o}'>".ucfirst($o)."</option>"; }
    echo "</select>";
    echo "<button class='btn btn-sm btn-success' type='submit'>Update</button>";
    echo "</form>";
    echo "</td>";
    echo "</tr>";
    $i++;
  }
} else {
  echo "<tr><td colspan='8' class='text-center small-muted py-4'>No service requests found</td></tr>";
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php include('footer.php'); ?>
