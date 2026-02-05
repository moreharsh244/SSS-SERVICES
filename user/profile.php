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
    $password = mysqli_real_escape_string($con, $_POST['password'] ?? '');
    if($email){
        $upd = "UPDATE cust_reg SET c_contact='$contact'";
        if(strlen($password) > 0) $upd .= ", c_password='$password'";
        $upd .= " WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1";
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

          <div class="row">
            <div class="col-md-5">
              <div class="card p-3 mb-3">
                <h6 class="mb-2">Profile Info</h6>
                <p class="mb-1"><strong>Email:</strong><br><?php echo htmlspecialchars($email); ?></p>
                <p class="mb-1"><strong>Contact:</strong><br><?php echo htmlspecialchars($user['c_contact'] ?? ''); ?></p>
              </div>
            </div>
            <div class="col-md-7">
              <form method="post" class="card p-3">
                <h6 class="mb-3">Edit Profile</h6>
                <div class="mb-2">
                  <label class="form-label">Contact Number</label>
                  <input name="contact" class="form-control" value="<?php echo htmlspecialchars($user['c_contact'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['c_address'] ?? ''); ?></textarea>
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
