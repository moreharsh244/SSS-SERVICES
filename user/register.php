

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
                                        <form action="register.php" method="POST" id="regForm" class="needs-validation" novalidate>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe">
                                            </div>
                                            <div class="mb-3">
                                                <label for="contact" class="form-label">Contact Number</label>
                                                <input type="text" class="form-control" id="contact" name="contact" required placeholder="+91 9876543210">
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email address</label>
                                                <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                                            </div>
                                            <div class="mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <textarea class="form-control" id="address" name="address" placeholder="Street, House no..."></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="city" class="form-label">City</label>
                                                    <input type="text" class="form-control" id="city" name="city" placeholder="City">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="state" class="form-label">State</label>
                                                    <input type="text" class="form-control" id="state" name="state" placeholder="State">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="pincode" name="pincode" placeholder="Postal code">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="password" name="password" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="password2" class="form-label">Confirm Password</label>
                                                    <input type="password" class="form-control" id="password2" name="password2" required>
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" name="register" class="btn btn-success">Create Account</button>
                                            </div>
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
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if($password !== $password2){
        echo "<script>alert('Passwords do not match'); window.history.back();</script>"; exit;
    }

    include('../admin/conn.php');

    // ensure columns exist
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='c_name'";
    $cres = mysqli_query($con, $col_check);
    if(!$cres || mysqli_num_rows($cres)===0){
        @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN c_name VARCHAR(255) DEFAULT NULL");
    }
    $col_check2 = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME='created_at'";
    $cres2 = mysqli_query($con, $col_check2);
    if(!$cres2 || mysqli_num_rows($cres2)===0){
        @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
    // ensure address fields exist (use INFORMATION_SCHEMA for compatibility)
    $addrCols = ['c_address'=>'TEXT NULL','c_city'=>'VARCHAR(128) NULL','c_state'=>'VARCHAR(128) NULL','c_pincode'=>'VARCHAR(32) NULL'];
    $colQ = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cust_reg' AND COLUMN_NAME IN ('".implode("','",array_keys($addrCols))."')";
    $colRes = mysqli_query($con, $colQ);
    $existing = [];
    if($colRes){ while($r = mysqli_fetch_assoc($colRes)) $existing[] = $r['COLUMN_NAME']; }
    foreach($addrCols as $col => $def){ if(!in_array($col, $existing)){ @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN $col $def"); } }

    $email_esc = mysqli_real_escape_string($con, $email);
    $sqlq = "SELECT * FROM cust_reg WHERE c_email='$email_esc' LIMIT 1";
    $result = mysqli_query($con, $sqlq);
    if($result && mysqli_num_rows($result) > 0){
        echo "<script>alert('Email already registered!'); window.location.href='register.php';</script>"; exit;
    }

    $name_esc = mysqli_real_escape_string($con, $name);
    $contact_esc = mysqli_real_escape_string($con, $contact);
    $address_esc = mysqli_real_escape_string($con, $address);
    $city_esc = mysqli_real_escape_string($con, $city);
    $state_esc = mysqli_real_escape_string($con, $state);
    $pin_esc = mysqli_real_escape_string($con, $pincode);
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $ins = "INSERT INTO cust_reg (c_name, c_email, c_contact, c_password, c_address, c_city, c_state, c_pincode) VALUES ('$name_esc', '$email_esc', '$contact_esc', '$hash', '$address_esc', '$city_esc', '$state_esc', '$pin_esc')";
    if(mysqli_query($con, $ins)){
        echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>"; exit;
    } else {
        echo "<script>alert('Registration failed: ".mysqli_error($con)."'); window.location.href='register.php';</script>"; exit;
    }

    mysqli_close($con);
}

?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('regForm');
    const pw = document.getElementById('password');
    const pw2 = document.getElementById('password2');

    form.addEventListener('submit', function(e){
        // Bootstrap validation
        if(!form.checkValidity()){
            e.preventDefault(); e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        if(pw.value !== pw2.value){
            e.preventDefault(); e.stopPropagation();
            pw2.classList.add('is-invalid');
            alert('Passwords do not match');
            return;
        }
    });
});
</script>