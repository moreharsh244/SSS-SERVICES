<?php
include('conn.php');
// fetch registered users
$sql = "SELECT username, password FROM user_login ORDER BY username DESC";
$res = mysqli_query($con, $sql);
?>

<div class="col-12">
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Registered Users</h4>
        <div class="small text-muted">Manage accounts and access</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm search-input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="userSearch" type="search" class="form-control form-control-sm" placeholder="Search username">
        </div>
        <span class="badge bg-secondary">Total: <?php echo mysqli_num_rows($res); ?></span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="usersTable" class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:48px">#</th>
            <th style="width:64px"></th>
            <th>Username</th>
            <th style="width:180px">Password</th>
            <th style="width:170px">Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
if($res && mysqli_num_rows($res) > 0){
  $i = 1;
  while($row = mysqli_fetch_assoc($res)){
    $uname = $row['username'];
    $enc = rawurlencode($uname);
    $masked = '••••••••';
    // initials for avatar
    $parts = preg_split('/\s+/', trim($uname));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if(count($parts) > 1) $initials .= strtoupper(substr($parts[1], 0, 1));

    echo '<tr>';
    echo '<td>'.($i++).'</td>';
    echo '<td><div class="user-avatar" title="'.htmlspecialchars($uname).'">'.htmlspecialchars($initials).'</div></td>';
    echo '<td class="align-middle fw-semibold">'.htmlspecialchars($uname).'</td>';
    echo '<td><span class="small-muted pw-mask">'. $masked .'</span> <button type="button" class="btn btn-sm btn-outline-secondary ms-2 toggle-pw" data-pw="'.htmlspecialchars($row['password']).'">Show</button></td>';
    echo '<td class="action-btns">';
    echo '<a href="edit_user.php?username='. $enc .'" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>';
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
// client-side search and password toggle
document.addEventListener('DOMContentLoaded', function(){
  var search = document.getElementById('userSearch');
  if(search){
    search.addEventListener('input', function(){
      var q = this.value.toLowerCase();
      var rows = document.querySelectorAll('#usersTable tbody tr');
      rows.forEach(function(r){
        var nameCell = r.querySelector('td:nth-child(3)');
        if(!nameCell) return;
        var name = nameCell.textContent.toLowerCase();
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
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-secondary');
      } else {
        mask.textContent = '••••••••';
        btn.textContent = 'Show';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-outline-secondary');
      }
    });
  });
});
</script>
