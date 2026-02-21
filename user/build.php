<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    session_start();
}
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');

$is_partial = isset($_GET['partial']);

// --- PHP Processing Logic (kept exactly as original) ---
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_name = mysqli_real_escape_string($con, $_SESSION['username'] ?? $_SESSION['email'] ?? '');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = mysqli_real_escape_string($con, $_POST['build_name'] ?? 'My Build');
    $items_json = $_POST['items_json'] ?? '';
    $data = json_decode($items_json, true);
    
    if(!$data || !isset($data['items'])){
        echo '<script>alert("Unable to process build data.");window.history.back();</script>';
        exit;
    }

    $required_components = ['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler'];
    $category_map = [
        'CPU' => 'CPU', 'Motherboard' => 'Motherboard',
        'Graphics Card' => 'GPU', 'GPU' => 'GPU',
        'RAM Memory' => 'RAM', 'RAM' => 'RAM',
        'Storage Drive' => 'Storage', 'Storage' => 'Storage',
        'Power Supply' => 'PSU', 'PSU' => 'PSU',
        'Cabinet' => 'Case', 'Case' => 'Case',
        'CPU Cooler' => 'Cooler', 'Cooler' => 'Cooler'
    ];
    
    $present = [];
    foreach($data['items'] as $it){
        $cat_raw = $it['category'] ?? '';
        $cat_norm = $category_map[$cat_raw] ?? $cat_raw;
        if($cat_norm !== '') $present[$cat_norm] = true;
    }
    
    $missing = array_values(array_diff($required_components, array_keys($present)));
    if(!empty($missing)){
        $msg = 'Required: ' . implode(', ', $missing);
        echo '<script>alert("'.htmlspecialchars($msg).'");window.history.back();</script>';
        exit;
    }

    $total = floatval($data['total'] ?? 0);

    $sqlc = "CREATE TABLE IF NOT EXISTS `builds` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `user_name` VARCHAR(255), `name` VARCHAR(255), `total` DECIMAL(10,2), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;";
    mysqli_query($con, $sqlc);
    $sqlc2 = "CREATE TABLE IF NOT EXISTS `build_items` (`id` INT AUTO_INCREMENT PRIMARY KEY, `build_id` INT NOT NULL, `product_id` INT, `category` VARCHAR(100), `product_name` VARCHAR(255), `product_img` VARCHAR(255), `price` DECIMAL(10,2), `qty` INT DEFAULT 1, FOREIGN KEY (`build_id`) REFERENCES `builds`(`id`) ON DELETE CASCADE) ENGINE=InnoDB;";
    mysqli_query($con, $sqlc2);

    $ins = "INSERT INTO builds (user_id, user_name, name, total) VALUES ('$user_id', '$user_name', '$name', '$total')";
    if(mysqli_query($con, $ins)){
        $build_id = mysqli_insert_id($con);
        
        // Create admin notification for new build
        $notif_title = "New PC Build: $name";
        $notif_msg = "Customer: $user_name | Total: ₹" . number_format($total, 2) . " | Items: " . count($data['items']);
        add_admin_notification('build', $notif_title, $notif_msg, 'builds.php');
        
        foreach($data['items'] as $it){
            $pid = intval($it['pid'] ?? 0);
            $price = floatval($it['price'] ?? 0);
            $qty = max(1, intval($it['qty'] ?? 1));
            $cat = mysqli_real_escape_string($con, $it['category'] ?? '');
            $pname = mysqli_real_escape_string($con, $it['name'] ?? '');
            $pimg = mysqli_real_escape_string($con, $it['img'] ?? '');
            mysqli_query($con, "INSERT INTO build_items (build_id, product_id, category, product_name, product_img, price, qty) VALUES ('$build_id', '$pid', '$cat', '$pname', '$pimg', '$price', '$qty')");
        }
        echo '<script>sessionStorage.removeItem("buildItemsCurrent"); alert("Build Saved!"); window.location.href="cart.php";</script>';
        exit;
    }
}

$products = [];
$pq = mysqli_query($con, "SELECT pid, pname, pprice, pcat, pimg, pcompany, pdisc, pqty FROM products");
if($pq){ while($r = mysqli_fetch_assoc($pq)) $products[] = $r; }

