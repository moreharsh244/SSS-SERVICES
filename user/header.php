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
    <a class="navbar-brand fw-bold" href="view_products.php" style="font-size: 30px;">Shree Swami Samarth</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

  
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
<!-- Page layout -->
<div class="container mt-3">
  <div class="row">
    <div class="col-12 d-flex justify-content-center">
      <nav class="nav nav-pills justify-content-center flex-wrap gap-2 bg-white rounded shadow-sm p-2">
        <a class="nav-link" href="view_products.php"><i class="bi bi-card-list me-2"></i>Browse Products</a>
        <a class="nav-link" href="build.php"><i class="bi bi-hammer me-2"></i>Build PC</a>
        <a class="nav-link" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a>
        <a class="nav-link" href="myorder.php"><i class="bi bi-bag me-2"></i>My Orders</a>
        <a class="nav-link" href="orderstatus.php"><i class="bi bi-truck me-2"></i>Order Status</a>
      </nav>
    </div>
  </div>
  <div class="row mt-4">
    <main class="col-12">

<!-- Image preview modal (user) -->
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
<script>
// Intercept Build PC nav link and load build UI under navbar without full redirect
document.addEventListener('DOMContentLoaded', function(){
  function loadBuildFragment(){
    fetch('build.php')
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const frag = doc.querySelector('.container.py-4');
        const modal = doc.getElementById('imageModalBuild');
        const main = document.querySelector('main.col-12');
        if(frag && main){
          main.innerHTML = '';
          main.appendChild(frag);
        }
        // append modal if present
        if(modal){
          // remove existing modal if any
          const existing = document.getElementById('imageModalBuild');
          if(existing) existing.remove();
          document.body.appendChild(modal);
        }
        // execute inline scripts from fetched page
        const scripts = doc.querySelectorAll('script');
        scripts.forEach(s => {
          if(!s.src){
            try{ eval(s.textContent); }catch(e){ console.error(e); }
          } else {
            // ensure external scripts are loaded
            if(!document.querySelector('script[src="'+s.src+'"]')){
              const sc = document.createElement('script'); sc.src = s.src; document.body.appendChild(sc);
            }
          }
        });
      }).catch(console.error);
  }

  // attach handler to Build PC nav link(s)
  document.querySelectorAll('a.nav-link[href="build.php"]').forEach(a => {
    a.addEventListener('click', function(e){ e.preventDefault(); loadBuildFragment(); });
  });
});
</script>
