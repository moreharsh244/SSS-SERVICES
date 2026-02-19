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
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/pc-theme.css">
  <link rel="stylesheet" href="user.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="../js/bootstrap.bundle.min.js"></script>
  
  <style>
    :root {
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --accent-grad: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
        --glass-bg: rgba(255, 255, 255, 0.75);
        --text-main: #1e293b;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-main);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
        background: linear-gradient(120deg, #667eea 0%, #764ba2 100%); 
        background-attachment: fixed;
    }

    /* --- Glass Navbar --- */
    .glass-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        z-index: 1020; /* High z-index to stay on top */
        padding: 0.8rem 0;
    }

      /* --- BRAND TEXT (Forced !important to block external CSS interference) --- */
      .brand-text {
        font-weight: 700 !important;
        font-size: 2.25rem !important;
        letter-spacing: -0.03em !important;
        background: linear-gradient(to right, #4338ca, #be185d) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: transparent !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
      }

      @media (max-width: 1200px) {
        .brand-text { font-size: 1.8rem !important; }
      }

    /* --- Nav Pills --- */
    .nav-pills-custom {
        display: flex;
        gap: 0.25rem;
      padding: 0.4rem;
        background: #f1f5f9;
      border-radius: 14px;
      overflow-x: auto;
      scrollbar-width: none;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
    }
    .nav-pills-custom::-webkit-scrollbar { display: none; }

    .nav-link-custom {
        display: inline-flex;
        align-items: center;
      padding: 0.6rem 1rem;
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
      border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
      white-space: nowrap;
    }

    .nav-link-custom:hover {
        color: #4f46e5;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .nav-link-custom.active {
        background: var(--primary-grad);
        color: white;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
    }

    /* --- User Profile Pill --- */
    .user-pill {
        display: flex;
        align-items: center;
      gap: 12px;
      padding: 5px 14px 5px 5px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        max-width: 220px; /* Limit width */
    }
    .user-pill:hover { border-color: #4f46e5; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

    .user-name-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px; /* Truncate long emails */
        display: inline-block;
        vertical-align: middle;
    }

    .avatar-circle {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
      font-size: 0.95rem;
        flex-shrink: 0;
      box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3);
    }

    /* --- Sticky Search Bar --- */
    .user-nav-sticky {
        position: sticky;
        top: 80px; 
        z-index: 3000;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .subnav-search-shell {
        max-width: 600px;
        background: transparent;
        backdrop-filter: blur(8px);
        border: none;
        border-radius: 50px;
    }
    .subnav-search-form input {
        border-radius: 50px;
        padding-left: 40px;
        border: none;
        background: rgba(255, 255, 255, 0.9);
    }
    .subnav-search-btn {
        position: absolute;
        right: 5px;
        top: 5px;
        bottom: 5px;
        border-radius: 50px;
        background: var(--primary-grad);
        color: white;
        border: none;
        padding: 0 20px;
        font-weight: 600;
    }
    .subnav-search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }
    
    .search-bar-hide {
      display: none !important;
    }
    
    /* Responsive Helpers */
    @media (max-width: 991px) {
      .glass-header { padding: 0.8rem 0; }
        .nav-pills-custom { overflow-x: auto; width: 100%; justify-content: center; margin-top: 10px; }
        .user-nav-sticky { top: 130px; }
    }
  </style>
</head>
<body>

