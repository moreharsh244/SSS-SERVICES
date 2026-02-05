<?php
include('conn.php');

$sql = "SELECT username, password FROM user_login ORDER BY username DESC";
$res = mysqli_query($con, $sql);
$total = ($res) ? mysqli_num_rows($res) : 0;
?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">
          <i class="bi bi-people-fill text-primary me-2"></i>
          User Management
        </h4>
        <div class="small-muted">Registered user accounts</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm search-input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="userSearch" type="search" class="form-control" placeholder="Search by username">
        </div>
        <span class="badge badge-total">
          <i class="bi bi-person-badge me-1"></i> <?php echo $total; ?>
        </span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="usersTable" class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Avatar</th>
            <th>Username</th>
            <th>Password</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>

<?php
if ($res && $total > 0) {
  $i = 1;
  while ($row = mysqli_fetch_assoc($res)) {

    $username = htmlspecialchars($row['username']);
    $encoded  = rawurlencode($row['username']);

    // Create initials
    $parts = preg_split('/\s+/', trim($row['username']));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
      $initials .= strtoupper(substr($parts[1], 0, 1));
    }

    echo "
      <tr>
        <td>{$i}</td>

        <td>
          <div class='user-avatar' title='{$username}'>
            {$initials}
          </div>
        </td>

        <td class='fw-semibold'>
          <i class='bi bi-person-circle me-1 text-secondary'></i>
          {$username}
        </td>

        <td>
          <span class='pw-mask small-muted'>••••••••</span>
          <button
            type='button'
            class='btn btn-sm btn-outline-secondary ms-2 toggle-pw'
            data-pw='{$row['password']}'>
            <i class='bi bi-eye'></i> Show
          </button>
        </td>

        <td class='text-center action-btns'>
          <a href='edit_user.php?username={$encoded}'
             class='btn btn-sm btn-outline-primary me-1'>
             <i class='bi bi-pencil-square'></i>
          </a>

          <a href='delete_user.php?username={$encoded}'
             class='btn btn-sm btn-outline-danger'
             onclick='return confirm(\"Are you sure you want to delete this user?\")'>
             <i class='bi bi-trash'></i>
          </a>
        </td>
      </tr>
    ";

    $i++;
  }
} else {
  echo "
    <tr>
      <td colspan='5' class='text-center small-muted py-4'>
        <i class='bi bi-exclamation-circle me-1'></i>
        No users found
      </td>
    </tr>
  ";
}
?>

        </tbody>
      </table>
    </div>

  </div>
</div>
