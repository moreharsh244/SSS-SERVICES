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
  $reg_time = date('F Y', strtotime($user['created_at'])); // Changed to "Month Year" format
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
    // DB Checks
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_address TEXT NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_city VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_state VARCHAR(128) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_pincode VARCHAR(32) NULL");
    @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS c_photo VARCHAR(255) NULL");
    
    // Check for updated_at column
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
      header('Location: profile.php?toast=' . rawurlencode('Your profile has been updated successfully.') . '&toast_type=success');
      exit;
    } else {
      $err = mysqli_error($con);
    }
  }
}
?>
<?php include('header.php'); ?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
  :root {
    --primary-grad: linear-gradient(135deg, #8b5cf6 0%, #0ea5e9 100%);
    --bg-surface: #eef4ff;
    --card-shadow: 0 20px 40px -10px rgba(30,64,175,0.12);
    --input-bg: #eef6ff;
    --text-dark: #1f2a44;
  }

  body {
    background:
      radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
      radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
      linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    color: var(--text-dark);
  }

  .main-wrapper {
    padding: 60px 0;
    min-height: 90vh;
    display: flex;
    align-items: center;
  }

  /* Main Card Layout */
  .profile-card {
    background: #f8fbff;
    border-radius: 30px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    border: 1px solid #dbeafe;
  }

  /* --- Left Sidebar (Colorful) --- */
  .identity-sidebar {
    background: var(--primary-grad);
    color: white;
    padding: 60px 30px;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }

  /* Decorative Circles in BG */
  .identity-sidebar::before {
    content: '';
    position: absolute;
    top: -50px; left: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
  }
  .identity-sidebar::after {
    content: '';
    position: absolute;
    bottom: -50px; right: -50px;
    width: 150px; height: 150px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
  }

  .avatar-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 25px;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 5px solid rgba(255,255,255,0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
  }

  .avatar-wrapper:hover .avatar-img {
    transform: scale(1.05);
    border-color: rgba(255,255,255,0.8);
  }

  .camera-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #fff;
    color: #6366f1;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.2s;
  }
  .camera-btn:hover {
    background: #f0f0f0;
    transform: rotate(15deg);
  }

  .user-name-display {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
  }

  .user-email-display {
    font-size: 0.9rem;
    opacity: 0.8;
    background: rgba(255,255,255,0.2);
    padding: 5px 15px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
  }

  .sidebar-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    border-top: 1px solid rgba(255,255,255,0.2);
    padding-top: 25px;
    position: relative;
    z-index: 1;
  }

  .stat-box h6 {
    font-size: 0.75rem;
    text-transform: uppercase;
    opacity: 0.7;
    margin-bottom: 5px;
    letter-spacing: 1px;
  }
  .stat-box span {
    font-size: 1rem;
    font-weight: 600;
  }

  /* --- Right Form Section --- */
  .form-container {
    padding: 50px 40px;
  }

  .section-header {
    margin-bottom: 30px;
  }
  .section-header h3 {
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .section-header p {
    color: #64748b;
    font-size: 0.95rem;
  }

  /* Form Elements */
  .form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
  }

  .input-group-text {
    background: var(--input-bg);
    border: 1px solid transparent;
    color: #64748b;
    border-radius: 12px 0 0 12px;
  }

  .form-control {
    background: var(--input-bg);
    border: 1px solid transparent;
    border-radius: 0 12px 12px 0; /* Rounded right only */
    padding: 12px 15px;
    font-size: 0.95rem;
    color: #334155;
    transition: all 0.3s;
  }
  
  /* Fix rounding for single inputs (textarea) */
  textarea.form-control {
    border-radius: 12px !important;
  }

  .form-control:focus {
    background: #fff;
    border-color: #93c5fd;
    box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
    color: #1e293b;
  }
  .form-control:focus + .input-group-text, 
  .input-group:focus-within .input-group-text {
    background: #fff;
    border-color: #6366f1;
    color: #6366f1;
  }

  .btn-gradient {
    background: var(--primary-grad);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s;
    width: 100%;
    box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
  }

  .btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.5);
    color: white;
  }

  /* Responsive */
  @media (max-width: 991px) {
    .identity-sidebar {
      padding: 40px 20px;
      border-radius: 30px 30px 0 0;
    }
    .form-container {
      padding: 30px 20px;
    }
  }