<header class="glass-header">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between w-100 flex-wrap flex-lg-nowrap">
      
      <a class="d-flex align-items-center gap-3 text-decoration-none me-lg-4" href="view_products.php">
        <img src="../img/logo-mark.svg" alt="Logo" width="48" height="48" onerror="this.style.display='none'">
        <span class="brand-text">Shree Swami Samarth</span>
      </a>

      <button class="navbar-toggler d-lg-none border-0 p-2 bg-light rounded-circle shadow-sm ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
         <i class="bi bi-list fs-4"></i>
      </button>

      <div class="collapse d-lg-flex w-100 align-items-center" id="navContent">
          
          <nav class="nav-pills-custom mx-lg-auto my-3 my-lg-0">
             <a class="nav-link-custom <?php echo in_array($current_page, ['view_products.php', 'purchase.php', 'purchase_order.php']) ? 'active' : ''; ?>" href="view_products.php">
                <i class="bi bi-grid-fill me-2" style="<?php echo in_array($current_page, ['view_products.php', 'purchase.php']) ? 'color:white;' : 'color:#6366f1;'; ?>"></i>Products
             </a>
             <a class="nav-link-custom <?php echo $current_page === 'build.php' ? 'active' : ''; ?>" href="build.php">
                <i class="bi bi-pc-display me-2" style="<?php echo $current_page === 'build.php' ? 'color:white;' : 'color:#10b981;'; ?>"></i>PC Builder
             </a>
             <a class="nav-link-custom <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                <i class="bi bi-person-badge-fill me-2" style="<?php echo $current_page === 'profile.php' ? 'color:white;' : 'color:#f59e0b;'; ?>"></i>Profile
             </a>
             <a class="nav-link-custom <?php echo in_array($current_page, ['service.php', 'service_submit.php']) ? 'active' : ''; ?>" href="service.php">
                <i class="bi bi-tools me-2" style="<?php echo in_array($current_page, ['service.php', 'service_submit.php']) ? 'color:white;' : 'color:#8b5cf6;'; ?>"></i>Support
             </a>
             <a class="nav-link-custom <?php echo in_array($current_page, ['myorder.php', 'myorder_details.php']) ? 'active' : ''; ?>" href="myorder.php">
                <i class="bi bi-bag-check-fill me-2" style="<?php echo in_array($current_page, ['myorder.php']) ? 'color:white;' : 'color:#ec4899;'; ?>"></i>Orders
             </a>
          </nav>

          <div class="d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0 ms-lg-4">
             <div class="dropdown">
                <a class="user-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                   <div class="avatar-circle">
                      <?php echo htmlentities($avatar_initial); ?>
                   </div>
                   <span class="fw-bold text-dark pe-1 user-name-truncate"><?php echo htmlentities($display_name); ?></span>
                   <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                   <li><a class="dropdown-item rounded-3 mb-1" href="profile.php"><i class="bi bi-person-circle me-2 text-primary"></i>My Profile</a></li>
                   <li><a class="dropdown-item rounded-3 mb-1" href="myorder.php"><i class="bi bi-box-seam me-2 text-info"></i>My Orders</a></li>
                   <li><hr class="dropdown-divider my-1"></li>
                   <li><a class="dropdown-item rounded-3 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
             </div>
          </div>

      </div>
    </div>
  </div>
</header>

<div class="container-fluid">
  <div class="row user-nav-sticky justify-content-center">
    <div class="col-11 col-md-8 col-lg-6">
      <div class="subnav-search-shell w-100 mx-auto <?php echo in_array($current_page, ['service.php', 'service_submit.php', 'build.php', 'myorder.php', 'myorder_details.php', 'orderstatus.php', 'profile.php', 'product_details.php']) ? 'search-bar-hide' : ''; ?>">
        <form class="subnav-search-form position-relative" id="site-search-form" action="view_products.php" method="get" autocomplete="off">
          <i class="bi bi-search subnav-search-icon"></i>
          <input id="site-search-input" name="q" class="form-control" type="search" placeholder="Search products, brands, categories..." aria-label="Search products">
          <button class="btn subnav-search-btn" type="submit">Search</button>
          <div id="search-suggestions" class="list-group position-absolute shadow-sm rounded-4 overflow-hidden mt-2" style="z-index:1050; top:100%; left:0; right:0; display:none;"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="container mt-2">
  <div class="row">
    <main class="col-12" id="mainContentArea">
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
            const img = it.pimg ? '<img src="../productimg/'+it.pimg+'" style="height:36px;width:36px;object-fit:cover;margin-right:8px;border-radius:4px;">' : '';
            return `<a href="view_products.php?q=${encodeURIComponent(it.pname)}" class="list-group-item list-group-item-action d-flex align-items-center">
                    <div class="d-flex align-items-center">${img}<div><div>${it.pname}</div><div class="small text-muted">${it.pcompany} • ₹${Number(it.pprice).toFixed(2)}</div></div></div>
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
        const main = document.querySelector('main.col-12');
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
  </div>
</div>
</body>
</html>