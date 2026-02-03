<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if(!isset($_SESSION['is_login'])){
  header('location:login.php');
  exit;
}
if(!defined('HEADER_INCLUDED')) define('HEADER_INCLUDED', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shree Swami Samarth - Hardware</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="user.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="user-area">
<!-- Primary navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="view_products.php">Shree Swami Samarth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <form class="d-flex ms-3 me-auto" action="view_products.php" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products or brands" aria-label="Search">
        <button class="btn btn-outline-primary" type="submit">Search</button>
      </form>

      <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
        <li class="nav-item me-2">
          <a class="btn btn-sm btn-outline-secondary" href="myorder.php">My Orders</a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2"></i>
            <?php echo htmlentities($_SESSION['username']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
            <li><a class="dropdown-item" href="view_products.php">Browse Products</a></li>
            <li><a class="dropdown-item" href="myorder.php">My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Page layout -->
<div class="container-fluid mt-3">
  <div class="row">
    <aside class="col-md-3 col-lg-2 mb-3">
      <div class="card shadow-sm">
        <div class="card-body p-2">
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link py-2" href="view_products.php">Browse Products</a></li>
            <li class="nav-item"><a class="nav-link py-2" href="myorder.php">My Orders</a></li>
            <li class="nav-item"><a class="nav-link py-2" href="orderstatus.php">Order Status</a></li>
          </ul>
        </div>
      </div>
    </aside>
    <main class="col-md-9 col-lg-10">
