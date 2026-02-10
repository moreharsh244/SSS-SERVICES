<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');
// user identifier: numeric id when available, otherwise store username/email for admin lookup
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_name = mysqli_real_escape_string($con, $_SESSION['username'] ?? $_SESSION['email'] ?? '');
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = mysqli_real_escape_string($con, $_POST['build_name'] ?? 'My Build');
    $items_json = $_POST['items_json'] ?? '';
    $data = json_decode($items_json, true);
    if(!$data || !isset($data['items'])){
        echo '<script>alert("Invalid build data");window.history.back();</script>';
        exit;
    }
    // enforce mandatory component categories server-side
    $required_components = ['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler'];
    $present = [];
    foreach($data['items'] as $k => $v){
        // client uses keys like "Category_index" so extract category part before underscore
        $parts = explode('_', $k);
        $cat = $parts[0] ?? $k;
        $present[$cat] = true;
    }
    $missing = array_values(array_diff($required_components, array_keys($present)));
    if(!empty($missing)){
        $msg = 'Please add the following components to your build: ' . implode(', ', $missing);
        echo '<script>alert("'.htmlspecialchars($msg, ENT_QUOTES).'");window.history.back();</script>';
        exit;
    }
    $total = floatval($data['total'] ?? 0);
    // create builds table if not exists (defensive)
        $sqlc = "CREATE TABLE IF NOT EXISTS `builds` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `user_name` VARCHAR(255) DEFAULT NULL,
            `name` VARCHAR(255) NOT NULL,
            `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $sqlc);
    $sqlc2 = "CREATE TABLE IF NOT EXISTS `build_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `build_id` INT NOT NULL,
      `product_id` INT NOT NULL,
      `category` VARCHAR(100) NULL,
      `price` DECIMAL(10,2) NOT NULL,
      FOREIGN KEY (`build_id`) REFERENCES `builds`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $sqlc2);

        // ensure builds table has user_name column (in case table was created earlier)
        $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'builds' AND COLUMN_NAME = 'user_name'";
        $col_res = mysqli_query($con, $col_check);
        if(!$col_res || mysqli_num_rows($col_res) === 0){
                @mysqli_query($con, "ALTER TABLE builds ADD COLUMN user_name VARCHAR(255) DEFAULT NULL");
        }

    // insert build
    $ins = "INSERT INTO builds (user_id, user_name, name, total) VALUES ('$user_id', '$user_name', '$name', '$total')";
    if(mysqli_query($con, $ins)){
        $build_id = mysqli_insert_id($con);
        foreach($data['items'] as $cat => $it){
            $pid = intval($it['pid']);
            $price = floatval($it['price']);
            $cat_esc = mysqli_real_escape_string($con, $cat);
            $ins2 = "INSERT INTO build_items (build_id, product_id, category, price) VALUES ('$build_id', '$pid', '$cat_esc', '$price')";
            mysqli_query($con, $ins2);
        }
        echo '<script>alert("Build saved successfully");window.location.href="cart.php";</script>';
        exit;
    } else {
        echo '<script>alert("Failed to save build: '.mysqli_error($con).'");window.history.back();</script>';
        exit;
    }
}
// If not POST, show the build UI
// fetch products for the product selector
$products = [];
$pq = mysqli_query($con, "SELECT pid, pname, pprice, pcat, pimg FROM products");
if($pq){
    while($r = mysqli_fetch_assoc($pq)) $products[] = $r;
}
// Render as partial for AJAX fetch when requested
$is_partial = isset($_GET['partial']) && $_GET['partial'] === '1';
if(!$is_partial){
    if(!defined('page')) define('page','build');
    include('header.php');
}
?>
<div class="container py-4 build-page">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="h3 mb-2">Build Your PC</h1>
            <p class="text-muted mb-3">Select parts with confidence. Your total updates instantly.</p>
            <div class="build-steps">
                <span class="build-step">1. Choose category</span>
                <span class="build-step">2. Select product</span>
                <span class="build-step">3. Add part</span>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-3 build-card">
                <h5 class="mb-2">Add Part</h5>
                <p class="text-muted small mb-3">Use the selector to add parts from the store.</p>
                <form id="addPartForm" onsubmit="return false;">
                    <div class="mb-2">
                        <label class="form-label">Category</label>
                        <select id="partCategory" class="form-select">
                            <option>CPU</option>
                            <option>Motherboard</option>
                            <option>GPU</option>
                            <option>RAM</option>
                            <option>Storage</option>
                            <option>PSU</option>
                            <option>Case</option>
                            <option>Cooler</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Product</label>
                        <div class="d-flex gap-2 align-items-start">
                            <select id="partProduct" class="form-select">
                                <option value="">Select a product from store</option>
                                <?php foreach($products as $p){
                                    $pn = htmlspecialchars($p['pname'], ENT_QUOTES);
                                    $pp = number_format((float)$p['pprice'], 2, '.', '');
                                    $pc = htmlspecialchars($p['pcat'] ?? '', ENT_QUOTES);
                                    $pid = (int)$p['pid'];
                                    $pimg = htmlspecialchars($p['pimg'] ?? '');
                                    $dataimg = $pimg ? '../productimg/'.rawurlencode($pimg) : '';
                                    echo "<option value=\"$pid\" data-price=\"$pp\" data-category=\"$pc\" data-img=\"$dataimg\">$pn</option>\n";
                                } ?>
                            </select>
                            <div style="width:120px;flex:0 0 120px">
                                <img id="prodPreview" src="../img/pc1.jpg" alt="Preview" class="img-fluid rounded" style="height:90px;object-fit:cover;width:120px;display:block;" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <div class="col-6">
                            <label class="form-label">Price</label>
                            <input id="partPrice" class="form-control" type="number" step="0.01" placeholder="0.00" readonly />
                        </div>
                    </div>
                    <div class="d-grid">
                        <button id="addBtn" class="btn btn-primary">Add Part</button>
                    </div>
                </form>
            </div>
            <div class="mt-3 text-muted small">
                Tip: Add each component, then click "Save Build" on the right.
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-0 build-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Current Build</h5>
                        <small class="text-muted">Preview of selected parts</small>
                    </div>
                    <div>
                        <span class="text-muted">Total:</span>
                        <span id="totalPrice" class="ms-2 h5 mb-0 price">₹0.00</span>
                    </div>
                </div>
                <div class="px-3 py-2 border-bottom build-requirements">
                    <div class="text-muted small mb-2">Required components</div>
                    <div id="requiredList" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="p-3">
                    <div id="itemsList">
                        <div class="build-empty text-center text-muted py-4">No parts added yet.</div>
                    </div>
                </div>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <div class="w-50">
                        <input id="buildName" name="build_name" class="form-control" placeholder="My Gaming Build" />
                    </div>
                    <form id="saveForm" method="post" class="d-flex ms-3 w-50 justify-content-end">
                        <input type="hidden" id="itemsJson" name="items_json" />
                        <button id="saveBtn" class="btn btn-success">Save Build</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const items = [];
    const itemsList = document.getElementById('itemsList');
    const totalPriceEl = document.getElementById('totalPrice');
    const REQUIRED_CATEGORIES = ['CPU','Motherboard','GPU','RAM','Storage','PSU','Case','Cooler'];

    function renderItems(){
        if(items.length === 0){
            itemsList.innerHTML = '<div class="build-empty text-center text-muted py-4">No parts added yet.</div>';
            totalPriceEl.textContent = '₹0.00';
            renderRequiredList();
            return;
        }
        let html = '<div class="list-group">';
            items.forEach((it, idx)=>{
                const imgHtml = it.img ? `<img src="${escapeHtml(it.img)}" class="item-thumb" onclick="showImage('${escapeHtml(it.img)}')" style="cursor:pointer">` : '';
                html += `<div class="list-group-item items-list">
                    ${imgHtml}
                    <div style="flex:1">
                        <div class="fw-semibold">${escapeHtml(it.category)} — ${escapeHtml(it.name)}</div>
                    </div>
                    <div class="text-end" style="min-width:120px">
                        <div class="price">₹${Number(it.price).toFixed(2)}</div>
                        <button class="btn btn-sm btn-link text-danger" onclick="removeItem(${idx})">Remove</button>
                    </div>
                </div>`;
            });
        html += '</div>';
        itemsList.innerHTML = html;
        const total = items.reduce((s,i)=>s+Number(i.price||0),0);
        totalPriceEl.textContent = '₹' + total.toFixed(2);
        renderRequiredList();
    }

    function renderRequiredList(){
        const wrap = document.getElementById('requiredList');
        if(!wrap) return;
        const present = {};
        items.forEach(it=>{ present[(it.category||'').trim()] = true; });
        wrap.innerHTML = REQUIRED_CATEGORIES.map(cat=>{
            const ok = !!present[cat];
            const cls = ok ? 'req-badge ok' : 'req-badge missing';
            return `<span class="${cls}">${cat}</span>`;
        }).join('');
    }

    function removeItem(i){ items.splice(i,1); renderItems(); }

    const prodSelect = document.getElementById('partProduct');
    const priceInput = document.getElementById('partPrice');
    const catSelect = document.getElementById('partCategory');

    // cache original product options so we can filter client-side
    const originalProdOptions = Array.from(prodSelect.options).map(o => o.cloneNode(true));

    function filterProductsByCategory(cat){
        // rebuild options keeping placeholder
        prodSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.text = 'Select a product from store';
        prodSelect.appendChild(placeholder);
        originalProdOptions.forEach(o=>{
            const ocat = o.dataset ? (o.dataset.category || '') : '';
            if(!cat || cat === '' || cat === 'All' || ocat === cat){
                prodSelect.appendChild(o.cloneNode(true));
            }
        });
        prodSelect.value = '';
        priceInput.value = '';
    }

    // when category changes, filter product list
    catSelect.addEventListener('change', ()=>{ filterProductsByCategory(catSelect.value); });

    // initialize product list to current category
    filterProductsByCategory(catSelect.value);

    // when product selection changes, fill price and category (if available)
    prodSelect.addEventListener('change', ()=>{
        const opt = prodSelect.options[prodSelect.selectedIndex];
        if(!opt || !opt.value){ priceInput.value = ''; return; }
        priceInput.value = opt.dataset.price || '';
        const pcat = opt.dataset.category || '';
        if(pcat) document.getElementById('partCategory').value = pcat;
        // update preview image
        const img = opt.dataset.img || '';
        const preview = document.getElementById('prodPreview');
        if(preview){ preview.src = img || '../img/pc1.jpg'; }
    });

    document.getElementById('addBtn').addEventListener('click', ()=>{
        const sel = prodSelect;
        const opt = sel.options[sel.selectedIndex];
        if(!opt || !opt.value){ alert('Please select a product from the store'); return; }
        const pid = opt.value;
        const name = opt.text;
        const price = parseFloat(opt.dataset.price) || 0;
        const cat = opt.dataset.category || document.getElementById('partCategory').value.trim();
        const img = opt.dataset.img || '';
        items.push({category:cat,name:name,pid:pid,price:price,img:img});
        // reset selection
        sel.value = '';
        priceInput.value = '';
        renderItems();
    });

    function showImage(src){
        if(!src) return;
        // open in modal if available, otherwise open in new tab
        const modalImg = document.getElementById('modalImageBuild');
        if(modalImg){ modalImg.src = src; var m = new bootstrap.Modal(document.getElementById('imageModalBuild')); m.show(); return; }
        window.open(src,'_blank');
    }

    document.getElementById('saveBtn').addEventListener('click',(e)=>{
        e.preventDefault();
        if(items.length===0){ alert('Add at least one part'); return; }
        // check required categories are present
        const present = {};
        items.forEach(it=>{ present[(it.category||'').trim()] = true; });
        const missing = REQUIRED_CATEGORIES.filter(c => !present[c]);
        if(missing.length>0){
            alert('Please add the following components to your build before saving: ' + missing.join(', '));
            return;
        }
        const buildName = document.getElementById('buildName').value.trim() || 'My Build';
        const payload = { items: {} , total: items.reduce((s,i)=>s+Number(i.price||0),0)};
        // Convert items array into simple category-keyed object for server compatibility
        items.forEach((it,idx)=>{ payload.items[it.category + '_' + idx] = { pid: it.pid||0, price: Number(it.price||0), name: it.name }; });
        document.getElementById('itemsJson').value = JSON.stringify(payload);
        // create a temporary form to include build_name and items_json
        const form = document.getElementById('saveForm');
        const bn = document.createElement('input'); bn.type='hidden'; bn.name='build_name'; bn.value=buildName; form.appendChild(bn);
        form.submit();
    });

    function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    renderItems();
</script>
<!-- Image modal for build preview -->
<div class="modal fade" id="imageModalBuild" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalImageBuild" src="" alt="Preview" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>
<?php if(!$is_partial){ include('footer.php'); } ?>
