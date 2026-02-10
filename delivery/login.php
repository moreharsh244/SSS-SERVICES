<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_DELIVERY_SESS');
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
    <title>Delivery Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/pc-theme.css">
    <link rel="stylesheet" href="delivery.css">
    <script src="../js/bootstrap.min.js"></script>
    <style>
        body{ background: linear-gradient(180deg,#fff4f4,#ffffff); }
        .auth-card{ max-width:520px; margin:5rem auto 0; border-radius:12px; box-shadow:0 10px 30px rgba(12,32,63,0.08); }
        .form-control{ border-radius:8px; }
        .btn-primary{ border-radius:8px; }
    </style>
</head>
<body class="pc-theme">
    <?php
    include '../admin/conn.php';
    include 'helpers.php';
    ensure_delivery_tables($con);

    if(isset($_POST['submit'])){
        $username = mysqli_real_escape_string($con, trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        $sqlq = "SELECT * FROM del_login WHERE username='$username' AND is_active=1 LIMIT 1";
        $result = mysqli_query($con, $sqlq);
        if($result && mysqli_num_rows($result)>0){
            $row = mysqli_fetch_assoc($result);
            $stored = $row['password'];
            $ok = ($stored === $password);
            if($ok){
                session_regenerate_id(true);
                $_SESSION['is_login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'delivery';
                log_delivery_action($con, $username, 'login', 'Delivery agent logged in');
                header('location:index.php');
                exit;
            }
        }
        echo "<script>alert('Login Failed');</script>";
    }
    
    
    
    ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-7">
            <div class="auth-card bg-white p-4">
                <div class="delivery-hero mb-3">
                    <h2 class="mb-1">Delivery Agent Login</h2>
                    <div class="text-muted">Secure access to your assigned deliveries.</div>
                </div>
                <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                </div>
                <button type="submit" name="submit" class="btn btn-delivery">Sign In</button>
                </form> 
                <div class="text-center mt-3">
                    <span class="text-muted">Contact admin to create an account</span>
                </div>
            </div>
        </div>
    </div>
</div>
    
</body>
</html>