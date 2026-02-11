<?php
include('header.php');
include('conn.php');
include('../delivery/helpers.php');
ensure_delivery_tables($con);
ensure_service_requests_table($con);

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

// active delivery agents list
$agents = [];
$ares = mysqli_query($con, "SELECT username FROM del_login WHERE is_active=1 ORDER BY username");
if($ares){
  while($ar = mysqli_fetch_assoc($ares)){
    $agents[] = $ar['username'];
  }
}
?>
<div class="col-12 mx-auto">
  <div class="admin-card admin-card-wide">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-tools text-primary me-2"></i>Support Requests</h4>
        <div class="small-muted">Requests submitted by users</div>
      </div>
        <div>
          <span class="badge bg-secondary"><i class="bi bi-list-check me-1"></i> <?php echo ($res && is_object($res)) ? mysqli_num_rows($res) : 0; ?></span>
        </div>
      </div>

    <div>
      <table class="table table-hover align-middle table-requests">
        <thead class="table-light">
          <tr>
            <th class="col-num">#</th>
            <th class="col-user">User</th>
            <th class="col-item">Item / Title</th>
            <th class="col-type">Type</th>
            <th class="col-details">Details</th>
            <th class="col-status">Status</th>
            <th class="col-agent">Agent</th>
            <th class="col-date">Date</th>
            <th class="col-actions text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($tbl_missing){
  echo "<tr><td colspan='9' class='text-center small-muted py-4'>No service requests found</td></tr>";
} elseif($res && mysqli_num_rows($res)>0){
  $i=1;
  // status -> badge class map
  $badge_map = [
    'pending' => 'bg-warning text-dark',
    'in_progress' => 'bg-info text-dark',
    'completed' => 'bg-success',
    'cancelled' => 'bg-danger'
  ];
  while($r = mysqli_fetch_assoc($res)){
    $id = (int)$r['id'];
    $user = htmlspecialchars($r['user']);
    $item = htmlspecialchars($r['item']);
    $type = htmlspecialchars($r['service_type']);
    $details = htmlspecialchars(mb_strimwidth($r['details'],0,90,'...'));
    $status = strtolower($r['status'] ?? 'pending');
    $date = !empty($r['created_at']) ? date('Y-m-d H:i', strtotime($r['created_at'])) : '';
    $status_cls = $badge_map[$status] ?? 'bg-secondary';
    $assigned_agent = htmlspecialchars($r['assigned_agent'] ?? '');

    echo "<tr>";
    echo "<td class='col-num'>{$i}</td>";
    echo "<td class='col-user'>{$user}</td>";
    echo "<td class='col-item fw-semibold'>{$item}</td>";
    echo "<td class='col-type'>{$type}</td>";
    echo "<td class='col-details'>{$details}</td>";
    echo "<td class='col-status'><span class='badge {$status_cls}'>".htmlspecialchars(ucfirst(str_replace('_',' ',$status)))."</span></td>";
    echo "<td class='col-agent'>".($assigned_agent !== '' ? $assigned_agent : "-")."</td>";
    echo "<td class='col-date'>{$date}</td>";
    echo "<td class='col-actions text-center'>";
    echo "<div class='d-flex flex-column gap-2 justify-content-center align-items-center actions-stack'>";
    echo "<a href='view_service.php?id={$id}' class='btn btn-sm btn-primary' title='View'><i class='bi bi-eye'></i> View</a>";
    echo "<form method='post' action='assign_service.php' class='d-inline-flex align-items-center gap-2'>";
    echo "<input type='hidden' name='id' value='{$id}'>";
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
    echo "</div>";
    echo "</td>";
    echo "</tr>";
    $i++;
  }
} else {
  echo "<tr><td colspan='9' class='text-center small-muted py-4'>No service requests found</td></tr>";
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php include('footer.php'); ?>
