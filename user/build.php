<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    session_start();
}
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
        </div>
    </div>

    <!-- Category Selection Grid -->
    <div class="row mb-4">
        <div class="col-lg-10 offset-lg-1">
            <div class="card p-0 build-card" style="box-shadow: 0 6px 20px rgba(0,0,0,0.12); border-radius: 12px; border: 1px solid rgba(0,0,0,0.08);">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div>
                        <h5 class="mb-0">Your PC Build</h5>
                        <small style="color: rgba(255,255,255,0.9);">Customize your perfect configuration</small>
                    </div>
                    <div>
                        <span style="color: rgba(255,255,255,0.9);">Total Price:</span>
                        <span id="totalPrice" class="ms-2 h5 mb-0 price" style="color: #fff;">₹0.00</span>
                    </div>
                </div>
                <div class="p-4 border-bottom">
                    <div class="text-muted mb-3 fw-semibold" style="font-size: 16px;">🛒 Select Components</div>
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $categories = ['CPU', 'Motherboard', 'Graphics Card', 'RAM Memory', 'Storage Drive', 'Power Supply', 'Cabinet', 'CPU Cooler', 'Monitor'];
                        $icons = ['🖥️', '🔌', '📊', '💾', '💽', '⚡', '🎁', '❄️', '🖲️'];  
                        foreach($categories as $idx => $cat):
                            $icon = $icons[$idx] ?? '➕';
                        ?>
                            <button class="btn btn-sm btn-outline-primary category-btn text-start d-flex align-items-center" 
                                    onclick="goToProductView('<?php echo htmlspecialchars($cat); ?>')"
                                    style="padding: 12px 14px; transition: all 0.3s; border-radius: 6px; font-weight: 500;">
                                <span class="me-2" style="font-size: 18px;"><?php echo $icon; ?></span>
                                <span><?php echo htmlspecialchars($cat); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="px-4 py-3 border-bottom build-requirements">
                    <div class="text-muted small mb-2 fw-semibold">✓ Required Components</div>
                    <div id="requiredList" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="p-4" style="min-height: 250px;">
                    <div id="itemsList" class="build-items-container">
                        <div class="build-empty text-center text-muted py-5" style="color: #999; font-size: 16px;">📭 No components selected yet</div>
                    </div>
                </div>
                <div class="p-4 border-top d-flex justify-content-between align-items-center" style="background-color: #f8f9fa;">
                    <div class="w-50">
                        <input id="buildName" name="build_name" class="form-control" placeholder="e.g., Gaming PC Pro" style="border-radius: 6px;" />
                    </div>
                    <form id="saveForm" method="post" class="d-flex ms-3 w-50 justify-content-end">
                        <input type="hidden" id="itemsJson" name="items_json" />
                        <button id="saveBtn" class="btn btn-success" style="border-radius: 6px; font-weight: 600; padding: 8px 24px;">💾 Save Configuration</button>
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
    const REQUIRED_CATEGORIES = ['CPU','Motherboard','Graphics Card','RAM Memory','Storage Drive','Power Supply','Cabinet','CPU Cooler'];
    
    // All products data
    const productsData = <?php echo json_encode($products); ?>;

    // Redirect to view products page with category filter
    function goToProductView(category){
        window.location.href = 'view_products.php?category=' + encodeURIComponent(category) + '&from=build';
    }

    // Open product selector modal for category
    function openComponentSelector(category){
        const filteredProducts = productsData.filter(p => p.pcat === category);
        
        let html;
        if(filteredProducts.length === 0){
            html = `<div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center justify-content-center" style="min-height: 300px; border: 2px dashed #0d6efd; border-radius: 8px;">
                                <div class="text-center">
                                    <div style="font-size: 48px; margin-bottom: 15px;">📦</div>
                                    <h5 style="color: #0d6efd; margin-bottom: 10px;">No Products Available</h5>
                                    <p class="text-muted mb-0">There are currently no <strong>${htmlspecialchars(category)}</strong> products in stock.</p>
                                    <p class="text-muted small mt-2">Please check back later or select another category.</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
        } else {
            html = '<div class="row g-3">';
            filteredProducts.forEach(p => {
                const imgLink = p.pimg ? '../productimg/' + encodeURIComponent(p.pimg) : '../img/pc1.jpg';
                const price = Number(p.pprice).toFixed(2);
                const name = htmlspecialchars(p.pname);
                const pid = p.pid;
                
                html += `<div class="col-md-6 col-lg-4">
                    <div class="card product-card cursor-pointer" onclick="selectProduct('${pid}', '${htmlspecialchars(name)}', '${price}', '${category}', '${htmlspecialchars(imgLink)}')" style="cursor:pointer;transition:all 0.3s;">
                        <img src="${htmlspecialchars(imgLink)}" class="card-img-top" alt="${name}" style="height:200px;object-fit:cover;">
                        <div class="card-body">
                            <h6 class="card-title">${name}</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">₹${price}</span>
                                <small class="text-muted">${category}</small>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
        }
            });
            html += '</div>';
        }
        
        // Show modal
        const modalBody = document.getElementById('productSelectorBody');
        const modalTitle = document.getElementById('productSelectorTitle');
        modalBody.innerHTML = html;
        modalTitle.textContent = 'Select ' + category;
        const modal = new bootstrap.Modal(document.getElementById('productSelectorModal'));
        modal.show();
    }

    // Select a product from modal
    function selectProduct(pid, name, price, category, imgLink){
        items.push({
            category: category,
            name: name,
            pid: pid,
            price: parseFloat(price),
            img: imgLink
        });
        renderItems();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('productSelectorModal'));
        if(modal) modal.hide();
    }

    function renderItems(){
        if(items.length === 0){
            itemsList.innerHTML = '<div class="build-empty text-center text-muted py-4">No parts added yet.</div>';
            totalPriceEl.textContent = '₹0.00';
            renderRequiredList();
            return;
        }
        let html = '<div class="list-group">';
            items.forEach((it, idx)=>{
                const imgHtml = it.img ? `<img src="${escapeHtml(it.img)}" class="item-thumb" onclick="showImage('${escapeHtml(it.img)}')" style="cursor:pointer;width:70px;height:70px;object-fit:cover;border-radius:6px;">` : '';
                html += `<div class="list-group-item items-list" style="border-left: 4px solid #0d6efd; padding: 12px;">
                    <div class="d-flex align-items-center gap-3">
                        ${imgHtml}
                        <div style="flex:1">
                            <div class="fw-semibold" style="color: #2c3e50;">${escapeHtml(it.category)} — ${escapeHtml(it.name)}</div>
                        </div>
                        <div class="text-end">
                            <div class="price fw-semibold" style="color: #27ae60; font-size: 16px;">₹${Number(it.price).toFixed(2)}</div>
                            <button class="btn btn-sm btn-outline-danger mt-1" onclick="removeItem(${idx})" style="font-size: 12px;">✕ Remove</button>
                        </div>
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
        const categoryIcons = {'CPU':'🖥️', 'Motherboard':'🔌', 'Graphics Card':'📊', 'RAM Memory':'💾', 'Storage Drive':'💽', 'Power Supply':'⚡', 'Cabinet':'🎁', 'CPU Cooler':'❄️'};
        items.forEach(it=>{ present[(it.category||'').trim()] = true; });
        wrap.innerHTML = REQUIRED_CATEGORIES.map(cat=>{
            const ok = !!present[cat];
            const icon = categoryIcons[cat] || '➕';
            const cls = ok ? 'badge bg-success' : 'badge bg-light text-dark border border-secondary';
            return `<span class="${cls}" style="padding: 6px 12px; font-size: 12px; font-weight: 500;">${icon} ${cat}</span>`;
        }).join('');
    }

    function removeItem(i){ items.splice(i,1); renderItems(); }

    // Check if coming from view_products with a product to add
    window.addEventListener('load', function(){
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('product');
        if(productId){
            const productData = sessionStorage.getItem('buildProduct_' + productId);
            if(productData){
                try {
                    const product = JSON.parse(productData);
                    items.push({
                        category: product.category,
                        name: product.name,
                        pid: product.pid,
                        price: parseFloat(product.price),
                        img: product.img
                    });
                    renderItems();
                    sessionStorage.removeItem('buildProduct_' + productId);
                    // Scroll to items list
                    document.getElementById('itemsList').scrollIntoView({ behavior: 'smooth' });
                } catch(e){
                    console.error('Error parsing product data:', e);
                }
            }
        }
    });

    function showImage(src){
        if(!src || src.trim() === '') {
            alert('No image available');
            return;
        }
        const modalImg = document.getElementById('modalImageBuild');
        if(modalImg){
            modalImg.src = src;
            const modal = new bootstrap.Modal(document.getElementById('imageModalBuild'));
            modal.show();
        }
    }

    document.getElementById('saveBtn').addEventListener('click',(e)=>{
        e.preventDefault();
        if(items.length===0){ alert('Add at least one part'); return; }
        const present = {};
        items.forEach(it=>{ present[(it.category||'').trim()] = true; });
        const missing = REQUIRED_CATEGORIES.filter(c => !present[c]);
        if(missing.length>0){
            alert('Please add the following components to your build before saving: ' + missing.join(', '));
            return;
        }
        const buildName = document.getElementById('buildName').value.trim() || 'My Build';
        const payload = { items: {} , total: items.reduce((s,i)=>s+Number(i.price||0),0)};
        items.forEach((it,idx)=>{ payload.items[it.category + '_' + idx] = { pid: it.pid||0, price: Number(it.price||0), name: it.name }; });
        document.getElementById('itemsJson').value = JSON.stringify(payload);
        const form = document.getElementById('saveForm');
        const bn = document.createElement('input'); bn.type='hidden'; bn.name='build_name'; bn.value=buildName; form.appendChild(bn);
        form.submit();
    });

    function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    renderItems();
</script>
<!-- Product Selector Modal -->
<div class="modal fade" id="productSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px;">
                <h5 class="modal-title" id="productSelectorTitle">Select Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div id="productSelectorBody"></div>
            </div>
        </div>
    </div>
</div>

<!-- Image modal for build preview -->
<div class="modal fade" id="imageModalBuild" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="modal-header border-0" style="background-color: #f8f9fa; padding: 16px 20px;">
                <h5 class="modal-title">Component Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4" style="background-color: #ffffff;">
                <img id="modalImageBuild" src="" alt="Preview" class="img-fluid rounded" style="max-height: 600px; object-fit: contain; display: block; margin: 0 auto;">
            </div>
        </div>
    </div>
</div>
<?php if(!$is_partial){ include('footer.php'); } ?>
