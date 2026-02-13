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

// format registration time
$reg_time = '';
if(isset($user['created_at']) && strlen(trim($user['created_at']))>0){
  $reg_time = date('d M Y', strtotime($user['created_at']));
}

// Prefill logic
$prefill_from_get = false;
$map = [ 'address' => 'c_address', 'city' => 'c_city', 'state' => 'c_state', 'pincode' => 'c_pincode', 'name' => 'c_name', 'contact' => 'c_contact' ];
foreach($map as $gk => $uk){
  if(isset($_GET[$gk]) && strlen(trim($_GET[$gk]))>0){
    if(!is_array($user)) $user = [];
    $user[$uk] = trim($_GET[$gk]);
    $prefill_from_get = true;
  }
}

// Update Logic
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])){
  $contact = mysqli_real_escape_string($con, $_POST['contact'] ?? '');
  $password = $_POST['password'] ?? '';
  $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
  $city = mysqli_real_escape_string($con, $_POST['city'] ?? '');
  $state = mysqli_real_escape_string($con, $_POST['state'] ?? '');
  $pincode = mysqli_real_escape_string($con, $_POST['pincode'] ?? '');
  if($email){
    // DB Checks (kept as is)
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_address TEXT NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_city VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_state VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_pincode VARCHAR(32) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_photo VARCHAR(255) NULL");
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

    if(isset($_FILES['profile_photo']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])){
      $allowed = ['jpg','jpeg','png','webp'];
      $maxSize = 2 * 1024 * 1024;
      $fileName = $_FILES['profile_photo']['name'] ?? '';
      $fileSize = (int)($_FILES['profile_photo']['size'] ?? 0);
      $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      if(in_array($ext, $allowed, true) && $fileSize > 0 && $fileSize <= $maxSize){
        $uploadDir = __DIR__ . '/profile_photos';
        if(!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $safeName = 'user_' . ($user['cid'] ?? $_SESSION['user_id'] ?? time()) . '_' . time() . '.' . $ext;
        $dest = $uploadDir . '/' . $safeName;
        if(@move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)){
          $photo_path = 'profile_photos/' . $safeName;
          $parts[] = "c_photo='".mysqli_real_escape_string($con, $photo_path)."'";
        }
      }
    }
    if(strlen($password) > 0){
      $parts[] = "c_password='".mysqli_real_escape_string($con,$password)."'";
    }
    $parts[] = "updated_at=NOW()";
    $upd = "UPDATE cust_reg SET " . implode(', ', $parts) . " WHERE c_email='".mysqli_real_escape_string($con,$email)."' LIMIT 1";
    if(mysqli_query($con, $upd)){
      echo "<script>alert('Your profile has been updated successfully.'); window.location='profile.php';</script>";
      exit;
    } else {
      $err = mysqli_error($con);
    }
  }
}
?>
<?php include('header.php'); ?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
  :root {
    --primary-color: #4f46e5; /* Indigo */
    --primary-hover: #4338ca;
    --bg-color: #f3f4f6;
    --card-bg: #ffffff;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --border-color: #e5e7eb;
  }

  body {
    background-color: var(--bg-color);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--text-main);
  }

  .main-wrapper {
    padding: 40px 0;
    min-height: 85vh;
  }

  /* Main Card Container */
  .settings-card {
    background: var(--card-bg);
    border-radius: 24px;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.8);
  }

  /* Left Sidebar (Identity) */
  .identity-section {
    background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
    border-right: 1px solid var(--border-color);
    padding: 40px 30px;
    text-align: center;
    height: 100%;
  }

  .avatar-wrapper {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 20px;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  }

  /* Camera Icon Overlay */
  .upload-icon-overlay {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: var(--primary-color);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    transition: transform 0.2s;
  }

  .upload-icon-overlay:hover {
    transform: scale(1.1);
  }

  .user-name {
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 5px;
    color: #111827;
  }

  .user-email {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 20px;
    background: #f3f4f6;
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .stats-grid {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid var(--border-color);
  }

  .stat-item h6 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 4px;
  }
  
  .stat-item span {
    font-weight: 600;
    color: var(--primary-color);
  }

  /* Right Side (Form) */
  .form-section {
    padding: 40px;
  }

  .section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .section-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin-bottom: 30px;
  }

  /* Modern Input Styling */
  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
  }

  .form-control {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.2s;
  }

  .form-control:focus {
    background-color: #fff;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
  }
  
  textarea.form-control {
    resize: none;
    height: 100px;
  }

  .btn-save {
    background: var(--primary-color);
    color: white;
    padding: 12px 32px;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    transition: all 0.3s;
    width: 100%;
  }

  .btn-save:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  }

  /* Mobile Adjustments */
  @media (max-width: 991px) {
    .identity-section {
      border-right: none;
      border-bottom: 1px solid var(--border-color);
      padding: 30px 20px;
    }
    .form-section {
      padding: 30px 20px;
    }
  }
