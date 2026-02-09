<?php
session_start();
?>
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
                <div class="d-grid">
                    <button type="submit" name="register" class="btn btn-primary">Create Admin</button>
                </div>
        </form>
    </div>
</body>
</html>
include 'conn.php';
if(isset($_POST['register'])){
    $username = trim($_POST['uname'] ?? '');
    $password = $_POST['pass'] ?? '';

    // ensure created_at column exists
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user_login' AND COLUMN_NAME='created_at'";
    $cres = mysqli_query($con, $col_check);
    if(!$cres || mysqli_num_rows($cres)===0){
        @mysqli_query($con, "ALTER TABLE user_login ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

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

    $u_esc = mysqli_real_escape_string($con, $username);
    $check = mysqli_query($con, "SELECT * FROM user_login WHERE username='$u_esc' LIMIT 1");
    if($check && mysqli_num_rows($check)>0){
        echo "<script>alert('Username already exists');</script>"; exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = "INSERT INTO user_login (username, password) VALUES ('$u_esc', '".mysqli_real_escape_string($con,$hash)."')";
    $result = mysqli_query($con, $ins);
    if($result){
        $_SESSION['is_login'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        header('location:index.php'); exit;
    } else {
        echo "<script>alert('Registration Failed');</script>";
    }
}
?>