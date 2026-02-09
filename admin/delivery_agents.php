<?php
if (session_status() === PHP_SESSION_NONE) {
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
                $success = 'Delivery agent created.';
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
    $success = 'Status updated.';
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
        $success = 'Password reset.';
        log_delivery_action($con, $username, 'admin_reset_password', 'Reset by admin '.$_SESSION['username']);
    } else {
        $errors[] = 'Username and new password are required.';
    }
}

$res = mysqli_query($con, "SELECT username, full_name, email, phone, role, is_active, created_at FROM del_login ORDER BY created_at DESC");
?>

<div class="col-12 col-lg-10 mx-auto">
    <div class="admin-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Delivery Agents</h4>
                <div class="small-muted">Manage delivery agent accounts</div>
            </div>
        </div>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-warning">
                <?php foreach($errors as $e){ echo '<div>'.htmlspecialchars($e).'</div>'; } ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Create Agent</div>
            <div class="card-body">
                <form method="post" action="delivery_agents.php" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="full_name" placeholder="Full name">
                    </div>
                    <div class="col-md-4">
                        <input type="email" class="form-control" name="email" placeholder="Email">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="phone" placeholder="Phone">
                    </div>
                    <div class="col-md-4">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" name="create_agent" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Reset Password</th>
                        <th>Toggle</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($res && mysqli_num_rows($res)>0): ?>
                    <?php while($r = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['username']); ?></td>
                            <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><?php echo htmlspecialchars($r['phone']); ?></td>
                            <td>
                                <?php if((int)$r['is_active'] === 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="delivery_agents.php" class="d-flex gap-2">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($r['username']); ?>">
                                    <input type="password" name="new_password" class="form-control form-control-sm" placeholder="New password" required>
                                    <button type="submit" name="reset_password" class="btn btn-sm btn-outline-secondary">Reset</button>
                                </form>
                            </td>
                            <td>
                                <form method="post" action="delivery_agents.php">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($r['username']); ?>">
                                    <input type="hidden" name="is_active" value="<?php echo ((int)$r['is_active'] === 1) ? 0 : 1; ?>">
                                    <button type="submit" name="toggle_active" class="btn btn-sm btn-outline-primary">
                                        <?php echo ((int)$r['is_active'] === 1) ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No delivery agents</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
