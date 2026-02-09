<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shree Swami Samarth</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/pc-theme.css">
    <script src="js/bootstrap.min.js"></script>
</head>
<body class="pc-theme">
   <!-- navbar start  -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" style="width:100%">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="img/logo-mark.svg" alt="Shree Swami Samarth" style="height:36px;width:36px;margin-right:10px;">
      <span>Shree Swami Samarth</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="font-size: 20px;">

      <div class="mx-auto d-flex align-items-center nav-auth">
        <a class="btn btn-outline-primary me-3 d-none d-lg-inline-flex align-items-center" href="admin/index.php"><i class="bi bi-shield-lock me-2"></i>Admin Portal</a>
        <div class="btn-group">
          <button type="button" class="btn btn-primary dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person me-2"></i>Customer Portal</button>
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

    <!-- hero start -->
<section class="hero-splash reveal">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <span class="hero-chip"><i class="bi bi-stars"></i> Devotional gifts made joyful</span>
        <h1 class="hero-title">Shree Swami Samarth
          <span class="hero-title-sub">Colorful collections for puja, festivals, and gifting</span>
        </h1>
        <p class="hero-copy">Pick from hand-selected essentials, festival kits, and gift-ready bundles delivered with care.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="btn btn-hero-primary btn-lg" href="user/index.php">Shop Now</a>
          <a class="btn btn-hero-outline btn-lg" href="view_products.php">See Collection</a>
        </div>
        <div class="hero-highlights">
          <div class="highlight-pill">Fast delivery</div>
          <div class="highlight-pill">Gift-ready packs</div>
          <div class="highlight-pill">Authentic items</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-collage">
          <div class="hero-card">
            <img src="img/pc1.jpg" alt="Puja essentials" class="img-fluid">
          </div>
          <div class="hero-card pop">
            <img src="img/pc2.jpg" alt="Festival kits" class="img-fluid">
          </div>
          <div class="hero-card tall">
            <img src="img/pc3.jpg" alt="Gift packs" class="img-fluid">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
    <!-- hero end -->

    <!-- info strip -->
<section class="info-strip reveal">
  <div class="container">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="info-card">
          <div class="info-icon"><i class="bi bi-flower3"></i></div>
          <div>
            <h5>Curated Puja Items</h5>
            <p>Authenticity-first products for everyday rituals.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-card">
          <div class="info-icon"><i class="bi bi-gift"></i></div>
          <div>
            <h5>Gift-Ready Bundles</h5>
            <p>Festival and gifting packs styled for joy.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-card">
          <div class="info-icon"><i class="bi bi-truck"></i></div>
          <div>
            <h5>Reliable Shipping</h5>
            <p>Fast delivery with secure packaging.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- categories -->
<section class="category-grid reveal">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="section-title mb-0">Popular Categories</h2>
      <a class="text-decoration-none fw-semibold" href="view_products.php">View all</a>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="category-tile teal">
          <div class="tile-top">Daily Puja</div>
          <p>Diya, incense, and essentials.</p>
          <a href="view_products.php" class="btn btn-light btn-sm">Explore</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="category-tile coral">
          <div class="tile-top">Festival Kits</div>
          <p>Ready-to-use celebration packs.</p>
          <a href="view_products.php" class="btn btn-light btn-sm">Explore</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="category-tile amber">
          <div class="tile-top">Gift Sets</div>
          <p>Thoughtful presents for loved ones.</p>
          <a href="view_products.php" class="btn btn-light btn-sm">Explore</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="category-tile violet">
          <div class="tile-top">Home Decor</div>
          <p>Spiritual decor and accents.</p>
          <a href="view_products.php" class="btn btn-light btn-sm">Explore</a>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- CTA band -->
<section class="cta-ribbon reveal">
  <div class="container">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <h3 class="mb-2">Track your orders in seconds</h3>
        <p class="mb-0">Sign in to the customer portal for updates and history.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a class="btn btn-dark btn-lg" href="user/index.php">Customer Portal</a>
      </div>
    </div>
  </div>
</section>

    <!-- footer -->
<footer class="bg-light py-3 mt-4">
  <div class="container text-center text-muted">&copy; <?php echo date('Y'); ?> Shree Swami Samarth</div>
</footer>
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="globalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Shree Swami Samarth</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body"></div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('is-visible'); });
  }

  var params = new URLSearchParams(window.location.search);
  var msg = params.get('toast');
  if(msg){
    var toastEl = document.getElementById('globalToast');
    toastEl.querySelector('.toast-body').textContent = msg;
    var t = new bootstrap.Toast(toastEl);
    t.show();
  }
});
</script>
</body>
</html>
