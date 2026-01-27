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
    if(isset($_POST['submit'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    
    $sqlq="select * from user_login where username='$username' AND password='$password'";
    $result=mysqli_query($con,$sqlq);
    if(mysqli_num_rows($result)>0){
        $_SESSION['is_login']=true;
        $_SESSION['username']=$username;
        header('location:index.php');
        exit;

    }

    else{
        echo"<script>alert('login Failed');</script>";
    }

    }


    ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6">
            <div class="alert alert-primary mt-5 shadow text-center" role="alert">
                Admin Login
            </div>
        <form action="login.php" method="post" class="mt-2 shadow-lg p-4">
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Username</label>
                <input type="text" class="form-control" name="username">
                <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" name="password">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Check me out</label>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        </form>
            </div>
        </div>
    </div>
</div>
<
</div>
</body>
</html>