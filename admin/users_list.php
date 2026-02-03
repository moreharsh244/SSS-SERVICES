<?php
include('conn.php');
// fetch registered users
$sql = "SELECT username, password FROM user_login ORDER BY username DESC";
$res = mysqli_query($con, $sql);
?>

<div class="col-12">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Registered Users</h4>
      <small class="small-muted">Total: <?php echo mysqli_num_rows($res); ?></small>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Password (hashed/plain)</th>
            <th>Registered At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($res && mysqli_num_rows($res) > 0){
  $i = 1;
  while($row = mysqli_fetch_assoc($res)){
    echo '<tr>';
    echo '<td>'.($i++).'</td>';
    echo '<td>'.htmlspecialchars($row['username']).'</td>';
    echo '<td><span class="small-muted">'.htmlspecialchars($row['password']).'</span></td>';
    echo '<td class="small-muted">-</td>';
    echo '<td>';
    $uname = $row['username'];
    $enc = rawurlencode($uname);
    echo '<a href="edit_user.php?username='. $enc .'" class="btn btn-sm btn-outline-primary me-2">Edit</a>';
    echo '<a href="delete_user.php?username='. $enc .'" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this user?\')">Delete</a>';
    echo '</td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="5" class="text-center small-muted">No registered users found.</td></tr>';
}
?>
        </tbody>
      </table>
    </div>
  </div>
</div>
