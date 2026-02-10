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
    <script src="../js/bootstrap.min.js"></script>
    <style>
        body{ background: linear-gradient(180deg,#fff4f4,#ffffff); }
        .auth-card{ max-width:560px; margin:4rem auto 0; border-radius:12px; box-shadow:0 10px 30px rgba(12,32,63,0.08); }
        .form-control{ border-radius:8px; }
        .btn-primary{ border-radius:8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="auth-card bg-white p-4">
                <div class="alert alert-danger shadow text-center" role="alert">Delivery Agent Registration</div>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-warning">
                        <?php foreach($errors as $e){ echo '<div>'.htmlspecialchars($e).'</div>'; } ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="register" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="login.php">Already have an account? Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
