<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
     <link rel="stylesheet" href="../css/bootstrap.min.css">
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
<body>
    <?php
    include '../admin/conn.php';
    if(!isset($con) || !$con){
        echo "<script>alert('Database connection failed. Please contact admin.');</script>";
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
            echo "<script>alert('Please enter email and password');</script>";
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
                    $ok = false;
                    if(password_verify($password, $stored)){
                        $ok = true;
                    } elseif($stored === $password) {
                        // legacy plaintext password, rehash and update
                        $ok = true;
                        $newhash = password_hash($password, PASSWORD_DEFAULT);
                        mysqli_query($con, "UPDATE cust_reg SET c_password='".mysqli_real_escape_string($con,$newhash)."' WHERE c_email='".mysqli_real_escape_string($con,$username)."'");
                    }

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
                        // if a return URL was provided, redirect there, otherwise go to index
                        $safeRet = '';
                        if(!empty($return_url)){
                            // return_url comes from REQUEST_URI encoded by header; decode and use
                            $safeRet = rawurldecode($return_url);
                        }
                        $dest = $safeRet ? htmlspecialchars($safeRet, ENT_QUOTES) : 'profile.php';
                        echo "<script>alert('Login successful!'); window.location.href='".$dest."';</script>"; exit;
                    } else {
                        echo "<script>alert('Incorrect password');</script>";
                    }
                } else {
                    echo "<script>alert('No account found with that email');</script>";
                }

                mysqli_stmt_close($stmt);
            } else {
                echo "<script>alert('Database query failed: ".mysqli_error($con)."');</script>";
            }
        }
    }

        ?>
<div class="auth-wrapper">
    <div class="auth-card bg-white">
        <div class="p-4 text-center border-bottom">
            <a class="brand h3" href="../index.php">Shree Swami Samarth</a>
            <div class="small text-muted">Welcome back — please sign in</div>
        </div>
        <div class="p-4">
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
                    <a href="register.php" class="small">Create account</a>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="submit" class="btn btn-primary">Sign In</button>
                    <a href="register.php" class="btn btn-outline-secondary">Create an account</a>
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