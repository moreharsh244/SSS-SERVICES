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
    <style>
      /* ensure navbar and dropdowns render above carousel images */
      .navbar { position: relative; z-index: 4000; }
      .navbar .dropdown-menu { z-index: 4001; }
      /* visual tweaks */
      .navbar-brand{ font-weight:700; font-size:1.6rem; color:#0d6efd; }
      .nav-auth .btn{ border-radius:8px; padding:0.45rem 0.9rem; }
      .hero-caption{ box-shadow:0 8px 30px rgba(13,110,253,0.08); }
    </style>
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

      <div class="mx-auto d-flex align-items-center nav-auth">
        <a class="btn btn-outline-primary me-3 d-none d-lg-inline-flex align-items-center" href="admin/index.php">Admin Portal</a>
        <div class="btn-group">
          <button type="button" class="btn btn-primary dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">Customer Portal</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="user/index.php">Sign In</a></li>
            <li><a class="dropdown-item" href="user/register.php">Sign Up</a></li>
          </ul>
        </div>
      </div>
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
