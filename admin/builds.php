<?php
include('header.php');
include('conn.php');

$q = "SELECT * FROM builds ORDER BY created_at DESC";
$res = mysqli_query($con, $q);

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-hammer text-primary me-2"></i>Saved Builds</h4>
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
    $total = number_format((float)$r['total'],2);
    $created = $r['created_at'];
    echo "<tr>";
    echo "<td>{$i}</td>";
    echo "<td class='fw-semibold'>{$bname}</td>";
    echo "<td>{$uname}</td>";
    echo "<td>\${$total}</td>";
    echo "<td>{$created}</td>";
    echo "<td class='text-center'>
            <a class='btn btn-sm btn-outline-primary' href='view_build.php?id={$id}'>View</a>
          </td>";
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
