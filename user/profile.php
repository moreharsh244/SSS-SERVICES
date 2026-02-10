<?php
if (session_status() === PHP_SESSION_NONE) {
  session_name('SSS_USER_SESS');
  session_start();
}
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');
$email = $_SESSION['username'] ?? '';
$user = null;
if($email){
    $q = mysqli_query($con, "SELECT * FROM cust_reg WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1");
    if($q && mysqli_num_rows($q)) $user = mysqli_fetch_assoc($q);
}

// format registration time if present
$reg_time = '';
if(isset($user['created_at']) && strlen(trim($user['created_at']))>0){
  $reg_time = date('d M Y, H:i', strtotime($user['created_at']));
}

// If registration redirected here with address data, prefer those values for quick completion
$prefill_from_get = false;
$map = [ 'address' => 'c_address', 'city' => 'c_city', 'state' => 'c_state', 'pincode' => 'c_pincode', 'name' => 'c_name', 'contact' => 'c_contact' ];
foreach($map as $gk => $uk){
  if(isset($_GET[$gk]) && strlen(trim($_GET[$gk]))>0){
    if(!is_array($user)) $user = [];
    $user[$uk] = trim($_GET[$gk]);
    $prefill_from_get = true;
  }
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])){
  $contact = mysqli_real_escape_string($con, $_POST['contact'] ?? '');
  $password = $_POST['password'] ?? '';
  $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
  $city = mysqli_real_escape_string($con, $_POST['city'] ?? '');
  $state = mysqli_real_escape_string($con, $_POST['state'] ?? '');
  $pincode = mysqli_real_escape_string($con, $_POST['pincode'] ?? '');
  if($email){
    // ensure address columns exist (best-effort)
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_address TEXT NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_city VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_state VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_pincode VARCHAR(32) NULL");
    // ensure updated_at column exists to track profile changes
    $updColQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='updated_at' LIMIT 1";
    $updColRes = mysqli_query($con, $updColQ);
    if(!$updColRes || mysqli_num_rows($updColRes)===0){
      @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL");
    }

    $name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $parts = [];
    $parts[] = "c_name='".mysqli_real_escape_string($con,$name)."'";
    $parts[] = "c_contact='$contact'";
    $parts[] = "c_address='".mysqli_real_escape_string($con,$address)."'";
    $parts[] = "c_city='".mysqli_real_escape_string($con,$city)."'";
    $parts[] = "c_state='".mysqli_real_escape_string($con,$state)."'";
    $parts[] = "c_pincode='".mysqli_real_escape_string($con,$pincode)."'";
    if(strlen($password) > 0){
      $parts[] = "c_password='".mysqli_real_escape_string($con,$password)."'";
    }
    // record profile update time
    $parts[] = "updated_at=NOW()";
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

<style>
  /* small, local visual polish for profile page */
  .profile-card { max-width:980px; margin:18px auto; border-radius:16px; overflow:hidden; border:1px solid rgba(15,23,42,0.08); }
  .profile-side { background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(13,110,253,0.02)); }
  .profile-avatar { width:104px; height:104px; border-radius:18px; background:#ffffff; display:flex; align-items:center; justify-content:center; font-size:40px; color:#0d6efd; box-shadow:0 10px 24px rgba(15,23,42,0.08); }
  .profile-meta { font-size:0.9rem; color:#475569; }
  .profile-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; background:#ffffff; border:1px solid rgba(15,23,42,0.08); font-size:0.8rem; }
  .form-section { background:#fff; padding:26px; }
  .section-title { font-weight:600; font-size:0.95rem; color:#0f172a; letter-spacing:0.2px; }
  .form-hint { font-size:0.85rem; color:#64748b; }
</style>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow-sm mt-3 profile-card">
        <div class="row g-0">
          <div class="col-md-4 d-flex align-items-center justify-content-center p-4 profile-side">
            <div class="text-center">
              <div class="profile-avatar mb-3"><i class="bi bi-person-circle"></i></div>
              <h5 class="mb-1">Profile</h5>
              <div class="profile-meta mb-3">Manage your details and keep your info up to date.</div>
              <div class="d-flex flex-column gap-2 align-items-center">
                <span class="profile-pill"><i class="bi bi-envelope"></i><?php echo htmlspecialchars($user['c_email'] ?? $email); ?></span>
                <?php if(!empty($reg_time)): ?>
                  <span class="profile-pill"><i class="bi bi-calendar-event"></i>Member since <?php echo htmlspecialchars($reg_time); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-md-8 form-section">
            <?php if(!empty($err)){ echo '<div class="alert alert-danger">'.htmlspecialchars($err).'</div>'; } ?>
            <?php if(!empty($prefill_from_get)){ echo '<div class="alert alert-info">We imported address details from registration — please confirm and save.</div>'; } ?>
            <form method="post">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="section-title">Edit Profile</div>
                    <div class="form-hint">Only fill in the fields you want to update.</div>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label">Full Name</label>
                  <input name="name" class="form-control" placeholder="Your full name" value="<?php echo htmlspecialchars($user['c_name'] ?? ''); ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label">Contact Number</label>
                  <input name="contact" class="form-control" placeholder="Mobile number" value="<?php echo htmlspecialchars($user['c_contact'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control" placeholder="Street, house no"><?php echo htmlspecialchars($user['c_address'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input name="city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($user['c_city'] ?? ''); ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">State</label>
                    <input name="state" class="form-control" placeholder="State" value="<?php echo htmlspecialchars($user['c_state'] ?? ''); ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Pincode</label>
                  <input name="pincode" class="form-control" placeholder="Postal / ZIP code" value="<?php echo htmlspecialchars($user['c_pincode'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                </div>
                <div class="d-flex justify-content-end gap-2">
                  <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
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
