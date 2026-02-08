

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
        <style>
            body{ background: linear-gradient(180deg,#f4f7fb,#ffffff); }
            .auth-wrapper{ min-height:80vh; display:flex; align-items:center; justify-content:center; }
            .auth-card{ max-width:520px; width:100%; border-radius:10px; overflow:hidden; box-shadow:0 8px 20px rgba(12,32,63,0.06); }
            .brand{ font-weight:800; color:#0d6efd; text-decoration:none; }
            .form-control{ border-radius:6px; }
            .form-control-sm{ padding:0.4rem 0.75rem; font-size:0.9rem; }
            .btn-primary, .btn-success{ border-radius:6px; padding:0.45rem 0.75rem; }
            .auth-card .p-4{ padding:1rem !important; }
            .mb-3{ margin-bottom:0.5rem !important; }
            .small.text-muted{ font-size:0.85rem; }
        </style>
</head>
<body>
        <div class="auth-wrapper">
            <div class="auth-card bg-white">
                <div class="p-3 text-center border-bottom">
                    <a class="brand h3" href="../index.php">Shree Swami Samarth</a>
                    <div class="small text-muted">Create your account</div>
                </div>
                <div class="p-3">
                    <form action="register.php" method="POST" id="regForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label h6 fw-semibold">Full Name</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name" required placeholder="name">
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" class="form-control form-control-sm" id="contact" name="contact" required placeholder="+91 9876543210">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="email" name="email" required placeholder="you@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control form-control-sm" id="address" name="address" required placeholder="Street, House no..." rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control form-control-sm" id="city" name="city" required placeholder="City">
                            </div>
                            <div class="col-6 mb-2">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control form-control-sm" id="state" name="state" required placeholder="State">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control form-control-sm" id="pincode" name="pincode" required placeholder="Postal code">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-sm" id="password" name="password" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label for="password2" class="form-label">Confirm</label>
                                <input type="password" class="form-control form-control-sm" id="password2" name="password2" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary btn-sm">Create Account</button>
                        </div>
                    </form>
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

    // server-side required fields validation
    $required = [
        'Full name' => $name,
        'Contact' => $contact,
        'Email' => $email,
        'Address' => $address,
        'City' => $city,
        'State' => $state,
        'Pincode' => $pincode,
        'Password' => $password,
    ];
    foreach($required as $label => $val){
        if(strlen(trim($val)) === 0){
            echo "<script>alert('Please fill the required field: $label'); window.history.back();</script>"; exit;
        }
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Please enter a valid email address'); window.history.back();</script>"; exit;
    }

    include('../admin/conn.php');

    // ensure c_password column exists and can store hashes
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
        @mysqli_query($con, "ALTER TABLE cust_reg ADD COLUMN c_password VARCHAR(255) DEFAULT NULL");
    }

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