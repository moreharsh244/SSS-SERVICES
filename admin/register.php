<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Admin Registration</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="uname" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="pass" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>
<?php
include 'conn.php';
if(isset($_POST['register'])){
$username=$_POST['uname'];       
$password=$_POST['pass'];
$sqlq="INSERT INTO user_login (username,password) VALUES('$username','$password')";
$result=mysqli_query($con,$sqlq);
if($result){
    $_SESSION['is_login']=true;
    $_SESSION['uname']=$_POST['uname'];
    header('location:index.php');
    exit;
}else{
    echo "<script>alert('Registration Failed');</script>";
}
}
?>