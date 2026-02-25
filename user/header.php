<?php
if (session_status() === PHP_SESSION_NONE) {
  session_name('SSS_USER_SESS');
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
// Attempt remember-me auto-login
if(!isset($_SESSION['is_login'])){
  include('../admin/conn.php');
  if(isset($_COOKIE['remember']) && !empty($_COOKIE['remember']) && isset($con) && $con){
    $token = mysqli_real_escape_string($con, $_COOKIE['remember']);
    $rq = mysqli_query($con, "SELECT c_name, c_email, cid, remember_expiry FROM cust_reg WHERE remember_token='$token' LIMIT 1");
    if($rq && mysqli_num_rows($rq)>0){
      $r = mysqli_fetch_assoc($rq);
      if(!empty($r['remember_expiry']) && strtotime($r['remember_expiry']) > time()){
        $_SESSION['is_login'] = true;
        $_SESSION['username'] = $r['c_name'] ?? $r['c_email'];
        $_SESSION['user_id'] = $r['cid'] ?? null;
      } else {
        setcookie('remember','', time()-3600, '/', '', false, true);
      }
    }
  }
}
if(!isset($_SESSION['is_login'])){
  $req = $_SERVER['REQUEST_URI'] ?? '';
  $ret = rawurlencode($req);
  header('Location: login.php?return=' . $ret);
  exit;
}
if(!defined('HEADER_INCLUDED')) define('HEADER_INCLUDED', true);
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$display_name = $_SESSION['username'] ?? 'User';
$avatar_initial = strtoupper(substr(trim((string)$display_name), 0, 1));
if($avatar_initial === ''){ $avatar_initial = 'U'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shree Swami Samarth - Hardware</title>
  
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="user.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="../js/bootstrap.bundle.min.js"></script>
  
  <style>
    :root {
      --brand-dark: #1f2a44;
      --brand-light: #eef6ff;
      --accent-gold: #7c3aed;
      --accent-glow: #a78bfa;
      --text-white: #1f2a44;
      --text-muted: #64748b;
      --bg-body: #eef4ff;
      --surface-1: #f5f3ff;
      --surface-2: #e0f2fe;
      --surface-3: #f0fdf4;
    }

    body {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        color: var(--text-white);
      background:
        radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
        radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
        radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.10) 0%, rgba(16, 185, 129, 0) 30%),
        linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* =========================================
       TIER 1: Top Brand Header (Dark)
       ========================================= */
    .top-brand-header {
        background: linear-gradient(90deg, rgba(245, 243, 255, 0.95) 0%, rgba(224, 242, 254, 0.95) 55%, rgba(240, 253, 244, 0.95) 100%);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(186, 230, 253, 0.5);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.06);
        position: sticky;
        top: 0;
        z-index: 1020;
        padding: 1rem 0;
    }

    .brand-text {
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Poppins', sans-serif;
        font-weight: 900;
        font-size: 2.25rem;
        letter-spacing: -0.03em;
        background: linear-gradient(to right, #4338ca, #be185d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-decoration: none;
        white-space: nowrap;
        line-height: 1.2;
    }
    
    .brand-text span {
        background: linear-gradient(to right, #4338ca, #be185d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    @media (max-width: 1200px) {
        .brand-text { font-size: 1.8rem; }
    }
    
    .brand-accent {
        background: linear-gradient(to right, #be185d, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* User Profile Pill */
    .user-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 16px 6px 6px;
        background: rgba(255,255,255,0.8);
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: var(--text-white);
    }
    
    .user-pill:hover {
      background: rgba(255,255,255,0.98);
      border-color: #93c5fd;
        color: var(--text-white);
    }

    .avatar-circle {
        width: 34px;
        height: 34px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-name-text {
        font-weight: 500;
        font-size: 0.95rem;
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Dark Dropdown */
    .dropdown-menu-dark-custom {
      background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%);
      border: 1px solid rgba(191,219,254,0.9);
      box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12);
        border-radius: 12px;
        margin-top: 10px !important;
    }
    .dropdown-menu-dark-custom .dropdown-item {
      color: #334155;
        font-weight: 500;
        padding: 8px 20px;
        transition: 0.2s;
    }
    .dropdown-menu-dark-custom .dropdown-item:hover {
      background-color: rgba(191,219,254,0.3);
      color: #1d4ed8;
    }
    .dropdown-menu-dark-custom .dropdown-divider {
      border-color: rgba(191,219,254,0.7);
    }

    /* Ensure dropdown appears above navbar */
    .dropdown-menu-dark-custom {
      z-index: 99999 !important;
      position: absolute !important;
      bottom: 100%;
      top: auto !important;
      margin-bottom: 10px !important;
      margin-top: 0 !important;
      left: 0 !important;
      right: auto !important;
      min-width: 220px;
      /* Make sure it is not clipped by parent */
      transform: translateY(-10px);
      box-shadow: 0 18px 40px rgba(30, 64, 175, 0.18);
    }

    /* Also target .dropdown-menu.show for extra reliability */
    .dropdown-menu.show.dropdown-menu-dark-custom {
      z-index: 99999 !important;
      position: absolute !important;
      bottom: 100%;
      top: auto !important;
      margin-bottom: 10px !important;
      margin-top: 0 !important;
      left: 0 !important;
      right: auto !important;
      min-width: 220px;
      transform: translateY(-10px);
      box-shadow: 0 18px 40px rgba(30, 64, 175, 0.18);
    }
    }

    /* Ensure dropdown menu always appears above everything when open */
    .dropdown-menu.show {
      z-index: 99999 !important;
      position: absolute !important;
      margin-top: 12px !important;
    }

    /* Prevent parent containers from clipping the dropdown */
    header, .top-brand-header, .secondary-navbar, .nav-container {
      overflow: visible !important;
    }

    /* Ensure parent .dropdown has high z-index stacking context */
    .dropdown {
      position: relative;
      z-index: 9998;
    }


    /* =========================================
       TIER 2: Navigation Bar (Light/White)
       ========================================= */
    .secondary-navbar {
      background: linear-gradient(90deg, rgba(245,243,255,0.92) 0%, rgba(238,246,255,0.92) 100%);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(186,230,253,0.9);
      box-shadow: 0 6px 18px rgba(30, 64, 175, 0.08);
      position: sticky;
      top: 0;
      z-index: 1019;
      padding: 0; /* Removing padding so links hit edges */
    }

    .nav-container {
        display: flex;
        align-items: center;
        justify-content: center; /* Center the links */
        overflow-x: auto;
        scrollbar-width: none;
    }
    .nav-container::-webkit-scrollbar { display: none; }

    .nav-link-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 16px 24px;
        color: #334155;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .nav-link-item i {
        font-size: 1.1rem;
      color: #64748b;
        transition: 0.3s;
    }

    .nav-link-item:hover {
        color: var(--brand-dark);
      background: #eef6ff;
    }
    
    .nav-link-item:hover i {
      color: #0284c7;
    }

    .nav-link-item.active {
        color: var(--brand-dark);
      border-bottom-color: #7c3aed;
      background: #f5f3ff;
    }
    .nav-link-item.active i {
      color: #7c3aed;
    }


    /* =========================================
       TIER 3: Floating Search Bar
       ========================================= */
    .search-wrapper {
        margin-top: 20px;
        margin-bottom: 20px;
        position: relative;
        z-index: 1010;
    }

    .search-form {
        position: relative;
        max-width: 700px;
        margin: 0 auto;
        box-shadow: 0 10px 25px rgba(30, 64, 175, 0.1);
        border-radius: 50px;
    }

    .search-input {
        width: 100%;
        height: 55px;
        border: 2px solid #dbeafe;
        border-radius: 50px;
        padding: 0 130px 0 50px;
        font-size: 1rem;
        color: var(--brand-dark);
        background: #f8fbff;
        transition: all 0.3s;
    }
    
    .search-input:focus {
        outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.16);
    }

    .search-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .search-btn {
        position: absolute;
        right: 6px;
        top: 6px;
        bottom: 6px;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 0 30px;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: 0.2s;
    }

    .search-btn:hover {
      background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
      color: #ffffff;
    }

    .search-hide { display: none !important; }

    /* Responsive */
    @media (max-width: 768px) {
        .brand-text { font-size: 1.5rem; }
        .nav-link-item { padding: 12px 16px; font-size: 0.85rem; }
        .search-wrapper { padding: 0 15px; }
    }
  </style>
