

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>User Registration</h3>
                    </div>
                    <div class="card-body shadow-lg">
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="contact" class="form-label">Enter Your Contact Number</label>
                                <input type="text" class="form-control" id="contact" name="contact" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Enter Your Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Enter Your Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>
<?php
if(isset($_POST['register'])){
    $contact=$_POST['contact'];
    $email=$_POST['email'];
    $password=$_POST['password'];

    // Database connection
    include('../admin/conn.php');
    // Insert user data into database
    $sqlq="select * from cust_reg where c_email='$email'";
    $result=mysqli_query($con,$sqlq);
    $num=mysqli_num_rows($result);
    if($num>0){
            echo "<script>alert('Email already registered!'); window.location.href='register.php';</script>";
            exit();

    }
    $sql="INSERT INTO `cust_reg` ( `c_email`, `c_contact`, `c_password`) VALUES ('$email', '$contact', '$password')";

    if(mysqli_query($con, $sql)){
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }

    mysqli_close($con);
}


?>