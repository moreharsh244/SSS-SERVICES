<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');
$email = $_SESSION['username'] ?? '';
$user = null;
if($email){
    $q = mysqli_query($con, "SELECT * FROM cust_reg WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1");
    if($q && mysqli_num_rows($q)) $user = mysqli_fetch_assoc($q);
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])){
  $contact = mysqli_real_escape_string($con, $_POST['contact'] ?? '');
  $password = $_POST['password'] ?? '';
  $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
  $city = mysqli_real_escape_string($con, $_POST['city'] ?? '');
  $state = mysqli_real_escape_string($con, $_POST['state'] ?? '');
  $pincode = mysqli_real_escape_string($con, $_POST['pincode'] ?? '');
  if($email){
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_address TEXT NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_city VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_state VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_pincode VARCHAR(32) NULL");

    $name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $parts = [];
    $parts[] = "c_name='".mysqli_real_escape_string($con,$name)."'";
    $parts[] = "c_contact='$contact'";
    $parts[] = "c_address='".mysqli_real_escape_string($con,$address)."'";
    $parts[] = "c_city='".mysqli_real_escape_string($con,$city)."'";
    $parts[] = "c_state='".mysqli_real_escape_string($con,$state)."'";
    $parts[] = "c_pincode='".mysqli_real_escape_string($con,$pincode)."'";
    if(strlen($password) > 0){
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $parts[] = "c_password='".mysqli_real_escape_string($con,$hash)."'";
    }
    $upd = "UPDATE cust_reg SET " . implode(', ', $parts) . " WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1";
    if(mysqli_query($con, $upd)){
      echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
      exit;
    } else {
      $err = mysqli_error($con);
    }
  }
}
?>
<?php include('header.php'); ?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-9">
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:84px; height:84px; border-radius:12px; background:#eef2ff; display:flex; align-items:center; justify-content:center; font-size:28px; color:var(--primary);">
              <i class="bi bi-person-circle"></i>
            </div>
            <div>
              <h5 class="mb-0">Account</h5>
              <div class="text-muted small">Manage your account details</div>
            </div>
          </div>

          <?php if(!empty($err)){ echo '<div class="alert alert-danger">'.htmlspecialchars($err).'</div>'; } ?>
            <div class="col-md-7">
              <form method="post" class="card p-3">
                <h6 class="mb-3">Edit Profile</h6>
                <div class="mb-2">
                  <label class="form-label">Full Name</label>
                  <input name="name" class="form-control" value="<?php echo htmlspecialchars($user['c_name'] ?? ''); ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label">Contact Number</label>
                  <input name="contact" class="form-control" value="<?php echo htmlspecialchars($user['c_contact'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control" placeholder="Street, house no"><?php echo htmlspecialchars($user['c_address'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input name="city" class="form-control" value="<?php echo htmlspecialchars($user['c_city'] ?? ''); ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">State</label>
                    <input name="state" class="form-control" value="<?php echo htmlspecialchars($user['c_state'] ?? ''); ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Pincode</label>
                  <input name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['c_pincode'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">New Password (leave blank to keep)</label>
                  <input type="password" name="password" class="form-control">
                </div>
                <div class="d-flex justify-content-end">
                  <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<?php
// end of profile.php
?>
