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
    include '../admin/conn.php';
    if(isset($_POST['submit'])){
        $username = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $sqlq = "SELECT * FROM cust_reg WHERE c_email='$username' LIMIT 1";
        $result = mysqli_query($con, $sqlq);
        if($result && mysqli_num_rows($result)>0){
            $row = mysqli_fetch_assoc($result);
            $stored = $row['c_password'];
            $ok = false;
            if(password_verify($password, $stored)){
                $ok = true;
            } elseif($stored === $password) {
                // legacy plaintext password, rehash and update
                $ok = true;
                $newhash = password_hash($password, PASSWORD_DEFAULT);
                @mysqli_query($con, "UPDATE cust_reg SET c_password='".mysqli_real_escape_string($con,$newhash)."' WHERE c_email='$username'");
            }

            if($ok){
                $_SESSION['is_login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $row['id'] ?? $row['cid'] ?? null;
                echo "<script>alert('Login successful!'); window.location.href='index.php';</script>"; exit;
            }
        }
        echo "<script>alert('Login Failed');</script>";
    }
    
    
    
    ?>
<div class="container">
    <div class="row justify-content-center">
    
        <div class="col-sm-6 ">
                <div class="alert alert-danger mt-5 shadow text-center" role="alert">
                    User Login
                </div>
            <form action="login.php" method="post" class="mt-2 shadow-lg p-4 needs-validation" novalidate>
             
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email" aria-describedby="emailHelp" required>
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="exampleInputPassword1" required>
                </div>
               
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                <a href="register.php" class="btn btn-success">Register Here</a>
            

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
    
</body>
</html>