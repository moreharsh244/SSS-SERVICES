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
      <div>
        <input id="userSearch" type="search" class="form-control form-control-sm d-inline-block me-2" placeholder="Search username" style="width:220px;">
        <span class="badge bg-secondary">Total: <?php echo mysqli_num_rows($res); ?></span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="usersTable" class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:48px">#</th>
            <th>Username</th>
            <th style="width:180px">Password</th>
            <th style="width:140px">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($res && mysqli_num_rows($res) > 0){
  $i = 1;
  while($row = mysqli_fetch_assoc($res)){
    echo '<tr>';
    echo '<td>'.($i++).'</td>';
    echo '<td class="align-middle">'.htmlspecialchars($row['username']).'</td>';
    // mask password for safety
    $masked = '••••••••';
    echo '<td><span class="small-muted pw-mask">'. $masked .'</span> <button type="button" class="btn btn-sm btn-outline-secondary ms-2 toggle-pw" data-pw="'.htmlspecialchars($row['password']).'">Show</button></td>';
    echo '<td>';
    $uname = $row['username'];
    $enc = rawurlencode($uname);
    echo '<a href="edit_user.php?username='. $enc .'" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-pencil"></i> Edit</a>';
    echo '<a href="delete_user.php?username='. $enc .'" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this user?\')"><i class="bi bi-trash"></i> Delete</a>';
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

<script>
// client-side search
document.addEventListener('DOMContentLoaded', function(){
  var search = document.getElementById('userSearch');
  if(search){
    search.addEventListener('input', function(){
      var q = this.value.toLowerCase();
      var rows = document.querySelectorAll('#usersTable tbody tr');
      rows.forEach(function(r){
        var name = r.querySelector('td:nth-child(2)').textContent.toLowerCase();
        r.style.display = name.indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }

  // toggle password reveal
  document.querySelectorAll('.toggle-pw').forEach(function(btn){
    btn.addEventListener('click', function(){
      var tr = btn.closest('tr');
      var mask = tr.querySelector('.pw-mask');
      if(btn.textContent.trim() === 'Show'){
        mask.textContent = btn.getAttribute('data-pw');
        btn.textContent = 'Hide';
      } else {
        mask.textContent = '••••••••';
        btn.textContent = 'Show';
      }
    });
  });
});
</script>
