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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="delivery.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="../js/bootstrap.min.js"></script>
    <style>
        body{ 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 30px 0;
        }
        .auth-card{ 
            max-width: 480px; 
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h2 {
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .auth-header .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
        }
        .auth-header .icon-wrapper i {
            font-size: 2.5rem;
            color: white;
        }
        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        .btn-delivery-login {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 14px 20px;
            border-radius: 12px;
            transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            width: 100%;
        }
        .btn-delivery-login:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            color: white;
        }
    </style>
</head>
<body>
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
        <div class="col-sm-12">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="icon-wrapper">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h2>Delivery Agent Login</h2>
                    <div class="text-muted">Secure access to your assigned deliveries</div>
                </div>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-person-circle me-1"></i>
                            Username
                        </label>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-shield-lock me-1"></i>
                            Password
                        </label>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-delivery-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form> 
                <div class="text-center mt-4">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Contact admin to create an account
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
    
</body>
</html>