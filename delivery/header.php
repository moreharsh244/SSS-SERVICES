<?php
if (session_status() === PHP_SESSION_NONE) {
  // Keep session alive while browser is open and across refreshes
  session_name('SSS_DELIVERY_SESS');
  ini_set('session.gc_maxlifetime', '86400');
  ini_set('session.cookie_lifetime', '0');
  ini_set('session.gc_probability', '1');
  ini_set('session.gc_divisor', '100');
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
  session_start();
}
if(!isset($_SESSION['is_login'])){
  header('location:login.php');
  exit;
}
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'delivery'){
    header('location:login.php');
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Portal</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pc-theme.css">
    <link rel="stylesheet" href="../user/user.css">
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="delivery.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
          /* ensure navbar and dropdowns render above other page components */
          .navbar { position: relative; z-index: 4000; }
          .navbar .dropdown-menu { z-index: 4001; }
        </style>
</head>  
<body class="delivery-body user-area pc-theme">
    <!-- navbar start -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php" style="font-size: 30px;">
      <img src="../img/logo-mark.svg" alt="Shree Swami Samarth" style="height:36px;width:36px;margin-right:10px;">
      <span>Shree Swami Samarth</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php">Deliveries</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="audit_log.php">Audit Log</a>
        </li>
        
      </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2"></i>
            <?php echo $_SESSION['username']; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
            <li><a class="dropdown-item" href="index.php">Deliveries</a></li>
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><a class="dropdown-item" href="audit_log.php">Audit Log</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
   
    </div>
  </div>
</nav>
<!-- navbar End -->
 <!-- container start  -->
  <div class="container mt-3">
    <div class="row">
      <div class="col-12 d-flex justify-content-center">
        <nav class="nav nav-pills justify-content-center flex-wrap gap-2 bg-white rounded shadow-sm p-2">
          <a class="nav-link" href="index.php"><i class="bi bi-truck me-2"></i>Deliveries</a>
          <a class="nav-link" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a>
          <a class="nav-link" href="audit_log.php"><i class="bi bi-clock-history me-2"></i>Audit Log</a>
        </nav>
      </div>
    </div>
    <div class="row mt-4 justify-content-center">