</style>

<div class="main-wrapper">
  <div class="container">
    
    <?php if(!empty($err)): ?>
       <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4"><i class="bi bi-exclamation-circle me-2"></i> <?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    
    <?php if($prefill_from_get): ?>
       <div class="alert alert-info shadow-sm border-0 rounded-3 mb-4"><i class="bi bi-info-circle me-2"></i> Profile data imported from registration. Please review and update as needed.</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="settings-card">
        <div class="row g-0">
          
          <div class="col-lg-4">
            <div class="identity-section">
              <div class="avatar-wrapper">
                <?php 
                  $display_photo = !empty($user['c_photo']) ? htmlspecialchars($user['c_photo']) : 'https://ui-avatars.com/api/?name='.urlencode($user['c_name'] ?? 'User').'&background=random&size=200';
                ?>
                <img src="<?php echo $display_photo; ?>" alt="Profile" class="avatar-img" id="photoPreview">
                
                <label for="profile_photo" class="upload-icon-overlay" title="Change Photo">
                  <i class="bi bi-camera"></i>
                </label>
                <input type="file" id="profile_photo" name="profile_photo" style="display:none;" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this)">
              </div>
              
              <div class="user-name"><?php echo htmlspecialchars($user['c_name'] ?? 'My Profile'); ?></div>
              <div class="user-email"><?php echo htmlspecialchars($user['c_email'] ?? $email); ?></div>
              
              <div class="stats-grid">
                <div class="stat-item">
                  <h6>Joined</h6>
                  <span><?php echo $reg_time ?: 'Recently'; ?></span>
                </div>
                <div class="stat-item">
                  <h6>Status</h6>
                  <span class="text-success">Active</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="form-section">
              <div class="d-flex justify-content-between align-items-center mb-4">
                 <div>
                    <h2 class="section-title">Account Settings</h2>
                    <p class="section-subtitle">Manage your personal details and delivery address.</p>
                 </div>
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['c_name'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 form-group">
                  <label class="form-label">Phone Number</label>
                  <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['c_contact'] ?? ''); ?>">
                </div>

                <div class="col-12 form-group">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control" placeholder="Street address, Apartment, Suite, Unit, etc."><?php echo htmlspecialchars($user['c_address'] ?? ''); ?></textarea>
                </div>

                <div class="col-md-4 form-group">
                  <label class="form-label">City</label>
                  <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['c_city'] ?? ''); ?>">
                </div>

                <div class="col-md-4 form-group">
                  <label class="form-label">State</label>
                  <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['c_state'] ?? ''); ?>">
                </div>

                <div class="col-md-4 form-group">
                  <label class="form-label">Pincode</label>
                  <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['c_pincode'] ?? ''); ?>">
                </div>

                <div class="col-12 mt-2">
                   <hr class="text-muted opacity-25 mb-4">
                   <h5 class="mb-3" style="font-size:1rem; font-weight:700;">Security</h5>
                </div>

                <div class="col-md-12 form-group">
                  <label class="form-label">New Password <span class="text-muted fw-normal">(Leave blank to keep current)</span></label>
                  <input type="password" name="password" class="form-control" autocomplete="new-password">
                </div>

                <div class="col-12 mt-4">
                  <button type="submit" name="update_profile" class="btn-save">
                    Save Changes
                  </button>
                </div>

              </div>
            </div>
          </div>
          
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Simple JS to preview image before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include('footer.php'); ?>