</head>
<body>

<header class="top-brand-header">
  <div class="container-fluid px-lg-5 px-3">
    <div class="d-flex align-items-center justify-content-between">
      
      <a href="view_products.php" class="brand-text">
        <img src="../img/logo-mark.svg" alt="Logo" width="38" height="38" onerror="this.style.display='none'">
        <span>Shree Swami <span class="brand-accent">Samarth</span></span>
      </a>

      <div class="dropup">
          <a class="user-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar-circle">
              <?php echo htmlentities($avatar_initial); ?>
            </div>
            <span class="user-name-text d-none d-sm-inline-block"><?php echo htmlentities($display_name); ?></span>
            <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom" style="background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%); border: 1px solid rgba(191,219,254,0.9); box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12); border-radius: 12px; margin-top: 10px;">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2 text-warning"></i> My Profile</a></li>
            <li><a class="dropdown-item" href="myorder.php"><i class="bi bi-box-seam me-2 text-info"></i> My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
          </ul>

    </div>
  </div>
</header>

<nav class="secondary-navbar">
    <div class="container-fluid">
        <div class="nav-container">
            <a class="nav-link-item <?php echo in_array($current_page, ['view_products.php', 'purchase.php', 'purchase_order.php']) ? 'active' : ''; ?>" href="view_products.php">
                <i class="bi bi-grid-fill"></i> Store
            </a>
            <a class="nav-link-item <?php echo $current_page === 'build.php' ? 'active' : ''; ?>" href="build.php">
                <i class="bi bi-pc-display"></i> PC Builder
            </a>
            <a class="nav-link-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                <i class="bi bi-person-badge-fill"></i> Account
            </a>
            <a class="nav-link-item <?php echo in_array($current_page, ['service.php', 'service_submit.php']) ? 'active' : ''; ?>" href="service.php">
                <i class="bi bi-tools"></i> Support
            </a>
            <a class="nav-link-item <?php echo in_array($current_page, ['myorder.php', 'myorder_details.php']) ? 'active' : ''; ?>" href="myorder.php">
                <i class="bi bi-bag-check-fill"></i> Orders
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid search-wrapper <?php echo in_array($current_page, ['service.php', 'service_submit.php', 'build.php', 'myorder.php', 'myorder_details.php', 'orderstatus.php', 'profile.php', 'product_details.php']) ? 'search-hide' : ''; ?>">
    <form class="search-form" id="site-search-form" action="view_products.php" method="get" autocomplete="off">
        <i class="bi bi-search search-icon"></i>
        <input id="site-search-input" name="q" class="search-input" type="search" placeholder="Search for graphics cards, processors, cases...">
        <button class="search-btn" type="submit">SEARCH</button>
        
        <div id="search-suggestions" class="list-group position-absolute w-100 shadow-lg rounded-4 overflow-hidden mt-2" style="z-index:1050; top:100%; left:0; display:none; border: none;"></div>
    </form>
