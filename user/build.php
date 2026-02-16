<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    session_start();
}
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');

$is_partial = isset($_GET['partial']);

// --- PHP Processing Logic (Same as before) ---
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

    // Required components mapping
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

    // Database insertions
    $sqlc = "CREATE TABLE IF NOT EXISTS `builds` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `user_name` VARCHAR(255), `name` VARCHAR(255), `total` DECIMAL(10,2), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;";
    mysqli_query($con, $sqlc);
    $sqlc2 = "CREATE TABLE IF NOT EXISTS `build_items` (`id` INT AUTO_INCREMENT PRIMARY KEY, `build_id` INT NOT NULL, `product_id` INT, `category` VARCHAR(100), `product_name` VARCHAR(255), `product_img` VARCHAR(255), `price` DECIMAL(10,2), `qty` INT DEFAULT 1, FOREIGN KEY (`build_id`) REFERENCES `builds`(`id`) ON DELETE CASCADE) ENGINE=InnoDB;";
    mysqli_query($con, $sqlc2);

    $ins = "INSERT INTO builds (user_id, user_name, name, total) VALUES ('$user_id', '$user_name', '$name', '$total')";
    if(mysqli_query($con, $ins)){
        $build_id = mysqli_insert_id($con);
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

// Fetch products for modal
$products = [];
$pq = mysqli_query($con, "SELECT pid, pname, pprice, pcat, pimg FROM products");
if($pq){ while($r = mysqli_fetch_assoc($pq)) $products[] = $r; }

if(!$is_partial){
    if(!defined('page')) define('page','build');
    include('header.php');
}
?>

<style>
    .build-page-wrap {
        min-height: 100vh;
        background: radial-gradient(1200px 600px at 10% 0%, #eef6ff 0%, #ffffff 45%),
                    radial-gradient(900px 500px at 90% 10%, #f2f8ff 0%, #ffffff 40%);
    }
    .build-container { max-width: 1200px; margin: 0 auto; }
    .build-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 55%, #0a58ca 100%);
        color: #ffffff;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 12px 30px rgba(13,110,253,0.25);
    }
    .slot-card {
        height: 120px; /* Short fixed height */
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8f9fa;
        color: #6c757d;
        flex-direction: column;
        text-align: center;
    }
    .slot-card:hover { border-color: #0d6efd; background: #e7f1ff; color: #0d6efd; }
    .slot-card.filled {
        border: 1px solid #dee2e6;
        border-left: 5px solid #0d6efd;
        background: #fff;
        padding: 10px;
        flex-direction: row;
        justify-content: start;
        text-align: left;
    }
    .slot-icon { font-size: 24px; margin-bottom: 5px; }
    .slot-label { font-size: 14px; font-weight: 600; }
    .slot-img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; margin-right: 15px; }
    .slot-info { flex: 1; overflow: hidden; }
    .slot-title { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .slot-price { color: #198754; font-weight: 700; font-size: 14px; }
    .slot-actions { display: flex; flex-direction: column; gap: 4px; }
    
    /* Sticky Footer for Total/Save */
    .sticky-total-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        padding: 15px 0;
        z-index: 1000;
        border-top: 1px solid #dee2e6;
    }
    /* Modal Product Grid */
    .modal-product-card { cursor: pointer; transition: transform 0.2s; border: 1px solid #eee; }
    .modal-product-card:hover { transform: translateY(-3px); border-color: #0d6efd; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div id="buildPageRoot">
<div class="build-page-wrap">
<div class="container build-container py-4 pb-5 mb-5">
    <div class="build-hero mb-4 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h2 class="fw-bold mb-1">PC Builder</h2>
            <div class="small" style="opacity:0.85;">Pick parts, see the total live, and save the build.</div>
        </div>
        <div class="mt-3 mt-md-0">
            <span class="badge bg-light text-primary px-3 py-2">Smart Build Planner</span>
        </div>
    </div>

    <div class="row g-3" id="buildGrid"></div>
</div>

<div class="sticky-total-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted small d-block">Total Estimate</span>
            <span class="h3 fw-bold text-primary m-0" id="totalPrice">₹0.00</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <input type="text" id="buildName" class="form-control" placeholder="Build Name" style="width: 200px;">
            <form id="saveForm" method="post" class="m-0">
                <input type="hidden" id="itemsJson" name="items_json">
                <button id="saveBtn" class="btn btn-success fw-bold px-4"><i class="fas fa-save me-2"></i>SAVE</button>
            </form>
        </div>
    </div>
</div>
<div style="height: 90px;"></div>
</div>

<div class="modal fade" id="productSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="productSelectorTitle">Select Component</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" style="max-height: 70vh; overflow-y: auto;">
                <div id="productSelectorBody" class="row g-3"></div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    let productsData = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
    if(!Array.isArray(productsData)) productsData = [];
    let items = [];
    const STORAGE_KEY = 'buildItemsCurrent';
    const QUEUE_KEY = 'buildItems';

    // Categories Layout
    const SLOTS = [
        { key: 'CPU', label: 'Processor', icon: '🧠' },
        { key: 'Motherboard', label: 'Motherboard', icon: '🔌' },
        { key: 'GPU', label: 'Graphics Card', icon: '🎮' },
        { key: 'RAM', label: 'RAM Memory', icon: '💾' },
        { key: 'Storage', label: 'Storage', icon: '💽' },
        { key: 'PSU', label: 'Power Supply', icon: '⚡' },
        { key: 'Case', label: 'Cabinet', icon: '📦' },
        { key: 'Cooler', label: 'Cooler', icon: '❄️' },
        { key: 'Monitor', label: 'Monitor', icon: '🖥️' }
    ];

    const CAT_MAP = {
        'graphics card':'GPU',
        'gpu':'GPU',
        'ram memory':'RAM',
        'ram':'RAM',
        'storage drive':'Storage',
        'storage':'Storage',
        'power supply':'PSU',
        'psu':'PSU',
        'cabinet':'Case',
        'case':'Case',
        'cpu cooler':'Cooler',
        'cooler':'Cooler',
        'cpu':'CPU',
        'processor':'CPU',
        'intel':'CPU',
        'amd':'CPU',
        'monitor':'Monitor'
    };

    function normalizeCat(c) {
        return String(c || '').trim().toLowerCase();
    }

    function getCanon(c) {
        const key = normalizeCat(c);
        if (CAT_MAP[key]) return CAT_MAP[key];

        if (key.includes('intel') || key.includes('amd') || key.includes('cpu') || key.includes('processor')) return 'CPU';
        if (key.includes('mother')) return 'Motherboard';
        if (key.includes('graphic') || key.includes('gpu')) return 'GPU';
        if (key.includes('ram')) return 'RAM';
        if (key.includes('storage') || key.includes('ssd') || key.includes('hdd') || key.includes('nvme')) return 'Storage';
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

    productsData = productsData.map(p => {
        return {
            ...p,
            pprice: parsePrice(p.pprice)
        };
    });

    // --- LOGIC: INITIALIZATION ---
    function initBuildPage(){
        const grid = document.getElementById('buildGrid');
        if(!grid) return;
        const modalEl = document.getElementById('productSelectorModal');
        if(modalEl && modalEl.parentElement !== document.body){
            document.body.appendChild(modalEl);
        }
        loadItems();
        bindModalSelection();
        // Process items coming from view_products
        const urlParams = new URLSearchParams(window.location.search);
        const pid = urlParams.get('product');
        if(pid) {
            const pData = sessionStorage.getItem('buildProduct_' + pid);
            if(pData) {
                addItem(JSON.parse(pData));
                sessionStorage.removeItem('buildProduct_' + pid);
            }
        }

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

        const saveBtn = document.getElementById('saveBtn');
        if(saveBtn && !saveBtn.dataset.bound){
            saveBtn.dataset.bound = '1';
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if(items.length === 0) { alert('Build is empty!'); return; }
                
                // Simple Validation
                const required = ['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler'];
                const currentCats = items.map(i => getCanon(i.category));
                const missing = required.filter(c => !currentCats.includes(c));
                
                if(missing.length > 0) {
                    alert('Missing components: ' + missing.join(', '));
                    return;
                }

                const payload = {
                    items: items,
                    total: items.reduce((s,i) => s + i.price, 0)
                };
                document.getElementById('itemsJson').value = JSON.stringify(payload);
                
                // Append Name
                const nameVal = document.getElementById('buildName').value || 'My Build';
                const form = document.getElementById('saveForm');
                const ni = document.createElement('input'); 
                ni.type='hidden'; ni.name='build_name'; ni.value=nameVal;
                form.appendChild(ni);
                
                form.submit();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBuildPage);
    } else {
        initBuildPage();
    }

    function addItem(p) {
        const cat = getCanon(p.category);
        if(['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler','Monitor'].includes(cat)){
            items = items.filter(i => getCanon(i.category) !== cat);
        }

        items.push({
            pid: p.pid,
            name: p.name,
            price: parsePrice(p.price),
            category: p.category,
            img: p.img,
            qty: 1
        });
    }

    function renderGrid() {
        const grid = document.getElementById('buildGrid');
        grid.innerHTML = '';
        let total = 0;

        SLOTS.forEach(slot => {
            // Find item for this slot
            const item = items.find(i => getCanon(i.category) === slot.key);
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3'; // 4 per row = Compact

            if(item) {
                total += item.price;
                col.innerHTML = `
                    <div class="slot-card filled">
                        <img src="${escapeHtml(item.img || '../img/pc1.jpg')}" class="slot-img">
                        <div class="slot-info">
                            <div class="text-uppercase text-muted" style="font-size:10px;">${slot.label}</div>
                            <div class="slot-title" title="${item.name}">${item.name}</div>
                            <div class="slot-price">₹${item.price.toFixed(2)}</div>
                        </div>
                        <div class="slot-actions">
                             <button class="btn btn-sm btn-outline-danger p-0 px-2" onclick="removeItem('${item.pid}', '${item.category}')" title="Remove">&times;</button>
                             <button class="btn btn-sm btn-outline-primary p-0 px-2" onclick="openSelector('${slot.key}')" title="Change">&#8635;</button>
                        </div>
                    </div>`;
            } else {
                col.innerHTML = `
                    <div class="slot-card" onclick="openSelector('${slot.key}')">
                        <div class="slot-icon">${slot.icon}</div>
                        <div class="slot-label">Select ${slot.label}</div>
                    </div>`;
            }
            grid.appendChild(col);
        });

        document.getElementById('totalPrice').innerText = '₹' + total.toFixed(2);
    }

    function openSelector(key) {
        // Filter products matching this canonical key
        const filtered = productsData.filter(p => getCanon(p.pcat) === key);
        const body = document.getElementById('productSelectorBody');
        document.getElementById('productSelectorTitle').innerText = 'Select ' + key;

        if(filtered.length === 0) {
            body.innerHTML = '<div class="col-12 text-center py-5 text-muted">No products found.</div>';
        } else {
            body.innerHTML = filtered.map(p => {
                const img = p.pimg ? `../productimg/${encodeURIComponent(p.pimg)}` : '../img/pc1.jpg';
                const priceVal = parsePrice(p.pprice);
                return `
                <div class="col-6 col-md-4">
                    <div class="card modal-product-card h-100 product-select" role="button" onclick="selectProductFromCard(this)"
                        data-pid="${String(p.pid)}"
                        data-name="${escapeAttr(p.pname)}"
                        data-price="${String(priceVal)}"
                        data-category="${escapeAttr(p.pcat)}"
                        data-img="${escapeAttr(img)}">
                        <img src="${img}" class="card-img-top" style="height:100px; object-fit:contain;">
                        <div class="card-body p-2 text-center">
                            <div class="small fw-bold text-truncate">${p.pname}</div>
                            <div class="text-success fw-bold">₹${priceVal.toFixed(2)}</div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }
        const modalEl = document.getElementById('productSelectorModal');
        if(modalEl && modalEl.parentElement !== document.body){
            document.body.appendChild(modalEl);
        }
        new bootstrap.Modal(modalEl).show();
    }

    function closeProductModal(){
        const modalEl = document.getElementById('productSelectorModal');
        if(!modalEl) return;
        if(window.bootstrap && bootstrap.Modal){
            const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            inst.hide();
            return;
        }
        // Fallback if bootstrap instance is not available
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if(backdrop) backdrop.remove();
    }

    function selectProduct(pid, name, price, cat, img) {
        addItem({pid, name, price, category: cat, img});
        saveItems();
        renderGrid();
        closeProductModal();
    }

    function selectProductFromCard(card){
        if(!card) return;
        const pid = card.getAttribute('data-pid') || '';
        const name = card.getAttribute('data-name') || '';
        const price = card.getAttribute('data-price') || '0';
        const category = card.getAttribute('data-category') || '';
        const img = card.getAttribute('data-img') || '';
        selectProduct(pid, name, price, category, img);
    }

    document.addEventListener('click', function(e){
        const card = e.target.closest('.product-select');
        if(!card) return;
        e.preventDefault();
        selectProductFromCard(card);
    });

    window.selectProductFromCard = selectProductFromCard;

    function bindModalSelection(){
        const body = document.getElementById('productSelectorBody');
        if(!body) return;
        body.addEventListener('click', function(e){
            const card = e.target.closest('.product-select');
            if(!card) return;
            const pid = card.getAttribute('data-pid') || '';
            const name = card.getAttribute('data-name') || '';
            const price = card.getAttribute('data-price') || '0';
            const category = card.getAttribute('data-category') || '';
            const img = card.getAttribute('data-img') || '';
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

    function escapeHtml(text) {
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    function escapeAttr(text){
        return String(text || '')
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

</script>
<?php if(!$is_partial){ include('footer.php'); } ?>