<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
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
if (!isset($_SESSION['is_login'])) {
    header('location:login.php');
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}
include_once('conn.php');

// Handle notification actions
if(isset($_GET['delete_notif'])){
    $id = preg_replace('/[^a-zA-Z0-9_\.-]/', '', (string)$_GET['delete_notif']);
    if($id !== ''){
        delete_admin_notification($id);
    }
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
}
if(isset($_GET['clear_notifs']) && $_GET['clear_notifs'] === '1'){
    clear_admin_notifications();
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$low_stock = [];
if(isset($con)){
    $lsq = "SELECT pid, pname, pcompany, pqty FROM products WHERE pqty <= 5 ORDER BY pqty ASC, pname ASC";
    $lsr = mysqli_query($con, $lsq);
    if($lsr){
        while($r = mysqli_fetch_assoc($lsr)){
            $low_stock[] = $r;
        }
    }
}

// Check if we should auto-show the low stock modal on this page load
$show_low_stock_modal = false;
if(!empty($low_stock)){
    if(!isset($_SESSION['low_stock_shown_at'])){
        // Show modal on first page load when low stock exists
        $show_low_stock_modal = true;
        $_SESSION['low_stock_shown_at'] = time();
    } else {
        // Re-show modal if it's been more than 2 hours since last shown
        $time_since_shown = time() - $_SESSION['low_stock_shown_at'];
        if($time_since_shown > 7200){ // 2 hours
            $show_low_stock_modal = true;
            $_SESSION['low_stock_shown_at'] = time();
        }
    }
}

// Get admin notifications
$unread_count = 0;
$recent_notifications = [];
if(isset($con)){
    $unread_count = get_unread_notifications_count();
    $recent_notifications = get_recent_notifications(5);
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shree Swami Samarth - Hardware</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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
            /* Static, Attractive Gradient Background */
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
            background: var(--primary-grad);
            color: white;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        /* --- Icons & User --- */
        .icon-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
            transition: 0.2s;
            position: relative;
            text-decoration: none;
            cursor: pointer;
            padding: 0;
        }
        .icon-btn:hover { color: #4f46e5; border-color: #4f46e5; background: #f8fafc; }

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
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3);
        }
        
        .notification-dot {
            position: absolute;
            top: 10px;
            right: 11px;
            width: 9px;
            height: 9px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* --- Dropdowns & Modals --- */
        .dropdown-menu {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .dropdown-item {
            color: #1f2937;
            padding: 0.6rem 1rem;
            border-radius: 6px !important;
        }
        
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f3f4f6;
            color: #4f46e5;
        }
        
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

    </style>
</head>
<body>

<?php if(!empty($low_stock)){ ?>
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden">
            <div class="modal-header border-0 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm text-danger" 
                         style="width:40px; height:40px;">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Inventory Stock Alert
                        </h5>
                        <p class="mb-0 small text-muted mt-1">Critical stock levels detected - Action Required</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="ps-4 text-muted small fw-bold py-3">Product</th>
                            <th class="text-muted small fw-bold py-3">Company</th>
                            <th class="text-center text-muted small fw-bold py-3">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($low_stock as $ls){ ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($ls['pname']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($ls['pcompany']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">
                                        <?php echo (int)$ls['pqty']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Close</button>
                <a href="view_product.php" class="btn btn-primary rounded-3 px-4">Restock</a>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent shadow-none">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="modalImage" src="" alt="Preview" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<header class="glass-header">
    <div class="container-fluid px-4">
        <div class="row align-items-center gy-3">
            
            <div class="col-12 col-lg-auto d-flex align-items-center justify-content-between">
                <a href="products_card.php" class="d-flex align-items-center gap-3 text-decoration-none">
                    <img src="../img/logo-mark.svg" alt="Logo" width="48" height="48" onerror="this.style.display='none'">
                    <span class="brand-text">Shree Swami Samarth</span>
                </a>
                
                <button class="navbar-toggler d-lg-none border-0 p-2 bg-light rounded-circle shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="col-12 col-lg">
                <div class="collapse d-lg-flex justify-content-between align-items-center" id="navContent">
                    
                    <nav class="nav-pills-custom my-3 my-lg-0 mx-lg-auto">
                        <a class="nav-link-custom <?php echo ($current_page == 'products_card.php') ? 'active' : ''; ?>" href="products_card.php">
                            <i class="bi bi-grid-fill" style="color: <?php echo ($current_page == 'products_card.php') ? 'white' : '#6366f1'; ?>"></i> Grid
                        </a>
                        <a class="nav-link-custom <?php echo ($current_page == 'view_product.php') ? 'active' : ''; ?>" href="view_product.php">
                            <i class="bi bi-box-seam-fill" style="color: <?php echo ($current_page == 'view_product.php') ? 'white' : '#f59e0b'; ?>"></i> Inventory
                        </a>
                        <a class="nav-link-custom <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-graph-up-arrow" style="color: <?php echo ($current_page == 'index.php') ? 'white' : '#10b981'; ?>"></i> Analytics
                        </a>
                        <a class="nav-link-custom <?php echo ($current_page == 'orders_list.php') ? 'active' : ''; ?>" href="orders_list.php">
                            <i class="bi bi-bag-check-fill" style="color: <?php echo ($current_page == 'orders_list.php') ? 'white' : '#ec4899'; ?>"></i> Orders
                        </a>
                        <a class="nav-link-custom <?php echo ($current_page == 'service_requests.php') ? 'active' : ''; ?>" href="service_requests.php">
                            <i class="bi bi-wrench-adjustable" style="color: <?php echo ($current_page == 'service_requests.php') ? 'white' : '#8b5cf6'; ?>"></i> Service
                        </a>
                         <a class="nav-link-custom <?php echo ($current_page == 'builds.php') ? 'active' : ''; ?>" href="builds.php">
                            <i class="bi bi-cpu-fill" style="color: <?php echo ($current_page == 'builds.php') ? 'white' : '#0ea5e9'; ?>"></i> Builds
                        </a>
                        <a class="nav-link-custom <?php echo ($current_page == 'delivery_agents.php') ? 'active' : ''; ?>" href="delivery_agents.php">
                            <i class="bi bi-truck-front-fill" style="color: <?php echo ($current_page == 'delivery_agents.php') ? 'white' : '#f43f5e'; ?>"></i> Agents
                        </a>
                    </nav>

                    <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 justify-content-end">
                        
                        <!-- Notification Dropdown -->
                        <div class="dropdown">
                            <button class="icon-btn" type="button" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <i class="bi bi-bell-fill fs-5"></i>
                                <?php if($unread_count > 0 || !empty($low_stock)): ?>
                                    <span class="notification-dot"></span>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="notifBtn" style="width: 350px; max-height: 450px; overflow-y: auto;">
                                <li class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <h6 class="mb-0 text-uppercase small fw-bold text-muted">Notifications</h6>
                                    <?php if($unread_count > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo $unread_count; ?></span>
                                    <?php endif; ?>
                                </li>
                                
                                <?php if(!empty($recent_notifications)): ?>
                                    <?php foreach($recent_notifications as $notif): 
                                        $icon_class = get_notification_icon($notif['type'] ?? '');
                                        $created_at = $notif['created_at'] ?? '';
                                        $time_ago = $created_at ? (time() - strtotime($created_at)) : 0;
                                        $time_str = $time_ago < 60 ? 'Just now' : 
                                                    ($time_ago < 3600 ? floor($time_ago/60) . 'm ago' : 
                                                    ($time_ago < 86400 ? floor($time_ago/3600) . 'h ago' : 
                                                    floor($time_ago/86400) . 'd ago'));
                                    ?>
                                        <li>
                                            <div class="dropdown-item py-3" style="white-space: normal;">
                                                <div class="d-flex gap-2">
                                                    <div class="flex-shrink-0">
                                                        <i class="bi <?php echo $icon_class; ?> fs-5"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between gap-2">
                                                            <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($notif['title'] ?? ''); ?></div>
                                                            <?php if(!empty($notif['id'])): ?>
                                                                <a class="text-muted small" href="?delete_notif=<?php echo urlencode($notif['id']); ?>" title="Delete">
                                                                    <i class="bi bi-x-lg"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if(!empty($notif['message'])): ?>
                                                            <div class="text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($notif['message']); ?></div>
                                                        <?php endif; ?>
                                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                            <i class="bi bi-clock me-1"></i><?php echo $time_str; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if(!empty($low_stock)): ?>
                                    <li>
                                        <a class="dropdown-item py-3" href="#" data-bs-toggle="modal" data-bs-target="#lowStockModal" style="white-space: normal;">
                                            <div class="d-flex gap-2">
                                                <div class="flex-shrink-0">
                                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark small mb-1">Low Stock Alert</div>
                                                    <div class="text-muted" style="font-size: 0.8rem;"><?php echo count($low_stock); ?> products need restocking</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if(empty($recent_notifications) && empty($low_stock)): ?>
                                    <li class="text-center py-4">
                                        <i class="bi bi-bell-slash text-muted fs-1 d-block mb-2"></i>
                                        <span class="text-muted small">No notifications</span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if($unread_count > 0): ?>
                                    <li class="border-top">
                                        <a class="dropdown-item text-center text-primary small fw-bold py-2" href="?clear_notifs=1">
                                            <i class="bi bi-trash me-1"></i>Clear all
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- User Menu Dropdown -->
                        <div class="dropdown">
                            <button class="user-pill" type="button" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
                                <div class="avatar-circle">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <span class="fw-bold text-dark pe-1"><?php echo htmlentities($_SESSION['username']); ?></span>
                                <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-power me-2"></i>Sign Out</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<?php if(!empty($low_stock)): ?>
<!-- Professional Low Stock Alert Banner -->
<div class="alert alert-danger border-0 rounded-0 m-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 5px solid #dc2626 !important;">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center bg-danger rounded-circle" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-exclamation-triangle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold text-danger">
                            <i class="bi bi-bell-fill me-1"></i>Low Stock Alert
                        </h6>
                        <p class="mb-0 small text-dark">
                            <strong><?php echo count($low_stock); ?></strong> product<?php echo count($low_stock) > 1 ? 's have' : ' has'; ?> critical stock levels (≤5 units). Immediate restocking recommended.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-danger btn-sm rounded-pill px-4 me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#lowStockModal">
                    <i class="bi bi-eye-fill me-1"></i>View Details
                </button>
                <a href="view_product.php" class="btn btn-outline-danger btn-sm rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i>Restock Now
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">