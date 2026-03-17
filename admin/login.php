<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}

if (!empty($_SESSION['is_login']) && ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: products_card.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
        <style>
            *{ box-sizing:border-box; }
            body{ 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
                    radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
                    radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
                    linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
                margin: 0;
            }
            .auth-wrapper{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
            .auth-card{ max-width:520px; width:100%; border-radius:14px; overflow:hidden; background: linear-gradient(155deg, rgba(245, 243, 255, 0.95) 0%, rgba(238, 246, 255, 0.95) 55%, rgba(240, 253, 244, 0.95) 100%); border: 1px solid #bfdbfe; box-shadow:0 18px 36px rgba(30, 64, 175, 0.14); }
            .brand{ 
                font-weight: 900; 
                background: linear-gradient(to right, #4338ca, #be185d);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-decoration:none;
                display: inline-block;
                margin-bottom: 8px;
            }
            .card-header{ padding:24px; text-align:center; border-bottom:1px solid #dbeafe; }
            .card-body{ padding:24px; }
            .text-muted{ color:#64748b; }
            .small{ font-size:0.875rem; }
            .alert{
                padding: 12px 14px;
                border-radius: 10px;
                margin-bottom: 18px;
                border: 1px solid #fecaca;
                background: #fef2f2;
                color: #b91c1c;
            }
            .field{ margin-bottom:18px; }
            .form-label{ display:block; margin-bottom:8px; font-weight:600; color:#1f2a44; }
            .form-control{ border-radius:10px; border-color:#bfdbfe; background:#f8fbff; }
            .form-control{
                width:100%;
                border:1px solid #bfdbfe;
                padding:12px 14px;
                font-size:1rem;
                color:#1f2a44;
                outline:none;
            }
            .form-control:focus{ border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(124, 58, 237,.15); }
            .meta-row{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
            .form-check{ display:flex; align-items:center; gap:8px; color:#334155; }
            .form-check-input{ margin:0; }
            .link-help{ color:#6d28d9; text-decoration:none; }
            .link-help:hover{ text-decoration:underline; }
            .btn-primary{ width:100%; border-radius:10px; border:none; background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%); color:#fff; padding:12px 16px; font-size:1rem; font-weight:700; cursor:pointer; }
            .btn-primary:hover{ background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%); }
            @media (max-width: 480px){
                .auth-wrapper{ padding:16px; }
                .card-header, .card-body{ padding:18px; }
                .meta-row{ flex-direction:column; align-items:flex-start; }
            }
        </style>
</head>
<body>
    <?php
    $login_error = '';
    if(isset($_GET['toast']) && trim((string)$_GET['toast']) !== ''){
        $login_error = trim((string)$_GET['toast']);
    }
    if(isset($_POST['submit'])){
        include 'conn.php';
        $username = mysqli_real_escape_string($con, trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        $sqlq = "SELECT username, password FROM user_login WHERE username='$username' LIMIT 1";
        $result = mysqli_query($con, $sqlq);
        if($result && mysqli_num_rows($result)>0){
            $row = mysqli_fetch_assoc($result);
            $stored = (string)($row['password'] ?? '');
            $ok = ($stored === $password);
            if($ok){
                session_regenerate_id(true);
                $_SESSION['is_login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                // redirect to products page after successful admin login
                header('Location: products_card.php'); exit;
            }
        }
        $login_error = 'Login failed. Please check username and password.';
    }


    ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="card-header">
            <a class="brand h3" href="../admin/index.php">Admin Panel</a>
            <div class="small text-muted">Sign in to manage the store</div>
        </div>
        <div class="card-body">
            <?php if(!empty($login_error)): ?>
                <div class="alert"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="field">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required>
                </div>
                <div class="field">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="meta-row">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberAdmin">
                        <label class="form-check-label" for="rememberAdmin">Remember me</label>
                    </div>
                    <a href="#" class="small link-help">Need help?</a>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>
    </div>
</div>
<?php include(__DIR__ . '/../footer.php'); ?>
</body>
</html>