</style>

<div class="main-wrapper">
  <div class="container">
    
    <?php if(!empty($err)): ?>
       <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4 d-flex align-items-center">
         <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
         <div><?php echo htmlspecialchars($err); ?></div>
       </div>
    <?php endif; ?>
    
    <?php if($prefill_from_get): ?>
       <div class="alert alert-primary shadow-sm border-0 rounded-4 mb-4 d-flex align-items-center" style="background: #e0e7ff; color: #3730a3;">
         <i class="bi bi-info-circle-fill fs-4 me-3"></i>
         <div>Profile data imported. Please review and save.</div>
       </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="profile-card">
        <div class="row g-0">
          
          <div class="col-lg-4">
            <div class="identity-sidebar">
              <div class="avatar-wrapper">
                <?php 
                  $display_photo = !empty($user['c_photo']) ? htmlspecialchars($user['c_photo']) : 'https://ui-avatars.com/api/?name='.urlencode($user['c_name'] ?? 'User').'&background=random&size=256&bold=true';
                ?>
                <img src="<?php echo $display_photo; ?>" alt="Profile" class="avatar-img" id="photoPreview">
                
                <label for="profile_photo" class="camera-btn" title="Upload New Photo">
                  <i class="bi bi-camera-fill"></i>
                </label>
                <input type="file" id="profile_photo" name="profile_photo" style="display:none;" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this)">
              </div>
              
              <div class="user-name-display"><?php echo htmlspecialchars($user['c_name'] ?? 'My Profile'); ?></div>
              <div class="user-email-display"><?php echo htmlspecialchars($user['c_email'] ?? $email); ?></div>
              
              <div class="sidebar-stats">
                <div class="stat-box">
                  <h6>Joined</h6>
                  <span><?php echo $reg_time ?: 'Recently'; ?></span>
                </div>
                <div style="width: 1px; background: rgba(255,255,255,0.3);"></div>
                <div class="stat-box">
                  <h6>Status</h6>
                  <span class="badge bg-white text-primary rounded-pill px-3">Active</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="form-container">
              
              <div class="section-header">
                 <h3><i class="bi bi-person-lines-fill text-primary"></i> Edit Profile</h3>
                 <p>Update your personal information and shipping address.</p>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control" placeholder="Your Name" value="<?php echo htmlspecialchars($user['c_name'] ?? ''); ?>" required>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Phone Number</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="contact" class="form-control" placeholder="10-digit Mobile" value="<?php echo htmlspecialchars($user['c_contact'] ?? ''); ?>" required>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Shipping Address</label>
                  <div class="input-group">
                     <span class="input-group-text border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-geo-alt"></i></span>
                     <textarea name="address" class="form-control border-start-0" rows="2" placeholder="Street, Sector, Apartment..." style="border-radius: 0 12px 12px 0 !important;" required><?php echo htmlspecialchars($user['c_address'] ?? ''); ?></textarea>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">City</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                    <input type="text" name="city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($user['c_city'] ?? ''); ?>" required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">State</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-map"></i></span>
                    <input type="text" name="state" class="form-control" placeholder="State" value="<?php echo htmlspecialchars($user['c_state'] ?? ''); ?>" required>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Pincode</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                    <input type="text" name="pincode" class="form-control" placeholder="ZIP Code" value="<?php echo htmlspecialchars($user['c_pincode'] ?? ''); ?>" required>
                  </div>
                </div>

                <div class="col-12 my-2">
                   <hr class="text-muted opacity-25">
                </div>

                <div class="col-md-12">
                  <label class="form-label text-danger">Change Password <small class="text-muted fw-normal">(Optional)</small></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password only if changing" autocomplete="new-password">
                  </div>
                </div>

                <div class="col-12 mt-4">
                  <button type="submit" name="update_profile" class="btn-gradient">
                    <i class="bi bi-check-lg me-2"></i> Save Changes
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

<?php include(__DIR__ . '/../footer.php'); ?>