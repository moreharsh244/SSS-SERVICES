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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/pc-theme.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <style>
      :root {
        --brand-dark: #1f2a44;
        --text-muted: #64748b;
        --accent-main: #7c3aed;
        --accent-blue: #0ea5e9;
      }

      body.pc-theme {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        color: var(--brand-dark);
        min-height: 100vh;
        overflow-x: hidden;
        background:
          radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
          radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
          radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.10) 0%, rgba(16, 185, 129, 0) 30%),
          linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
      }

      .navbar.navbar-light {
        background: linear-gradient(90deg, rgba(245, 243, 255, 0.95) 0%, rgba(224, 242, 254, 0.95) 55%, rgba(240, 253, 244, 0.95) 100%) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(186, 230, 253, 0.5);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.06) !important;
        padding: 1rem 0;
      }

      .navbar .container-fluid {
        padding-left: 3rem;
        padding-right: 3rem;
      }

      .navbar-light .navbar-toggler {
        border-color: rgba(147, 197, 253, 0.9);
      }

      .navbar .btn-primary {
        border: none;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: #fff;
      }

      .navbar .btn-primary:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
      }

      .navbar .btn-outline-primary {
        border-color: #7c3aed;
        color: #5b21b6;
        background: rgba(255, 255, 255, 0.7);
      }

      .navbar .btn-outline-primary:hover {
        border-color: #7c3aed;
        background: #f5f3ff;
        color: #4c1d95;
      }

      .dropdown-menu {
        background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%);
        border: 1px solid rgba(191,219,254,0.9);
        box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12);
        border-radius: 12px;
      }

      .dropdown-menu .dropdown-item {
        color: #334155;
        font-weight: 500;
      }

      .dropdown-menu .dropdown-item:hover {
        background-color: rgba(191,219,254,0.3);
        color: #1d4ed8;
      }

      .navbar-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        margin: 0;
      }

      .brand-logo {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
      }

      .brand-text-main {
        font-family: 'Poppins', sans-serif;
        font-weight: 900;
        font-size: 2.25rem;
        letter-spacing: -0.03em;
        line-height: 1.2;
        background: linear-gradient(to right, #4338ca, #be185d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        white-space: nowrap;
      }

      .brand-accent {
        background: linear-gradient(to right, #be185d, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      @media (max-width: 1200px) {
        .brand-text-main { font-size: 1.8rem; }
      }

      @media (max-width: 991px) {
        .navbar .container-fluid {
          padding-left: 1rem;
          padding-right: 1rem;
        }
      }
    </style>
</head>
<body class="pc-theme">
   <!-- navbar start  -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" style="width:100%">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="img/logo-mark.svg" alt="Shree Swami Samarth" class="brand-logo">
      <span class="brand-text-main">Shree Swami <span class="brand-accent">Samarth</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="font-size: 20px;">

      <div class="mx-auto d-flex align-items-center nav-auth">
        <a class="btn btn-outline-primary me-3 d-none d-lg-inline-flex align-items-center" href="admin/login.php"><i class="bi bi-shield-lock me-2"></i>Admin Portal</a>
        <div class="btn-group">
          <button type="button" class="btn btn-primary dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person me-2"></i>Customer Portal</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="user/login.php">Sign In</a></li>
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
          <span class="hero-title-sub">Where Tools and Service Meet Excellence</span>
        </h1>

        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="btn btn-hero-primary btn-lg" href="user/index.php">Shop Now</a>
          <a class="btn btn-hero-outline btn-lg" href="user/view_products.php">See Collection</a>
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
            <img src="https://images.unsplash.com/photo-1587202372634-32705e3bf49c?w=600&h=400&fit=crop" alt="Gaming PC Setup" class="img-fluid">
          </div>
          <div class="hero-card pop">
            <img src="https://images.unsplash.com/photo-1591799265444-d66432b91588?w=600&h=400&fit=crop" alt="Custom PC Build" class="img-fluid">
          </div>
          <div class="hero-card tall">
            <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?w=400&h=600&fit=crop" alt="PC Components" class="img-fluid">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
    <!-- hero end -->

  

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