if(!$is_partial){
    if(!defined('page')) define('page','build');
    include('header.php');
}
?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    :root {
        --primary-grad: linear-gradient(135deg, #8b5cf6 0%, #0ea5e9 100%);
        --glass-bg: rgba(248, 251, 255, 0.9);
        --glass-border: 1px solid rgba(191, 219, 254, 0.8);
        --card-shadow: 0 10px 20px rgba(30,64,175,0.10);
        --hover-shadow: 0 15px 30px rgba(2,132,199,0.16);
    }

    .build-page-wrap {
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.10) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
        padding-top: 20px;
    }

    .build-container { max-width: 1200px; margin: 0 auto; }

    /* --- HERO SECTION --- */
    .build-hero {
        background: linear-gradient(120deg, rgba(245,243,255,0.92) 0%, rgba(238,246,255,0.92) 55%, rgba(240,253,244,0.92) 100%);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(191,219,254,0.8);
        position: relative;
        overflow: hidden;
    }
    
    .build-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 6px; height: 100%;
        background: var(--primary-grad);
    }

    /* --- SLOT CARDS --- */
    .slot-card {
        height: 160px;
        background: white;
        border-radius: 20px;
        border: 2px dashed #cbd5e1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .slot-card:hover {
        transform: translateY(-5px);
        border-color: #6366f1;
        box-shadow: var(--hover-shadow);
    }

    .slot-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 10px;
        transition: transform 0.3s;
    }

    .slot-card:hover .slot-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    /* --- FILLED STATE --- */
    .slot-card.filled {
        border: none;
        background: white;
        padding: 15px;
        align-items: flex-start;
        justify-content: space-between;
        height: auto;
        min-height: 160px;
    }

    .slot-card.filled::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 6px;
        background: var(--slot-color, #6366f1); /* Dynamic Color */
    }

    .filled-content {
        display: flex;
        gap: 15px;
        width: 100%;
        align-items: center;
    }

    .slot-img {
        width: 70px;
        height: 70px;
        object-fit: contain;
        border-radius: 10px;
        background: #f8fafc;
        padding: 5px;
        border: 1px solid #e2e8f0;
    }

    .slot-details { flex: 1; overflow: hidden; }
    
    .slot-category-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--slot-color, #64748b);
        margin-bottom: 4px;
        display: inline-block;
    }

    .slot-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .slot-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #10b981;
    }

    .slot-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        width: 100%;
    }

    .btn-slot {
        flex: 1;
        border: none;
        border-radius: 8px;
        padding: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-change { background: #eff6ff; color: #3b82f6; }
    .btn-change:hover { background: #dbeafe; }
    
    .btn-remove { background: #fef2f2; color: #ef4444; }
    .btn-remove:hover { background: #fee2e2; }

    /* --- FLOATING FOOTER --- */
    .sticky-total-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 1000px;
        background: linear-gradient(90deg, rgba(245,243,255,0.95) 0%, rgba(238,246,255,0.95) 100%);
        backdrop-filter: blur(12px);
        color: #1f2a44;
        border-radius: 100px;
        padding: 12px 30px;
        z-index: 1000;
        box-shadow: 0 20px 40px rgba(30,64,175,0.18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(191,219,254,0.9);
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp { from { bottom: -100px; } to { bottom: 20px; } }

    .total-label { font-size: 0.8rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; }
    .total-value { font-size: 1.5rem; font-weight: 700; color: #0284c7; text-shadow: none; }

    .build-name-input {
        background: #f8fbff;
        border: 1px solid #bfdbfe;
        color: #1f2a44;
        border-radius: 20px;
        padding: 8px 15px;
        outline: none;
    }
    .build-name-input::placeholder { color: #94a3b8; }
    .build-name-input:focus { background: #ffffff; border-color: #93c5fd; }

    #saveBtn {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border: none;
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 700;
        box-shadow: 0 0 15px rgba(124, 58, 237, 0.35);
        transition: 0.3s;
    }
    #saveBtn:hover { transform: scale(1.05); box-shadow: 0 0 25px rgba(124, 58, 237, 0.5); }

    /* --- MODAL STYLING --- */
    .modal-content { border-radius: 20px; overflow: hidden; border: none; }
    .modal-header { background: #eef6ff; border-bottom: 1px solid #bfdbfe; padding: 20px; }
    .modal-title { font-weight: 700; color: #1e293b; }
    .product-select-card {
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        transition: 0.2s;
        cursor: pointer;
        height: 100%;
        overflow: hidden;
    }
    .product-select-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);
        transform: translateY(-3px);
    }

    .product-brand {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }

    .product-desc {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.35;
        margin-top: 4px;
        min-height: 34px;
    }

    .product-stock {
        font-size: 0.75rem;
        font-weight: 600;
        color: #0f766e;
        background: #ecfeff;
        border: 1px solid #a5f3fc;
        border-radius: 999px;
        padding: 2px 8px;
    }
</style>

<div id="buildPageRoot">
<div class="build-page-wrap">
    <div class="container build-container py-4 pb-5 mb-5">
        
        <div class="build-hero mb-5 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h1 class="fw-extrabold mb-0 text-dark" style="font-size: 2.5rem;">
                    <span style="background: -webkit-linear-gradient(45deg, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Dream PC</span> Builder
                </h1>
                <p class="text-muted mt-2 mb-0" style="font-size: 1.1rem;">Select components to craft your ultimate machine.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="bi bi-pc-display text-primary" style="font-size: 3rem; opacity: 0.2;"></i>
            </div>
        </div>

        <div class="row g-4" id="buildGrid">
            </div>
    </div>

    <div class="sticky-total-bar">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex flex-column">
                <span class="total-label">Estimated Total</span>
                <span class="total-value" id="totalPrice">₹0.00</span>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="buildName" class="build-name-input d-none d-sm-block" placeholder="Name your build...">
            <form id="saveForm" method="post" class="m-0">
                <input type="hidden" id="itemsJson" name="items_json">
                <button id="saveBtn" class="btn btn-primary">
                    <i class="bi bi-cloud-upload-fill me-2"></i>SAVE BUILD
                </button>
            </form>
        </div>
    </div>
    
    <div style="height: 60px;"></div>
</div>

<div class="modal fade" id="productSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="productSelectorTitle">Select Component</h5>
                    <small class="text-muted">Choose the best part for your build</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <div id="productSelectorBody" class="row g-4"></div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    // --- Data & Config ---
    let productsData = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
    if(!Array.isArray(productsData)) productsData = [];
    let items = [];
    const STORAGE_KEY = 'buildItemsCurrent';
    const QUEUE_KEY = 'buildItems';

    // Categories Configuration (Colors & Icons)
    const SLOTS = [
        { key: 'CPU', label: 'Processor', icon: 'bi-cpu', color: '#3b82f6', bg: '#eff6ff' },
        { key: 'Motherboard', label: 'Motherboard', icon: 'bi-motherboard', color: '#8b5cf6', bg: '#f5f3ff' },
        { key: 'GPU', label: 'Graphics Card', icon: 'bi-gpu-card', color: '#ef4444', bg: '#fef2f2' },
        { key: 'RAM', label: 'Memory (RAM)', icon: 'bi-memory', color: '#10b981', bg: '#ecfdf5' },
        { key: 'Storage', label: 'Storage (SSD/HDD)', icon: 'bi-device-hdd', color: '#f59e0b', bg: '#fffbeb' },
        { key: 'PSU', label: 'Power Supply', icon: 'bi-plug', color: '#6366f1', bg: '#eef2ff' },
        { key: 'Case', label: 'Cabinet / Case', icon: 'bi-pc-display', color: '#14b8a6', bg: '#f0fdfa' },
        { key: 'Cooler', label: 'CPU Cooler', icon: 'bi-fan', color: '#0ea5e9', bg: '#f0f9ff' },
        { key: 'Monitor', label: 'Monitor', icon: 'bi-display', color: '#ec4899', bg: '#fdf2f8' }
    ];

    const CAT_MAP = {
        'graphics card':'GPU', 'gpu':'GPU',
        'ram memory':'RAM', 'ram':'RAM',
        'storage drive':'Storage', 'storage':'Storage',
        'power supply':'PSU', 'psu':'PSU',
        'cabinet':'Case', 'case':'Case',
        'cpu cooler':'Cooler', 'cooler':'Cooler',
        'cpu':'CPU', 'processor':'CPU',
        'monitor':'Monitor'
    };

    function normalizeCat(c) { return String(c || '').trim().toLowerCase(); }
    
    function getCanon(c) {
        const key = normalizeCat(c);
        if (CAT_MAP[key]) return CAT_MAP[key];
        if (key.includes('cpu') || key.includes('processor')) return 'CPU';
        if (key.includes('mother')) return 'Motherboard';
        if (key.includes('graphic') || key.includes('gpu')) return 'GPU';
        if (key.includes('ram')) return 'RAM';
        if (key.includes('storage') || key.includes('ssd')) return 'Storage';
        if (key.includes('power') || key.includes('psu')) return 'PSU';
        if (key.includes('case') || key.includes('cabinet')) return 'Case';
        if (key.includes('cooler') || key.includes('fan')) return 'Cooler';
        if (key.includes('monitor')) return 'Monitor';
        return c;
    }

    function parsePrice(value) {
        const cleaned = String(value || '').replace(/[^0-9.]/g, '');
        const parsed = parseFloat(cleaned);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function previewText(value, max = 90) {
        const text = String(value || '').trim();
        if(!text) return 'No description available.';
        return text.length > max ? text.slice(0, max - 3) + '...' : text;
    }

    productsData = productsData.map(p => { return { ...p, pprice: parsePrice(p.pprice) }; });

    // --- Initialization ---
    function initBuildPage(){
        const grid = document.getElementById('buildGrid');
        if(!grid) return;
        
        const modalEl = document.getElementById('productSelectorModal');
        if(modalEl && modalEl.parentElement !== document.body){ document.body.appendChild(modalEl); }
        
        loadItems();
        bindModalSelection();

        // Check for items added from view_products page
        let queueRaw = null;
        try { queueRaw = localStorage.getItem(QUEUE_KEY); } catch(e){}
        if(!queueRaw){ queueRaw = sessionStorage.getItem(QUEUE_KEY); }
        if(queueRaw){
            JSON.parse(queueRaw).forEach(addItem);
            try { localStorage.removeItem(QUEUE_KEY); } catch(e){}
            sessionStorage.removeItem(QUEUE_KEY);
        }

        saveItems();
        renderGrid();
        bindSaveButton();
    }

    function bindSaveButton() {
        const saveBtn = document.getElementById('saveBtn');
        if(saveBtn && !saveBtn.dataset.bound){
            saveBtn.dataset.bound = '1';
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if(items.length === 0) { alert('Build is empty!'); return; }
                
                const required = ['CPU','Motherboard','GPU','RAM','Storage','PSU','Case'];
                const currentCats = items.map(i => getCanon(i.category));
                const missing = required.filter(c => !currentCats.includes(c));
                
                if(missing.length > 0) {
                    if(!confirm('Your build is missing: ' + missing.join(', ') + '. Save anyway?')) return;
                }

                const payload = { items: items, total: items.reduce((s,i) => s + i.price, 0) };
                document.getElementById('itemsJson').value = JSON.stringify(payload);
                
                const nameVal = document.getElementById('buildName').value || 'My Custom Build';
                const form = document.getElementById('saveForm');
                
                let ni = form.querySelector('input[name="build_name"]');
                if(!ni) {
                    ni = document.createElement('input'); 
                    ni.type='hidden'; ni.name='build_name'; 
                    form.appendChild(ni);
                }
                ni.value=nameVal;
                
                form.submit();
            });
        }
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initBuildPage); } 
    else { initBuildPage(); }

    function addItem(p) {
        const cat = getCanon(p.category);
        // Replace existing item in same category (except multi-slot logic which we simplify here for UI)
        if(['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler','Monitor'].includes(cat)){
            items = items.filter(i => getCanon(i.category) !== cat);
        }
        items.push({
            pid: p.pid, name: p.name, price: parsePrice(p.price),
            category: p.category, img: p.img, qty: 1
        });
    }

    function renderGrid() {
        const grid = document.getElementById('buildGrid');
        grid.innerHTML = '';
        let total = 0;

        SLOTS.forEach(slot => {
            const item = items.find(i => getCanon(i.category) === slot.key);
            const col = document.createElement('div');
            col.className = 'col-12 col-sm-6 col-lg-4 col-xl-3'; 

            if(item) {
                total += item.price;
                col.innerHTML = `
                    <div class="slot-card filled" style="--slot-color: ${slot.color}">
                        <div class="filled-content">
                            <img src="${escapeHtml(item.img || '../img/pc1.jpg')}" class="slot-img">
                            <div class="slot-details">
                                <span class="slot-category-badge">${slot.label}</span>
                                <div class="slot-title" title="${escapeHtml(item.name)}">${item.name}</div>
                                <div class="slot-price">₹${item.price.toFixed(2)}</div>
                            </div>
                        </div>
                        <div class="slot-actions">
                             <button class="btn-slot btn-change" onclick="openSelector('${slot.key}')">
                                <i class="bi bi-arrow-repeat"></i> Replace
                             </button>
                             <button class="btn-slot btn-remove" onclick="removeItem('${item.pid}', '${item.category}')">
                                <i class="bi bi-trash"></i>
                             </button>
                        </div>
                    </div>`;
            } else {
                col.innerHTML = `
                    <div class="slot-card" onclick="openSelector('${slot.key}')" style="border-color: ${slot.color}40; background: ${slot.bg};">
                        <div class="slot-icon-wrapper" style="background: ${slot.color}; color: white;">
                            <i class="bi ${slot.icon}"></i>
                        </div>
                        <div class="text-dark fw-bold">${slot.label}</div>
                        <div class="text-muted small">Tap to select</div>
                        <div style="position:absolute; bottom:15px; right:15px; color:${slot.color};">
                            <i class="bi bi-plus-circle-fill fs-4"></i>
                        </div>
                    </div>`;
            }
            grid.appendChild(col);
        });

        document.getElementById('totalPrice').innerText = '₹' + total.toFixed(2);
    }

    function openSelector(key) {
        const filtered = productsData.filter(p => getCanon(p.pcat) === key);
        const body = document.getElementById('productSelectorBody');
        document.getElementById('productSelectorTitle').innerText = 'Select ' + key;

        if(filtered.length === 0) {
            body.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-box-seam display-4 text-muted"></i>
                <p class="mt-3 text-muted">No ${key} available currently.</p>
                <a href="view_products.php" class="btn btn-outline-primary mt-2">Browse All Products</a>
            </div>`;
        } else {
            body.innerHTML = filtered.map(p => {
                const img = p.pimg ? `../productimg/${encodeURIComponent(p.pimg)}` : '../img/pc1.jpg';
                const priceVal = parsePrice(p.pprice);
                const productName = escapeHtml(String(p.pname || 'Unnamed Product'));
                const productBrand = escapeHtml(String(p.pcompany || ''));
                const productDesc = escapeHtml(previewText(p.pdisc, 100));
                const stock = Number.isFinite(parseInt(p.pqty, 10)) ? parseInt(p.pqty, 10) : 0;
                return `
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="product-select-card product-select bg-white p-3 h-100 d-flex flex-column" 
                        role="button"
                        data-pid="${String(p.pid)}"
                        data-name="${escapeAttr(p.pname)}"
                        data-price="${String(priceVal)}"
                        data-category="${escapeAttr(p.pcat)}"
                        data-img="${escapeAttr(img)}">
                        
                        <div class="text-center mb-3" style="height:120px; display:flex; align-items:center; justify-content:center;">
                            <img src="${img}" style="max-height:100%; max-width:100%; object-fit:contain;">
                        </div>
                        <div class="fw-bold text-dark small mb-1" style="line-height:1.2;">${productName}</div>
                        <div class="product-brand">${productBrand || 'Generic Brand'}</div>
                        <div class="product-desc">${productDesc}</div>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="product-stock">Stock: ${stock}</span>
                                <span class="h5 mb-0 text-success fw-bold">₹${priceVal.toFixed(2)}</span>
                                <button type="button" class="btn btn-sm btn-primary rounded-circle"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }
        new bootstrap.Modal(document.getElementById('productSelectorModal')).show();
    }

    // Modal & Selection Helpers
    function closeProductModal(){
        const modalEl = document.getElementById('productSelectorModal');
        if(!modalEl) return;
        const inst = bootstrap.Modal.getInstance(modalEl);
        if(inst) inst.hide();
    }

    function selectProduct(pid, name, price, cat, img) {
        addItem({pid, name, price, category: cat, img});
        saveItems();
        renderGrid();
        closeProductModal();
    }

    function bindModalSelection(){
        const body = document.getElementById('productSelectorBody');
        if(!body) return;
        body.addEventListener('click', function(e){
            const card = e.target.closest('.product-select');
            if(!card) return;
            const pid = card.getAttribute('data-pid');
            const name = card.getAttribute('data-name');
            const price = card.getAttribute('data-price');
            const category = card.getAttribute('data-category');
            const img = card.getAttribute('data-img');
            selectProduct(pid, name, price, category, img);
        });
    }

    function removeItem(pid, cat) {
        items = items.filter(i => !(i.pid == pid && i.category == cat));
        saveItems();
        renderGrid();
    }

    function saveItems() {
        const raw = JSON.stringify(items);
        try { localStorage.setItem(STORAGE_KEY, raw); } catch(e){}
        try { sessionStorage.setItem(STORAGE_KEY, raw); } catch(e){}
    }

    function loadItems() {
        let raw = null;
        try { raw = localStorage.getItem(STORAGE_KEY); } catch(e){}
        if(!raw){ raw = sessionStorage.getItem(STORAGE_KEY); }
        if(!raw) return;
        try { items = JSON.parse(raw) || []; } catch(e){ items = []; }
    }

    function escapeHtml(text) { return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
    function escapeAttr(text){ return String(text || '').replace(/"/g, "&quot;"); }

</script>
<?php if(!$is_partial){ include('footer.php'); } ?>