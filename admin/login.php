<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/pc-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="../js/bootstrap.min.js"></script>
        <style>
            body{ 
                font-family: 'Poppins', sans-serif;
                background:
                    radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
                    radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
                    radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
                    linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
            }
            .auth-wrapper{ min-height:80vh; display:flex; align-items:center; justify-content:center; }
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
            .form-control{ border-radius:10px; border-color:#bfdbfe; background:#f8fbff; }
            .form-control:focus{ border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(124, 58, 237,.15); }
            .btn-primary{ border-radius:10px; border:none; background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%); }
            .btn-primary:hover{ background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%); }
        </style>
</head>
<body class="pc-theme">
    <?php
    include 'conn.php';
    // ensure password column exists and can store hashes
    $colInfoQ = "SELECT CHARACTER_MAXIMUM_LENGTH, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user_login' AND COLUMN_NAME='password' LIMIT 1";
    $colRes = mysqli_query($con, $colInfoQ);
    if($colRes && mysqli_num_rows($colRes)>0){
        $col = mysqli_fetch_assoc($colRes);
        $len = intval($col['CHARACTER_MAXIMUM_LENGTH'] ?? 0);
        $dt = strtolower($col['DATA_TYPE'] ?? '');
        if(($dt === 'varchar' && $len < 100) || ($dt === 'char' && $len < 100)){
            @mysqli_query($con, "ALTER TABLE user_login MODIFY password VARCHAR(255) NOT NULL");
        }
    } else {
        @mysqli_query($con, "ALTER TABLE user_login ADD COLUMN password VARCHAR(255) DEFAULT NULL");
    }
    if(isset($_POST['submit'])){
        $username = mysqli_real_escape_string($con, trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        $sqlq = "SELECT * FROM user_login WHERE username='$username' LIMIT 1";
        $result = mysqli_query($con, $sqlq);
        if($result && mysqli_num_rows($result)>0){
            $row = mysqli_fetch_assoc($result);
            $stored = $row['password'];
            $ok = ($stored === $password);
            if($ok){
                session_regenerate_id(true);
                $_SESSION['is_login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                // redirect to admin index which shows all products (product cards)
                header('Location: products_card.php'); exit;
            }
        }
        echo "<script>alert('login Failed');</script>";
    }


    ?>
<div class="auth-wrapper">
    <div class="auth-card bg-white">
        <div class="p-4 text-center border-bottom">
            <a class="brand h3" href="../admin/index.php">Admin Panel</a>
            <div class="small text-muted">Sign in to manage the store</div>
        </div>
        <div class="p-4">
            <form action="login.php" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberAdmin">
                        <label class="form-check-label ms-2" for="rememberAdmin">Remember me</label>
                    </div>
                    <a href="#" class="small">Need help?</a>
                </div>
                <div class="d-grid">
                    <button type="submit" name="submit" class="btn btn-primary">Sign In</button>
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