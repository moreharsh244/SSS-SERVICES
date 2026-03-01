<?php
include('header.php');
include '../admin/conn.php';
include 'helpers.php';
ensure_delivery_tables($con);

$username = $_SESSION['username'] ?? '';

// ... [Existing PHP Logic remains the same] ...
if(isset($_POST['update_profile'])){
    $full_name = mysqli_real_escape_string($con, trim($_POST['full_name'] ?? ''));
    $email = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone'] ?? ''));
    mysqli_query($con, "UPDATE del_login SET full_name='$full_name', email='$email', phone='$phone' WHERE username='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
    log_delivery_action($con, $username, 'profile_update', 'Updated profile details');
}

if(isset($_POST['add_address'])){
    $line1 = mysqli_real_escape_string($con, trim($_POST['address_line1'] ?? ''));
    $line2 = mysqli_real_escape_string($con, trim($_POST['address_line2'] ?? ''));
    $city = mysqli_real_escape_string($con, trim($_POST['city'] ?? ''));
    $state = mysqli_real_escape_string($con, trim($_POST['state'] ?? ''));
    $postal = mysqli_real_escape_string($con, trim($_POST['postal_code'] ?? ''));
    $country = mysqli_real_escape_string($con, trim($_POST['country'] ?? ''));
    if($line1 !== '' && $city !== '' && $state !== '' && $postal !== '' && $country !== ''){
        $ins = "INSERT INTO delivery_addresses (agent_username,address_line1,address_line2,city,state,postal_code,country) VALUES ('".mysqli_real_escape_string($con,$username)."','$line1','$line2','$city','$state','$postal','$country')";
        mysqli_query($con, $ins);
        log_delivery_action($con, $username, 'address_add', 'Added address');
    }
}

if(isset($_POST['set_default'])){
    $addr_id = intval($_POST['address_id'] ?? 0);
    if($addr_id > 0){
        mysqli_query($con, "UPDATE delivery_addresses SET is_default=0 WHERE agent_username='".mysqli_real_escape_string($con,$username)."'");
        mysqli_query($con, "UPDATE delivery_addresses SET is_default=1 WHERE id=$addr_id AND agent_username='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
        log_delivery_action($con, $username, 'address_default', 'Set default address');
    }
}

if(isset($_POST['delete_address'])){
    $addr_id = intval($_POST['address_id'] ?? 0);
    if($addr_id > 0){
        mysqli_query($con, "DELETE FROM delivery_addresses WHERE id=$addr_id AND agent_username='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
        log_delivery_action($con, $username, 'address_delete', 'Deleted address');
    }
}

$agent = null;
$agentRes = mysqli_query($con, "SELECT username, full_name, email, phone, role, created_at FROM del_login WHERE username='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
if($agentRes && mysqli_num_rows($agentRes)>0){
    $agent = mysqli_fetch_assoc($agentRes);
}

$addrRes = mysqli_query($con, "SELECT * FROM delivery_addresses WHERE agent_username='".mysqli_real_escape_string($con,$username)."' ORDER BY is_default DESC, created_at DESC");
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    :root {
        /* Purple Gradient to match Admin/User portals */
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
        --primary-solid: #6366f1;
        --card-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.15);
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* Hero Section */
    .profile-hero {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.8);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0; width: 6px;
        background: var(--primary-grad);
    }

    .hero-title {
        font-weight: 800;
        font-size: 1.8rem;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .stat-pill {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        color: #7c3aed;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #e9d5ff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Content Panels */
    .content-panel {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        height: 100%;
        transition: transform 0.3s ease;
    }

    .panel-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .panel-icon {
        width: 40px; height: 40px;
        background: #f3e8ff;
        color: #7c3aed;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .panel-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #0f172a;
        margin: 0;
    }

    /* Form Styles */
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-control {
        border-radius: 10px;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .form-control:focus {
        background: white;
        border-color: #7c3aed;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
    }

    .form-control[readonly] {
        background: #f1f5f9;
        color: #64748b;
    }

    /* Buttons */
    .btn-primary-custom {
        background: var(--primary-grad);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
    }
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(124, 58, 237, 0.3);
    }

    .btn-secondary-custom {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        transition: all 0.2s;
    }
    .btn-secondary-custom:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Address Table */
    .table-custom {
        margin-bottom: 0;
    }
    .table-custom th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 15px;
    }
    .table-custom td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .table-custom tr:last-child td { border-bottom: none; }

    .badge-default {
        background: #f0fdf4;
        color: #16a34a;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #dcfce7;
    }

    .btn-action-sm {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-set-default {
        background: white;
        border: 1px solid #cbd5e1;
        color: #475569;
    }
    .btn-set-default:hover { background: #f1f5f9; color: #0f172a; }
    
    .btn-delete {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #ef4444;
    }
    .btn-delete:hover { background: #fee2e2; color: #b91c1c; }

</style>

<div class="container">
    <div class="col-12 col-lg-10 mx-auto">
        
        <div class="profile-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="hero-title mb-1">Agent Profile</h2>
                <div class="text-muted">Manage personal details and saved addresses</div>
            </div>
            <div class="stat-pill">
                <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($agent['username'] ?? $username); ?>
            </div>
        </div>

        <div class="row g-3">
            
            <div class="col-md-5">
                <div class="content-panel">
                    <div class="panel-header">
                        <div class="panel-icon"><i class="bi bi-person-circle"></i></div>
                        <h5 class="panel-title">Agent Profile</h5>
                    </div>
                    
                    <form action="profile.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($agent['username'] ?? $username); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($agent['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn-primary-custom">
                            <i class="bi bi-check-circle"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                
                <div class="content-panel">
                    <div class="panel-header">
                        <div class="panel-icon"><i class="bi bi-plus-circle"></i></div>
                        <h5 class="panel-title">Add New Address</h5>
                    </div>
                    
                    <form action="profile.php" method="post" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="address_line1" class="form-control" placeholder="House/Flat No, Street Name" required>
                        </div>
                        <div class="col-12">
                            <input type="text" name="address_line2" class="form-control" placeholder="Apartment, Suite, Unit, etc. (Optional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="add_address" class="btn-secondary-custom">
                                <i class="bi bi-plus-lg me-2"></i> Add Address
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-panel">
                    <div class="panel-header">
                        <div class="panel-icon"><i class="bi bi-list-check"></i></div>
                        <h5 class="panel-title">Saved Addresses</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Location Details</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($addrRes && mysqli_num_rows($addrRes)>0): ?>
                                    <?php while($a = mysqli_fetch_assoc($addrRes)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($a['address_line1']); ?></div>
                                                <?php if($a['address_line2']): ?>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($a['address_line2']); ?></div>
                                                <?php endif; ?>
                                                <div class="text-muted small mt-1">
                                                    <?php echo htmlspecialchars($a['city'].', '.$a['state'].' - '.$a['postal_code']); ?>
                                                </div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($a['country']); ?></div>
                                            </td>
                                            <td>
                                                <?php if((int)$a['is_default'] === 1): ?>
                                                    <span class="badge badge-delivered"><i class="bi bi-check-circle me-1"></i> Default</span>
                                                <?php else: ?>
                                                    <form method="post" action="profile.php">
                                                        <input type="hidden" name="address_id" value="<?php echo (int)$a['id']; ?>">
                                                        <button type="submit" name="set_default" class="btn btn-sm btn-outline-primary">Set Default</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <form method="post" action="profile.php">
                                                    <input type="hidden" name="address_id" value="<?php echo (int)$a['id']; ?>">
                                                    <button type="submit" name="delete_address" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No saved addresses found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include(__DIR__ . '/../footer.php');
?>