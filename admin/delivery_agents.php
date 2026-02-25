<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}
include('header.php');
include('conn.php');
include('../delivery/helpers.php');
ensure_delivery_tables($con);

// --- PHP LOGIC (UNCHANGED) ---
$errors = [];
$success = '';

if(isset($_POST['create_agent'])){
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username === '' || $password === ''){
        $errors[] = 'Username and password are required.';
    } else {
        $u_esc = mysqli_real_escape_string($con, $username);
        $check = mysqli_query($con, "SELECT * FROM del_login WHERE username='$u_esc' LIMIT 1");
        if($check && mysqli_num_rows($check)>0){
            $errors[] = 'Username already exists.';
        } else {
            $fn = mysqli_real_escape_string($con, $full_name);
            $em = mysqli_real_escape_string($con, $email);
            $ph = mysqli_real_escape_string($con, $phone);
            $pass_plain = mysqli_real_escape_string($con, $password);
            $ins = "INSERT INTO del_login (username,password,full_name,email,phone,role,is_active) VALUES ('$u_esc','$pass_plain','$fn','$em','$ph','delivery',1)";
            if(mysqli_query($con, $ins)){
                $success = 'Delivery agent created successfully.';
                log_delivery_action($con, $username, 'admin_create', 'Created by admin '.$_SESSION['username']);
            } else {
                $errors[] = 'Create failed.';
            }
        }
    }
}

if(isset($_POST['toggle_active'])){
    $username = trim($_POST['username'] ?? '');
    $status = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
    $u_esc = mysqli_real_escape_string($con, $username);
    mysqli_query($con, "UPDATE del_login SET is_active=$status WHERE username='$u_esc' LIMIT 1");
    $success = 'Agent status updated.';
    $action = $status === 1 ? 'admin_enable' : 'admin_disable';
    log_delivery_action($con, $username, $action, 'Updated by admin '.$_SESSION['username']);
}

if(isset($_POST['reset_password'])){
    $username = trim($_POST['username'] ?? '');
    $newpass = $_POST['new_password'] ?? '';
    if($username !== '' && $newpass !== ''){
        $u_esc = mysqli_real_escape_string($con, $username);
        $pass_plain = mysqli_real_escape_string($con, $newpass);
        mysqli_query($con, "UPDATE del_login SET password='$pass_plain' WHERE username='$u_esc' LIMIT 1");
        $success = 'Password reset successfully.';
        log_delivery_action($con, $username, 'admin_reset_password', 'Reset by admin '.$_SESSION['username']);
    } else {
        $errors[] = 'Username and new password are required.';
    }
}

$res = mysqli_query($con, "SELECT username, full_name, email, phone, role, is_active, created_at FROM del_login ORDER BY created_at DESC");
?>

<style>
    :root {
        --primary-brand: #7c3aed;
        --primary-soft: #ede9fe;
        --text-main: #1f2a44;
        --text-muted: #64748b;
        --card-shadow: 0 10px 22px rgba(30, 64, 175, 0.12);
    }
    
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    .page-header h4 { font-weight: 700; color: var(--text-main); }
    
    .admin-card-modern {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-header-custom {
        background: rgba(255, 255, 255, 0.6);
        border-bottom: 1px solid #bfdbfe;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Form Styling */
    .form-control {
        border-color: #e5e7eb;
        padding: 0.6rem 0.85rem;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: var(--primary-brand);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .input-group-text {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: var(--text-muted);
    }

    /* Table Styling */
    .table-modern { margin-bottom: 0; }
    .table-modern thead th {
        background-color: #f9fafb;
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .table-modern tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: var(--text-main);
        border-bottom: 1px solid #f3f4f6;
    }
    .table-modern tr:last-child td { border-bottom: none; }
    
    /* Avatar */
    .agent-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-soft);
        color: var(--primary-brand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* Badges */
    .status-badge {
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-active { background-color: #dcfce7; color: #166534; }
    .badge-disabled { background-color: #f3f4f6; color: #4b5563; }

    /* Action Buttons */
    .btn-action-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-toggle-on { background: #fee2e2; color: #991b1b; border: none; }
    .btn-toggle-on:hover { background: #fecaca; }
    .btn-toggle-off { background: #dcfce7; color: #166534; border: none; }
    .btn-toggle-off:hover { background: #bbf7d0; }

    .reset-pass-group { max-width: 250px; }
</style>

<div class="container py-4">
    
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="page-header">
                <h4 class="mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Delivery Agents</h4>
                <div class="text-muted small">Create and manage delivery accounts</div>
            </div>
        </div>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <div>
                <?php foreach($errors as $e){ echo '<div>'.htmlspecialchars($e).'</div>'; } ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-12 col-xl-4">
            <div class="admin-card-modern h-100">
                <div class="card-header-custom">
                    <i class="bi bi-person-plus"></i> Register New Agent
                </div>
                <div class="p-4">
                    <form method="post" action="delivery_agents.php">
                        <div class="mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Account Info</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="full_name" placeholder="Full Name">
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Username" required>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Initial Password" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Contact Details</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="Email Address">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" name="phone" placeholder="Phone Number">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="create_agent" class="btn btn-primary">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="admin-card-modern h-100">
                <div class="card-header-custom justify-content-between">
                    <span><i class="bi bi-list-ul"></i> Agent Directory</span>
                    <span class="badge bg-light text-secondary border">Total: <?php echo mysqli_num_rows($res); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Reset Password</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($res && mysqli_num_rows($res)>0): ?>
                            <?php while($r = mysqli_fetch_assoc($res)): 
                                // Generate Initials
                                $display_name = !empty($r['full_name']) ? $r['full_name'] : $r['username'];
                                $words = explode(" ", $display_name);
                                $initials = strtoupper(substr($words[0], 0, 1));
                                if(count($words)>1) $initials .= strtoupper(substr($words[1], 0, 1));
                                else $initials = strtoupper(substr($r['username'], 0, 2));
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="agent-avatar me-3"><?php echo $initials; ?></div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($display_name); ?></div>
                                                <div class="small text-muted">@<?php echo htmlspecialchars($r['username']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <?php if($r['email']): ?>
                                            <div class="small text-dark mb-1"><i class="bi bi-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($r['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if($r['phone']): ?>
                                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($r['phone']); ?></div>
                                        <?php endif; ?>
                                        <?php if(!$r['email'] && !$r['phone']) echo '<span class="text-muted small">-</span>'; ?>
                                    </td>

                                    <td>
                                        <?php if((int)$r['is_active'] === 1): ?>
                                            <span class="status-badge badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-disabled">Disabled</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <form method="post" action="delivery_agents.php" class="reset-pass-group">
                                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($r['username']); ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="password" name="new_password" class="form-control" placeholder="New pass" required>
                                                <button type="submit" name="reset_password" class="btn btn-outline-secondary" title="Update Password">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>

                                    <td class="text-end">
                                        <form method="post" action="delivery_agents.php">
                                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($r['username']); ?>">
                                            <input type="hidden" name="is_active" value="<?php echo ((int)$r['is_active'] === 1) ? 0 : 1; ?>">
                                            
                                            <?php if((int)$r['is_active'] === 1): ?>
                                                <button type="submit" name="toggle_active" class="btn-action-icon btn-toggle-on ms-auto" title="Disable Account">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="toggle_active" class="btn-action-icon btn-toggle-off ms-auto" title="Activate Account">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-4 d-block mb-3 opacity-25"></i>
                                    No delivery agents found. Use the form to add one.
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

<?php include(__DIR__ . '/../footer.php'); ?>