</div>

<main class="container mt-4" id="mainContentArea">
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      // Search suggestion logic
      const input = document.getElementById('site-search-input');
      const suggBox = document.getElementById('search-suggestions');
      let timer = null;

      function hideSuggestions(){ suggBox.style.display='none'; suggBox.innerHTML=''; }

      function renderSuggestions(items){
        if(!items.length){ hideSuggestions(); return; }
        suggBox.innerHTML = items.map(it=>{
          const img = it.pimg ? `<img src="../productimg/${it.pimg}" style="height:40px;width:40px;object-fit:contain;background:#f8fafc;border-radius:8px;padding:2px;margin-right:12px;">` : '';
          return `<a href="view_products.php?q=${encodeURIComponent(it.pname)}" class="list-group-item list-group-item-action d-flex align-items-center py-3" style="border-bottom: 1px solid #f1f5f9;">
                  <div class="d-flex align-items-center w-100">${img}
                    <div>
                        <div class="fw-bold text-dark" style="font-size:0.95rem;">${it.pname}</div>
                        <div class="small text-muted">${it.pcompany} • <span class="fw-bold" style="color:var(--accent-gold);">₹${Number(it.pprice).toFixed(2)}</span></div>
                    </div>
                  </div>
                </a>`;
        }).join('');
        suggBox.style.display = 'block';
      }

      if(input){
          input.addEventListener('input', function(){
            const v = this.value.trim();
            clearTimeout(timer);
            if(!v){ hideSuggestions(); return; }
            timer = setTimeout(()=>{
              fetch('search_suggest.php?q='+encodeURIComponent(v))
                .then(r=>r.json())
                .then(renderSuggestions)
                .catch(()=>hideSuggestions());
            }, 220);
          });
      }

      document.addEventListener('click', function(e){
        if(document.getElementById('site-search-form') && !document.getElementById('site-search-form').contains(e.target)) hideSuggestions();
      });

      // PC Builder SPA logic
      const main = document.querySelector('main#mainContentArea');
      const enableBuildSpa = false;
      const buildHref = 'build.php';
      const buildFetchUrl = 'build.php?partial=1';

      function loadBuildFragment(){
        fetch(buildFetchUrl)
          .then(r => r.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const frag = doc.getElementById('buildPageRoot');
            const modal = doc.getElementById('productSelectorModal');
            if(frag && main){
              main.innerHTML = '';
              main.appendChild(frag);
            }
            if(modal){
              const existing = document.getElementById('productSelectorModal');
              if(existing) existing.remove();
              document.body.appendChild(modal);
            }
            const scripts = doc.querySelectorAll('script');
            scripts.forEach(s => {
              if(!s.src){
                try{ eval(s.textContent); }catch(e){ console.error(e); }
              } else if(!document.querySelector('script[src="'+s.src+'"]')){
                const sc = document.createElement('script');
                sc.src = s.src;
                document.body.appendChild(sc);
              }
            });
          }).catch(console.error);
      }

      function pushBuildState(){
        history.pushState({ page: 'build' }, '', buildHref);
      }

      if(enableBuildSpa){
        if(!history.state){
          history.replaceState({ page: 'page', url: location.href }, '', location.href);
        }

        document.body.addEventListener('click', function(e){
          const link = e.target.closest('a[href="build.php"]');
          if(!link) return;
          if(link.target && link.target !== '_self') return;
          e.preventDefault();
          pushBuildState();
          loadBuildFragment();
        });

        window.addEventListener('popstate', function(e){
          if(e.state && e.state.page === 'build'){
            loadBuildFragment();
          } else {
            location.reload();
          }
        });
      }
    });
    </script>
</main>

</body>
</html>