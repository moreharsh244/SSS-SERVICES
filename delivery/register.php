<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_DELIVERY_SESS');
    session_start();
}
if(!isset($_SESSION['is_login']) || ($_SESSION['role'] ?? '') !== 'admin'){
    header('location:login.php');
    exit;
}
include '../admin/conn.php';
include 'helpers.php';
ensure_delivery_tables($con);

$errors = [];
$success = '';
if(isset($_POST['register'])){
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if($username === '' || $password === '' || $confirm === ''){
        $errors[] = 'Username and password are required.';
    }
    if($password !== $confirm){
        $errors[] = 'Passwords do not match.';
    }

    if(empty($errors)){
        $u_esc = mysqli_real_escape_string($con, $username);
        $check = mysqli_query($con, "SELECT * FROM del_login WHERE username='$u_esc' LIMIT 1");
        if($check && mysqli_num_rows($check)>0){
            $errors[] = 'Username already exists.';
        } else {
            $fn = mysqli_real_escape_string($con, $full_name);
            $em = mysqli_real_escape_string($con, $email);
            $ph = mysqli_real_escape_string($con, $phone);
            $pass_plain = mysqli_real_escape_string($con, $password);
            $ins = "INSERT INTO del_login (username,password,full_name,email,phone,role) VALUES ('$u_esc','$pass_plain','$fn','$em','$ph','delivery')";
            if(mysqli_query($con, $ins)){
                log_delivery_action($con, $username, 'register', 'Delivery agent registered');
                $success = 'Registration successful. Please sign in.';
            } else {
                $errors[] = 'Registration failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Registration</title>
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
            max-width: 560px; 
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
        .btn-delivery-register {
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
        .btn-delivery-register:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            color: white;
        }
        .alert {
            border-radius: 12px;
            border: none;
        }
        .link-delivery {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .link-delivery:hover {
            color: #4f46e5;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="icon-wrapper">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h2>Delivery Agent Registration</h2>
                    <div class="text-muted">Create your delivery agent account</div>
                </div>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php foreach($errors as $e){ echo '<div>'.htmlspecialchars($e).'</div>'; } ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-person-circle me-1"></i>
                                Username
                            </label>
                            <input type="text" class="form-control" name="username" placeholder="Enter username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-person-badge me-1"></i>
                                Full Name
                            </label>
                            <input type="text" class="form-control" name="full_name" placeholder="Enter full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                Email
                            </label>
                            <input type="email" class="form-control" name="email" placeholder="Enter email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-telephone me-1"></i>
                                Phone
                            </label>
                            <input type="text" class="form-control" name="phone" placeholder="Enter phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-shield-lock me-1"></i>
                                Password
                            </label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-shield-check me-1"></i>
                                Confirm Password
                            </label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="register" class="btn btn-delivery-register">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <span class="text-muted small">
                        Already have an account? 
                        <a href="login.php" class="link-delivery">Sign in</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
