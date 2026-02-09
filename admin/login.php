<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/pc-theme.css">
    <script src="../js/bootstrap.min.js"></script>
        <style>
            body{ background: linear-gradient(180deg,#f4f7fb,#ffffff); }
            .auth-wrapper{ min-height:80vh; display:flex; align-items:center; justify-content:center; }
            .auth-card{ max-width:520px; width:100%; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(12,32,63,0.08); }
            .brand{ font-weight:800; color:#0d6efd; text-decoration:none; }
            .form-control{ border-radius:8px; }
            .btn-primary{ border-radius:8px; }
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