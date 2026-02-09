<?php
include('header.php');
include '../admin/conn.php';
include 'helpers.php';
ensure_delivery_tables($con);

$username = $_SESSION['username'] ?? '';

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

<div class="col-12 col-lg-10 mx-auto">
    <div class="delivery-hero mb-4 fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Agent Profile</h2>
                <div class="text-muted">Manage personal details and saved addresses.</div>
            </div>
            <div class="stat-pill"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($agent['username'] ?? $username); ?></div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="delivery-card p-0">
                <div class="card-header bg-danger text-white">Agent Profile</div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($agent['username'] ?? $username); ?>" readonly>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($agent['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-delivery btn-sm">Save Profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="delivery-card p-0 mb-3">
                <div class="card-header bg-danger text-white">Addresses</div>
                <div class="card-body">
                    <form action="profile.php" method="post" class="row g-2">
                        <div class="col-12">
                            <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" required>
                        </div>
                        <div class="col-12">
                            <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2 (optional)">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="city" class="form-control" placeholder="City" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="state" class="form-control" placeholder="State" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="postal_code" class="form-control" placeholder="Postal Code" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="country" class="form-control" placeholder="Country" required>
                        </div>
                        <div class="col-md-6 d-grid">
                            <button type="submit" name="add_address" class="btn btn-delivery">Add Address</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="delivery-card p-0">
                <div class="card-header bg-light">Saved Addresses</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Address</th>
                                    <th>Default</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($addrRes && mysqli_num_rows($addrRes)>0): ?>
                                    <?php while($a = mysqli_fetch_assoc($addrRes)): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($a['address_line1']); ?><br>
                                                <?php echo htmlspecialchars($a['address_line2']); ?>
                                                <?php echo htmlspecialchars($a['city'].', '.$a['state'].' '.$a['postal_code']); ?><br>
                                                <?php echo htmlspecialchars($a['country']); ?>
                                            </td>
                                            <td>
                                                <?php if((int)$a['is_default'] === 1): ?>
                                                    <span class="badge bg-success">Default</span>
                                                <?php else: ?>
                                                    <form method="post" action="profile.php">
                                                        <input type="hidden" name="address_id" value="<?php echo (int)$a['id']; ?>">
                                                        <button type="submit" name="set_default" class="btn btn-outline-secondary btn-sm">Set Default</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="profile.php" onsubmit="return confirm('Delete this address?');">
                                                    <input type="hidden" name="address_id" value="<?php echo (int)$a['id']; ?>">
                                                    <button type="submit" name="delete_address" class="btn btn-outline-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">No addresses saved</td></tr>
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
include('footer.php');
?>
