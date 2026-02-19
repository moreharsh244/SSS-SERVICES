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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-grad: linear-gradient(135deg, #f43f5e 0%, #ec4899 100%);
            --accent-grad: linear-gradient(135deg, #fb7185 0%, #fda4af 100%);
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

        /* --- BRAND TEXT --- */
        .brand-text {
            font-weight: 900;
            font-size: 2.25rem;
            letter-spacing: -0.03em;
            background: linear-gradient(to right, #4338ca, #be185d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap; 
            line-height: 1.2;
        }

        @media (max-width: 1200px) {
            .brand-text { font-size: 1.8rem; } 
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
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
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
            color: inherit;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            margin: 0;
            box-sizing: border-box;
        }
        .user-pill:hover { border-color: #4f46e5; box-shadow: 0 2px 5px rgba(0,0,0,0.05); color: inherit; }
        .user-pill:focus { outline: none; border-color: #4f46e5; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

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

        /* --- Dropdown Menu --- */
        .dropdown-menu {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: none;
        }

        .dropdown-item {
            font-weight: 500;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: #f8fafc;
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
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between w-100 flex-wrap flex-lg-nowrap">
            
            <a class="d-flex align-items-center gap-3 text-decoration-none me-lg-4" href="index.php">
                <img src="../img/logo-mark.svg" alt="Logo" width="48" height="48" onerror="this.style.display='none'">
                <span class="brand-text">Shree Swami Samarth</span>
            </a>

            <button class="navbar-toggler d-lg-none border-0 p-2 bg-light rounded-circle shadow-sm ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse d-lg-flex w-100 align-items-center" id="navContent">
                
                <nav class="nav-pills-custom mx-lg-auto my-3 my-lg-0">
                    <a class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-truck-front-fill me-2" style="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'color:white;' : 'color:#f43f5e;'; ?>"></i>Deliveries
                    </a>
                    <a class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                        <i class="bi bi-person-badge-fill me-2" style="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'color:white;' : 'color:#f59e0b;'; ?>"></i>Profile
                    </a>
                    <a class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) === 'audit_log.php' ? 'active' : ''; ?>" href="audit_log.php">
                        <i class="bi bi-clock-history me-2" style="<?php echo basename($_SERVER['PHP_SELF']) === 'audit_log.php' ? 'color:white;' : 'color:#8b5cf6;'; ?>"></i>Audit Log
                    </a>
                </nav>

                <div class="d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0 ms-lg-4">
                    <div class="dropdown">
                        <a class="user-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            <span class="fw-bold text-dark pe-1"><?php echo htmlentities($_SESSION['username']); ?></span>
                            <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                            <li><a class="dropdown-item rounded-3 mb-1" href="index.php"><i class="bi bi-truck-front-fill me-2 text-danger"></i>Deliveries</a></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="profile.php"><i class="bi bi-person-circle me-2 text-warning"></i>My Profile</a></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="audit_log.php"><i class="bi bi-clock-history me-2 text-primary"></i>Audit Log</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item rounded-3 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">