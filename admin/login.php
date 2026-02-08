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
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
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
            $ok = false;
            if(password_verify($password, $stored)){
                $ok = true;
            } elseif($stored === $password) {
                // legacy plaintext password, rehash
                $ok = true;
                $newhash = password_hash($password, PASSWORD_DEFAULT);
                @mysqli_query($con, "UPDATE user_login SET password='".mysqli_real_escape_string($con,$newhash)."' WHERE username='$username'");
            }
            if($ok){
                $_SESSION['is_login'] = true;
                $_SESSION['username'] = $username;
                header('location:view_product.php'); exit;
            }
        }
        echo "<script>alert('login Failed');</script>";
    }


    ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6">
            <div class="alert alert-primary mt-5 shadow text-center" role="alert">
                Admin Login
            </div>
        <form action="login.php" method="post" class="mt-2 shadow-lg p-4 needs-validation" novalidate>
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
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                </form>
                                <script>
                                (function(){
                                    'use strict'
                                    var forms = document.querySelectorAll('.needs-validation')
                                    Array.prototype.slice.call(forms).forEach(function (form) {
                                        form.addEventListener('submit', function (event) {
                                            if (!form.checkValidity()) {
                                                event.preventDefault()
                                                event.stopPropagation()
                                            }
                                            form.classList.add('was-validated')
                                        }, false)
                                    })
                                })()
                                </script>
            </div>
        </div>
    </div>
</div>
</body>
</html>