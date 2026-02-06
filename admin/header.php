<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['is_login'])) {
  header('location:login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../user/user.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Bogle&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<!-- Primary navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="products_card.php" style="font-size: 30px;">Shree Swami Samarth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  

      <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
        <li class="nav-item me-2">
          <a class="btn btn-sm btn-outline-secondary" href="view_product.php">View Products</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2"></i>
            <?php echo htmlentities($_SESSION['username']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
            <li><a class="dropdown-item" href="myorder.php">My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    
    </div>
  </div>
</nav>
<!-- navbar End  -->

<!-- Sub navigation (horizontal) -->
<div class="container mt-3">
  <div class="row">
    <div class="col-12 d-flex justify-content-center">
      <nav class="nav nav-pills justify-content-center flex-wrap gap-2 bg-white rounded shadow-sm p-2">
        <a class="nav-link" href="add_product.php"><i class="bi bi-plus-square me-2"></i>Add Product</a>
        <a class="nav-link" href="view_product.php"><i class="bi bi-card-list me-2"></i>View Products</a>
        <a class="nav-link" href="products_card.php"><i class="bi bi-grid-3x3-gap me-2"></i>Product Cards</a>
        <a class="nav-link" href="orders_list.php"><i class="bi bi-bag-check me-2"></i>Orders List</a>
        <a class="nav-link" href="builds.php"><i class="bi bi-hammer me-2"></i>Builds</a>

      </nav>
    </div>
  </div>
  <div class="row mt-4 justify-content-center">
    <main class="col-12">
  <!-- Image preview modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content bg-transparent border-0 shadow-none">
        <div class="modal-body text-center p-0">
          <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
          <img id="modalImage" src="" alt="Preview" class="img-modal-img rounded">
        </div>
      </div>
    </div>
  </div>

<?php
