<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = mysqli_real_escape_string($con, $_POST['build_name'] ?? 'My Build');
    $items_json = $_POST['items_json'] ?? '';
    $data = json_decode($items_json, true);
    if(!$data || !isset($data['items'])){
        echo '<script>alert("Invalid build data");window.history.back();</script>';
        exit;
    }
    $total = floatval($data['total'] ?? 0);
    // create builds table if not exists (defensive)
    $sqlc = "CREATE TABLE IF NOT EXISTS `builds` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
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

    // insert build
    $ins = "INSERT INTO builds (user_id, name, total) VALUES ('$user_id', '$name', '$total')";
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
$pq = mysqli_query($con, "SELECT pid, pname, pprice, pcat FROM products");
if($pq){
    while($r = mysqli_fetch_assoc($pq)) $products[] = $r;
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Build Your PC</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="user.css" rel="stylesheet">
    <style>
        .build-card{border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
        .item-row:hover{background:#f8f9fa}
        .price{font-weight:600}
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="h3">Build Your PC</h1>
            <p class="text-muted">Add parts, see live total, then save your build.</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-3 build-card">
                <h5 class="mb-3">Add Part</h5>
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
                        <select id="partProduct" class="form-select">
                            <option value="">Select a product from store</option>
                            <?php foreach($products as $p){
                                $pn = htmlspecialchars($p['pname'], ENT_QUOTES);
                                $pp = number_format((float)$p['pprice'], 2, '.', '');
                                $pc = htmlspecialchars($p['pcat'] ?? '', ENT_QUOTES);
                                $pid = (int)$p['pid'];
                                echo "<option value=\"$pid\" data-price=\"$pp\" data-category=\"$pc\">$pn</option>\n";
                            } ?>
                        </select>
                    </div>
                    <div class="mb-2 row">
                        <div class="col-6">
                            <label class="form-label">Price (USD)</label>
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
                        <span id="totalPrice" class="ms-2 h5 mb-0 price">$0.00</span>
                    </div>
                </div>
                <div class="p-3">
                    <div id="itemsList">
                        <div class="text-center text-muted py-4">No parts added yet.</div>
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

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    const items = [];
    const itemsList = document.getElementById('itemsList');
    const totalPriceEl = document.getElementById('totalPrice');

    function renderItems(){
        if(items.length === 0){
            itemsList.innerHTML = '<div class="text-center text-muted py-4">No parts added yet.</div>';
            totalPriceEl.textContent = '$0.00';
            return;
        }
        let html = '<div class="list-group">';
        items.forEach((it, idx)=>{
            html += `<div class="list-group-item d-flex justify-content-between align-items-center item-row">
                <div>
                    <div class="fw-semibold">${escapeHtml(it.category)} — ${escapeHtml(it.name)}</div>
                </div>
                <div class="text-end">
                    <div class="price">$${Number(it.price).toFixed(2)}</div>
                    <button class="btn btn-sm btn-link text-danger" onclick="removeItem(${idx})">Remove</button>
                </div>
            </div>`;
        });
        html += '</div>';
        itemsList.innerHTML = html;
        const total = items.reduce((s,i)=>s+Number(i.price||0),0);
        totalPriceEl.textContent = '$' + total.toFixed(2);
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
    });

    document.getElementById('addBtn').addEventListener('click', ()=>{
        const sel = prodSelect;
        const opt = sel.options[sel.selectedIndex];
        if(!opt || !opt.value){ alert('Please select a product from the store'); return; }
        const pid = opt.value;
        const name = opt.text;
        const price = parseFloat(opt.dataset.price) || 0;
        const cat = opt.dataset.category || document.getElementById('partCategory').value.trim();
        items.push({category:cat,name:name,pid:pid,price:price});
        // reset selection
        sel.value = '';
        priceInput.value = '';
        renderItems();
    });

    document.getElementById('saveBtn').addEventListener('click',(e)=>{
        e.preventDefault();
        if(items.length===0){ alert('Add at least one part'); return; }
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
</body>
</html>
<?php
?>
