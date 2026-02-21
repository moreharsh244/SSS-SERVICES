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
    $recent_notifications = get_recent_notifications(20);
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
        
        /* --- NOTIFICATION BELL --- */
        .notification-bell {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(186, 230, 253, 0.6);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: var(--text-dark);
            font-size: 1.2rem;
        }

        .notification-bell:hover {
            border-color: #93c5fd;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.15);
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        .notification-dropdown {
            width: 420px;
            max-height: 500px;
            overflow-y: auto;
            background: white;
            border: 1px solid rgba(191, 219, 254, 0.9);
            box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12);
            border-radius: 12px;
            margin-top: 10px;
        }

        .notification-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%);
            border-radius: 12px 12px 0 0;
        }

        .notification-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.unread {
            background: rgba(239, 246, 255, 0.6);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .notification-icon.info { background: #dbeafe; color: #1e40af; }
        .notification-icon.success { background: #d1fae5; color: #065f46; }
        .notification-icon.warning { background: #fed7aa; color: #c2410c; }
        .notification-icon.danger { background: #fee2e2; color: #dc2626; }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .notification-footer {
            padding: 12px 20px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
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

<header class="top-brand-header">
  <div class="container-fluid px-lg-5 px-3">
    <div class="d-flex align-items-center justify-content-between">
      
      <a href="products_card.php" class="brand-text">
        <img src="../img/logo-mark.svg" alt="Logo" width="38" height="38" onerror="this.style.display='none'">
        <span>Shree Swami <span class="brand-accent">Samarth</span></span>
      </a>

      <div class="d-flex align-items-center gap-3">
        <!-- Notification Bell -->
        <div class="dropdown">
          <a href="#" class="notification-bell" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell-fill"></i>
            <?php if($unread_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_count > 99 ? '99+' : $unread_count; ?></span>
            <?php endif; ?>
          </a>
          
          <div class="dropdown-menu dropdown-menu-end notification-dropdown">
            <div class="notification-header">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold" style="color: var(--text-dark);">Notifications</h6>
                <?php if($unread_count > 0): ?>
                  <a href="?clear_notifs=1" class="btn btn-sm btn-link text-primary text-decoration-none p-0" style="font-size: 0.8rem;">
                    Clear All
                  </a>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="notification-list">
              <?php if(!empty($recent_notifications)): ?>
                <?php foreach($recent_notifications as $notif): 
                  $is_unread = ($notif['is_read'] ?? 0) == 0;
                  $type = $notif['type'] ?? 'info';
                  $icon_class = 'info';
                  $icon = 'bi-info-circle-fill';
                  
                  if($type == 'low_stock' || $type == 'warning') {
                    $icon_class = 'warning';
                    $icon = 'bi-exclamation-triangle-fill';
                  } elseif($type == 'new_order' || $type == 'success') {
                    $icon_class = 'success';
                    $icon = 'bi-check-circle-fill';
                  } elseif($type == 'error' || $type == 'urgent') {
                    $icon_class = 'danger';
                    $icon = 'bi-x-circle-fill';
                  }
                  
                  $time_ago = '';
                  if(!empty($notif['created_at'])) {
                    $timestamp = strtotime($notif['created_at']);
                    $diff = time() - $timestamp;
                    if($diff < 60) $time_ago = 'Just now';
                    elseif($diff < 3600) $time_ago = floor($diff / 60) . 'm ago';
                    elseif($diff < 86400) $time_ago = floor($diff / 3600) . 'h ago';
                    else $time_ago = floor($diff / 86400) . 'd ago';
                  }
                ?>
                  <div class="notification-item <?php echo $is_unread ? 'unread' : ''; ?>" 
                       onclick="window.location.href='?delete_notif=<?php echo urlencode($notif['id'] ?? ''); ?>'">
                    <div class="d-flex gap-3">
                      <div class="notification-icon <?php echo $icon_class; ?>">
                        <i class="bi <?php echo $icon; ?>"></i>
                      </div>
                      <div class="flex-grow-1">
                        <p class="mb-1 fw-semibold" style="font-size: 0.9rem; color: var(--text-dark);">
                          <?php echo htmlspecialchars($notif['title'] ?? 'Notification'); ?>
                        </p>
                        <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                          <?php echo htmlspecialchars($notif['message'] ?? ''); ?>
                        </p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                          <i class="bi bi-clock me-1"></i><?php echo $time_ago; ?>
                        </p>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="notification-empty">
                  <i class="bi bi-bell-slash" style="font-size: 2.5rem; opacity: 0.3;"></i>
                  <p class="mb-0 mt-2 fw-semibold">No notifications</p>
                  <p class="mb-0 small">You're all caught up!</p>
                </div>
              <?php endif; ?>
            </div>
            
            <?php if(!empty($recent_notifications)): ?>
              <div class="notification-footer">
                <a href="notifications.php" class="btn btn-sm btn-link text-primary text-decoration-none p-0 w-100 text-center">
                  View All Notifications
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
          <a class="user-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
           <div class="avatar-circle">
              <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
           </div>
           <span class="user-name-text d-none d-sm-inline-block"><?php echo htmlentities($_SESSION['username']); ?></span>
           <i class="bi bi-chevron-down text-muted" style="font-size: 0.7em;"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" style="background: linear-gradient(180deg, #f5f3ff 0%, #eef6ff 100%); border: 1px solid rgba(191,219,254,0.9); box-shadow: 0 14px 30px rgba(30, 64, 175, 0.12); border-radius: 12px; margin-top: 10px;">
           <li><a class="dropdown-item" href="index.php"><i class="bi bi-graph-up-arrow me-2 text-success"></i> Dashboard</a></li>
           <li><a class="dropdown-item" href="delivery_agents.php"><i class="bi bi-people me-2 text-info"></i> Team</a></li>
           <li><hr class="dropdown-divider" style="border-color: rgba(191,219,254,0.7);"></li>
           <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
        </ul>
      </div>
      </div>

    </div>
  </div>
</header>

<nav class="secondary-navbar">
    <div class="container-fluid">
        <div class="nav-container">
            <a class="nav-link-item <?php echo ($current_page == 'products_card.php') ? 'active' : ''; ?>" href="products_card.php">
                <i class="bi bi-grid-fill"></i> Products
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'view_product.php') ? 'active' : ''; ?>" href="view_product.php">
                <i class="bi bi-box-seam-fill"></i> Inventory
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="bi bi-graph-up-arrow"></i> Analytics
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'orders_list.php') ? 'active' : ''; ?>" href="orders_list.php">
                <i class="bi bi-bag-check-fill"></i> Orders
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'service_requests.php') ? 'active' : ''; ?>" href="service_requests.php">
                <i class="bi bi-wrench-adjustable"></i> Support
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'builds.php') ? 'active' : ''; ?>" href="builds.php">
                <i class="bi bi-cpu-fill"></i> Builds
            </a>
            <a class="nav-link-item <?php echo ($current_page == 'delivery_agents.php') ? 'active' : ''; ?>" href="delivery_agents.php">
                <i class="bi bi-truck-front-fill"></i> Delivery
            </a>
        </div>
    </div>
</nav>

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