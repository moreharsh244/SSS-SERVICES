<?php
include('header.php');
include('conn.php');

// handle POST update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['original_username'])){
  $original = mysqli_real_escape_string($con, $_POST['original_username']);
  $username = mysqli_real_escape_string($con, $_POST['username']);
  $password = mysqli_real_escape_string($con, $_POST['password']);
  $sql = "UPDATE user_login SET username='$username', password='$password' WHERE username='$original' LIMIT 1";
  mysqli_query($con, $sql);
  header('location:index.php');
  exit;
}

// show form
if(!isset($_GET['username'])){
  header('location:index.php');
  exit;
}
$orig = mysqli_real_escape_string($con, rawurldecode($_GET['username']));
$sql = "SELECT username, password FROM user_login WHERE username='$orig' LIMIT 1";
$res = mysqli_query($con, $sql);
if(!$res || mysqli_num_rows($res) === 0){
  echo '<div class="col-12"><div class="admin-card"><p class="small-muted">User not found.</p></div></div>';
  include(__DIR__ . '/footer.php');
  exit;
}
$row = mysqli_fetch_assoc($res);
?>

<div class="col-6">
  <div class="admin-card">
    <h4>Edit User</h4>
    <form method="post" action="edit_user.php">
      <input type="hidden" name="original_username" value="<?php echo htmlspecialchars($row['username']); ?>">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($row['password']); ?>" required>
      </div>
      <button class="btn btn-primary">Save</button>
      <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</div>

<?php include(__DIR__ . '/footer.php'); ?>
