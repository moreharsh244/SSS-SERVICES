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
    <title>Shree Swami Samarth - Hardware</title>
    
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary-color: #0ea5e9;
            --card-bg: #f8fbff;
            --text-dark: #1f2a44;
            --text-muted: #64748b;
            --surface-soft: #eef6ff;
            --surface-border: #dbeafe;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
                radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
                radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
                linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
        }

        /* --- TOP BRAND HEADER --- */
        .glass-header {
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

        /* --- SECONDARY NAVBAR --- */
        .secondary-navbar {
            background: linear-gradient(90deg, rgba(245, 243, 255, 0.92) 0%, rgba(238, 246, 255, 0.92) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(186, 230, 253, 0.9);
            box-shadow: 0 6px 18px rgba(30, 64, 175, 0.08);
            position: sticky;
            top: 0;
            z-index: 1019;
            padding: 0;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .nav-container::-webkit-scrollbar { display: none; }

        /* --- BRAND TEXT --- */
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
            white-space: nowrap; 
            line-height: 1.2;
            text-decoration: none;
        }

        .brand-text span {
            background: linear-gradient(to right, #4338ca, #be185d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-accent {
            background: linear-gradient(to right, #be185d, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 1200px) {
            .brand-text { font-size: 1.8rem; } 
        }

        /* --- NAV LINK ITEMS --- */
        .nav-link-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 24px;
            color: #334155;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            position: relative;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .nav-link-item i {
            font-size: 1.1rem;
            color: #64748b;
            transition: 0.3s;
        }

        .nav-link-item:hover {
            color: var(--primary-color);
            background: #eef6ff;
        }

        .nav-link-item:hover i {
            color: #0284c7;
        }

        .nav-link-item.active {
            color: var(--primary-color);
            border-bottom-color: #7c3aed;
            background: #f5f3ff;
        }

        .nav-link-item.active i {
            color: var(--primary-color);
        }

        /* --- USER PILL --- */
        .user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 5px 14px 5px 5px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(186, 230, 253, 0.6);
            border-radius: 50px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            color: inherit;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            margin: 0;
            box-sizing: border-box;
        }
        .user-pill:hover { border-color: #93c5fd; box-shadow: 0 2px 5px rgba(14, 165, 233, 0.15); color: inherit; }
        .user-pill:focus { outline: none; border-color: #93c5fd; box-shadow: 0 2px 5px rgba(14, 165, 233, 0.15); }

        .user-name-text {
            font-weight: 600;
            color: var(--text-dark);
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 2px 5px rgba(124, 58, 237, 0.3);
        }

        /* --- Dropdowns & Modals --- */
        .dropdown-menu {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .dropdown-item {
            color: var(--text-dark);
            padding: 0.6rem 1rem;
            border-radius: 6px !important;
        }
        
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: rgba(124, 58, 237, 0.08);
            color: var(--primary-color);
        }

        /* Responsive Helpers */
        @media (max-width: 991px) {
            .glass-header { padding: 0.8rem 0; }
            .nav-pills-custom { overflow-x: auto; width: 100%; justify-content: center; margin-top: 10px; }
        }
    </style>
</head>  
<body>

<header class="glass-header">
  <div class="container-fluid px-lg-5 px-3">
    <div class="d-flex align-items-center justify-content-between">
      
      <a href="index.php" class="brand-text">
        <img src="../img/logo-mark.svg" alt="Logo" width="38" height="38" onerror="this.style.display='none'">
        <span>Shree Swami <span class="brand-accent">Samarth</span></span>
      </a>

      <div class="dropdown">
        <a class="user-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
           <div class="avatar-circle">
              <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
           </div>
           <span class="user-name-text d-none d-sm-inline-block"><?php echo htmlentities($_SESSION['username']); ?></span>
           <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" style="background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%); border: 1px solid rgba(191,219,254,0.9); box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12); border-radius: 12px; margin-top: 10px;">
           <li><a class="dropdown-item" href="index.php"><i class="bi bi-truck-front-fill me-2 text-danger"></i> Deliveries</a></li>
           <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-badge-fill me-2 text-warning"></i> Profile</a></li>
           <li><a class="dropdown-item" href="audit_log.php"><i class="bi bi-clock-history me-2 text-primary"></i> Audit Log</a></li>
           <li><hr class="dropdown-divider" style="border-color: rgba(191,219,254,0.7);"></li>
           <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
        </ul>
      </div>

    </div>
  </div>
</header>


<?php
$pending_builds = 0;
$pending_services = 0;
include_once '../admin/conn.php';
$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');
$pending_builds_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM builds WHERE assigned_agent='$agent' AND (status IS NULL OR status IN ('pending','out_for_delivery'))");
if($pending_builds_res && mysqli_num_rows($pending_builds_res)>0){ $pending_builds = (int)mysqli_fetch_assoc($pending_builds_res)['cnt']; }
$pending_services_res = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM service_requests WHERE assigned_agent='$agent' AND status IN ('pending','in_progress')");
if($pending_services_res && mysqli_num_rows($pending_services_res)>0){ $pending_services = (int)mysqli_fetch_assoc($pending_services_res)['cnt']; }
$pending_total = $pending_builds + $pending_services;
?>
<nav class="secondary-navbar">
    <div class="container-fluid">
        <div class="nav-container">
                        <a class="nav-link-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
                                <i class="bi bi-truck-front-fill"></i> Deliveries
                        </a>
            <a class="nav-link-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                <i class="bi bi-person-badge-fill"></i> Profile
            </a>
            <a class="nav-link-item <?php echo basename($_SERVER['PHP_SELF']) === 'audit_log.php' ? 'active' : ''; ?>" href="audit_log.php">
                <i class="bi bi-clock-history"></i> Audit Log
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">