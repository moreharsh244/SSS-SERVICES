<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    ini_set('session.gc_maxlifetime', '86400');
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
     <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="user.css">
        <link rel="stylesheet" href="../css/pc-theme.css">
    <script src="../js/bootstrap.min.js"></script>
        <style>
            body{
                background:
                    radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
                    radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
                    radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.10) 0%, rgba(16, 185, 129, 0) 30%),
                    linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
            }
            .auth-wrapper{ min-height:80vh; display:flex; align-items:center; justify-content:center; }
            .auth-card{
                max-width:520px; width:100%; border-radius:14px; overflow:hidden;
                background: linear-gradient(155deg, rgba(245,243,255,0.95) 0%, rgba(238,246,255,0.95) 55%, rgba(240,253,244,0.95) 100%);
                border: 1px solid #bfdbfe;
                box-shadow:0 18px 36px rgba(30,64,175,0.14);
            }
            .brand{ 
                font-weight: 900; 
                background: linear-gradient(to right, #4338ca, #be185d);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-decoration:none;
                display: inline-block;
                margin-bottom: 8px;
            }
            .form-control{ border-radius:10px; border-color:#bfdbfe; background:#f8fbff; }
            .form-control:focus{ border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(59,130,246,.15); }
            .btn-primary{ border-radius:10px; border:none; background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%); }
            .btn-primary:hover{ background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%); }
            .btn-outline-secondary{ border-radius:10px; border-color:#93c5fd; color:#0369a1; }
            .btn-outline-secondary:hover{ background:#e0f2fe; color:#0c4a6e; border-color:#7dd3fc; }
        </style>
</head>
<body class="pc-theme">
    <?php
    include '../admin/conn.php';
    $login_error = '';
    if(isset($_GET['toast']) && trim((string)$_GET['toast']) !== ''){
        $login_error = trim((string)$_GET['toast']);
    }
    if(!isset($con) || !$con){
        $login_error = 'Unable to connect to database. Please contact support.';
    }

    // Ensure password column can store full hashes (bcrypt ~60 chars)
    if(isset($con) && $con){
        $colInfoQ = "SELECT CHARACTER_MAXIMUM_LENGTH, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='c_password' LIMIT 1";
        $colRes = mysqli_query($con, $colInfoQ);
        if($colRes && mysqli_num_rows($colRes)>0){
            $col = mysqli_fetch_assoc($colRes);
            $len = intval($col['CHARACTER_MAXIMUM_LENGTH'] ?? 0);
            $dt = strtolower($col['DATA_TYPE'] ?? '');
            if(($dt === 'varchar' && $len < 100) || ($dt === 'char' && $len < 100)){
                @mysqli_query($con, "ALTER TABLE cust_reg MODIFY c_password VARCHAR(255) NOT NULL");
            }
        } else {
            // column missing? try to add it
            @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN c_password VARCHAR(255) DEFAULT NULL");
        }
    }

    // preserve any return URL so form submits back with it
    $return_url = '';
    if(isset($_GET['return']) && strlen(trim($_GET['return']))>0){
        $return_url = $_GET['return'];
    }

    if(isset($_POST['submit'])){
        $username = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if($username === '' || $password === ''){
            $login_error = 'Please enter your email and password.';
        } else {
            // Select only needed columns that exist in our schema
            $stmt = mysqli_prepare($con, "SELECT c_password, c_name, cid FROM cust_reg WHERE c_email = ? LIMIT 1");
            if($stmt){
                mysqli_stmt_bind_param($stmt, 's', $username);
                mysqli_stmt_execute($stmt);

                if(function_exists('mysqli_stmt_get_result')){
                    $res = mysqli_stmt_get_result($stmt);
                    if($res && mysqli_num_rows($res) > 0){
                        $row = mysqli_fetch_assoc($res);
                        $stored = $row['c_password'] ?? '';
                        $nameVal = $row['c_name'] ?? null;
                        $idVal = $row['id'] ?? $row['cid'] ?? $row['c_id'] ?? null;
                    } else {
                        $stored = null;
                    }
                } else {
                    // fallback for environments without mysqli_stmt_get_result
                    mysqli_stmt_bind_result($stmt, $stored, $nameVal, $idVal);
                    if(!mysqli_stmt_fetch($stmt)){
                        $stored = null;
                    }
                }

                if(!empty($stored)){
                    $stored = trim($stored);
                    $ok = ($stored === $password);

                    if($ok){
                        $_SESSION['is_login'] = true;
                        // store email as session username to allow profile lookups
                        $_SESSION['username'] = $username;
                        // keep a display name separately
                        $_SESSION['display_name'] = $nameVal ?? '';
                        $_SESSION['user_id'] = $idVal ?? null;
                        // remember-me support: set persistent token and cookie
                        if(!empty($_POST['remember'])){
                            @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS remember_token VARCHAR(255) NULL");
                            @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN IF NOT EXISTS remember_expiry DATETIME NULL");
                            try{ $token = bin2hex(random_bytes(32)); } catch(Exception $e){ $token = bin2hex(openssl_random_pseudo_bytes(32)); }
                            $expiry = date('Y-m-d H:i:s', time()+30*24*3600);
                            $tok_esc = mysqli_real_escape_string($con, $token);
                            mysqli_query($con, "UPDATE cust_reg SET remember_token='$tok_esc', remember_expiry='$expiry' WHERE c_email='".mysqli_real_escape_string($con,$username)."' LIMIT 1");
                            setcookie('remember', $token, time()+30*24*3600, '/', '', false, true);
                        }
                        // Redirect to view_products.php after login (no popup)
                        header('Location: view_products.php');
                        exit;
                    } else {
                        $login_error = 'Incorrect password. Please try again.';
                    }
                } else {
                    $login_error = 'No account found with this email address.';
                }

                mysqli_stmt_close($stmt);
            } else {
                $login_error = 'An error occurred. Please try again later.';
            }
        }
    }

        ?>
<div class="auth-wrapper">
    <div class="auth-card bg-white">
        <div class="p-4 text-center border-bottom">
            <a class="brand h3" href="../index.php">Shree Swami Samarth</a>
            <div class="small text-muted">Welcome back — sign in to your account</div>
        </div>
        <div class="p-4">
            <?php if(!empty($login_error)): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form action="login.php<?php if(!empty($return_url)){ echo '?return='.rawurlencode($return_url); } ?>" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email" aria-describedby="emailHelp" required>
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="exampleInputPassword1" required>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                        <label class="form-check-label ms-2" for="remember">Remember me</label>
                    </div>
                    <a href="register.php" class="small">Register</a>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="submit" class="btn btn-primary">Sign In</button>
                    <a href="register.php" class="btn btn-outline-secondary">Register</a>
                </div>
            </form>
            <script>
                (function(){
                    'use strict'
                    var forms = document.querySelectorAll('.needs-validation')
                    Array.prototype.slice.call(forms).forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
                            form.classList.add('was-validated')
                        }, false)
                    })
                })()
            </script>
        </div>
    </div>
</div>
</body>
</html>