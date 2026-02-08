<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shree Swami Samarth</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
</head>
<body>
   <!-- navbar start  -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd; width:100%">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Shree Swami Samarth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="font-size: 20px;">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/index.php">Admin Login</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Customer Login
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="user/index.php">Login</a></li>
            <li><a class="dropdown-item" href="user/register.php">Register</a></li>
          </ul>
        </li>
      </ul>
      
      <form class="d-flex">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
   <!-- navbar end  -->

   <!-- ################################################################################################# -->

    <!-- slider start  -->
<div class="container mt-4">
  <div id="carouselExampleCaptions" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="img/pc1.jpg" class="d-block w-100 rounded" height="650px">
        <div class="carousel-caption d-none d-md-block hero-caption">
          <h5>Devotional Gifts</h5>
          <p>Hand-picked items for your worship and home puja.</p>
          <a class="btn btn-primary btn-lg" href="user/index.php">Shop Now</a>
        </div>
    </div>
    <div class="carousel-item">
      <img src="img/pc2.jpg" class="d-block w-100 rounded" height="650px">
        <div class="carousel-caption d-none d-md-block hero-caption">
          <h5>Quality Items</h5>
          <p>Quality assured products for everyday devotion.</p>
          <a class="btn btn-outline-light btn-lg" href="view_products.php">Browse</a>
        </div>
    </div>
    <div class="carousel-item">
      <img src="img/pc3.jpg" class="d-block w-100 rounded" height="650px">
        <div class="carousel-caption d-none d-md-block hero-caption">
          <h5>Fast Delivery</h5>
          <p>Reliable shipping across locations.</p>
          <a class="btn btn-primary btn-lg" href="delivery/index.php">Delivery Info</a>
        </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
  </div>
</div>

    <!-- slider end  -->

    <!-- welcome section -->
<section class="container my-4">
  <div class="p-4 bg-light rounded shadow-sm text-center">
    <h2 class="display-6 mb-2">Welcome to Shree Swami Samarth</h2>
    <p class="lead mb-0">Authentic devotional items selected with care — explore our collection.</p>
  </div>
</section>

<!-- feature cards -->
<section class="container features">
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card feature-card p-3 h-100">
        <div class="d-flex align-items-start gap-3">
          <div class="feature-emoji">🛕</div>
          <div>
            <h5 class="mb-1">Curated Puja Items</h5>
            <p class="mb-0 text-muted">Handpicked for authenticity and quality.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card feature-card p-3 h-100">
        <div class="d-flex align-items-start gap-3">
          <div class="feature-emoji">🎁</div>
          <div>
            <h5 class="mb-1">Gift Ready</h5>
            <p class="mb-0 text-muted">Beautifully packaged for gifting and festivals.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card feature-card p-3 h-100">
        <div class="d-flex align-items-start gap-3">
          <div class="feature-emoji">🚚</div>
          <div>
            <h5 class="mb-1">Fast Delivery</h5>
            <p class="mb-0 text-muted">Reliable shipping and secure packaging.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- footer -->
<footer class="bg-light py-3 mt-4">
  <div class="container text-center text-muted">&copy; <?php echo date('Y'); ?> Shree Swami Samarth</div>
</footer>
</body>
</html>
